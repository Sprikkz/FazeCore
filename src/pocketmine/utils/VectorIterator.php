<?php



namespace pocketmine\utils;

use pocketmine\block\Block;
use pocketmine\level\ChunkManager;
use pocketmine\math\Vector3;

/**
 * Этот класс выполняет трассировку лучей и перебирает блоки на линии.
 */
class VectorIterator implements \Iterator {

	/** @var ChunkManager */
	private $level;
	private $maxDistance;

	private static $gridSize = 16777216; //1 << 24

	private $end = false;

	/** @var \SplFixedArray<Vector3>[3] */
	private $positionQueue;
	private $currentBlock = 0;
	/** @var Vector3 */
	private $currentBlockObject = null;
	private $currentDistance = 0;
	private $maxDistanceInt = 0;

	private $secondError;
	private $thirdError;

	private $secondStep;
	private $thirdStep;

	private $mainFace;
	private $secondFace;
	private $thirdFace;

	/**
	 * VectorIterator constructor.
	 *
	 * @param ChunkManager $level
	 * @param Vector3      $from
	 * @param Vector3      $to
	 */
	public function __construct(ChunkManager $level, Vector3 $from, Vector3 $to){
		if($from->equals($to)){
			$this->end = true;
			$this->currentBlock = -1;
			return;
		}
		$direction = $to->subtract($from)->normalize();
		$maxDistance = $from->distance($to);
		$this->level = $level;
		$this->maxDistance = (int) $maxDistance;
		$this->positionQueue = new \SplFixedArray(3);

		$startClone = new Vector3($from->x, $from->y, $from->z);

		$this->currentDistance = 0;

		$mainDirection = 0;
		$secondDirection = 0;
		$thirdDirection = 0;

		$mainPosition = 0;
		$secondPosition = 0;
		$thirdPosition = 0;

		$pos = new Vector3($startClone->x, $startClone->y, $startClone->z);
		$startBlock = new Vector3(floor($pos->x), floor($pos->y), floor($pos->z));

		if($this->getXLength($direction) > $mainDirection){
			$this->mainFace = $this->getXFace($direction);
			$mainDirection = $this->getXLength($direction);
			$mainPosition = $this->getXPosition($direction, $startClone, $startBlock);

			$this->secondFace = $this->getYFace($direction);
			$secondDirection = $this->getYLength($direction);
			$secondPosition = $this->getYPosition($direction, $startClone, $startBlock);

			$this->thirdFace = $this->getZFace($direction);
			$thirdDirection = $this->getZLength($direction);
			$thirdPosition = $this->getZPosition($direction, $startClone, $startBlock);
		}
		if($this->getYLength($direction) > $mainDirection){
			$this->mainFace = $this->getYFace($direction);
			$mainDirection = $this->getYLength($direction);
			$mainPosition = $this->getYPosition($direction, $startClone, $startBlock);

			$this->secondFace = $this->getZFace($direction);
			$secondDirection = $this->getZLength($direction);
			$secondPosition = $this->getZPosition($direction, $startClone, $startBlock);

			$this->thirdFace = $this->getXFace($direction);
			$thirdDirection = $this->getXLength($direction);
			$thirdPosition = $this->getXPosition($direction, $startClone, $startBlock);
		}
		if($this->getZLength($direction) > $mainDirection){
			$this->mainFace = $this->getZFace($direction);
			$mainDirection = $this->getZLength($direction);
			$mainPosition = $this->getZPosition($direction, $startClone, $startBlock);

			$this->secondFace = $this->getXFace($direction);
			$secondDirection = $this->getXLength($direction);
			$secondPosition = $this->getXPosition($direction, $startClone, $startBlock);

			$this->thirdFace = $this->getYFace($direction);
			$thirdDirection = $this->getYLength($direction);
			$thirdPosition = $this->getYPosition($direction, $startClone, $startBlock);
		}

		$d = $mainPosition / $mainDirection;
		$secondd = $secondPosition - $secondDirection * $d;
		$thirdd = $thirdPosition - $thirdDirection * $d;

		$this->secondError = floor($secondd * self::$gridSize);
		$this->secondStep = round($secondDirection / $mainDirection * self::$gridSize);
		$this->thirdError = floor($thirdd * self::$gridSize);
		$this->thirdStep = round($thirdDirection / $mainDirection * self::$gridSize);

		if($this->secondError + $this->secondStep <= 0){
			$this->secondError = -$this->secondStep + 1;
		}

		if($this->thirdError + $this->thirdStep <= 0){
			$this->thirdError = -$this->thirdStep + 1;
		}

		$lastBlock = $startBlock->getSide(Vector3::getOppositeSide($this->mainFace));

		if($this->secondError < 0){
			$this->secondError += self::$gridSize;
			$lastBlock = $lastBlock->getSide(Vector3::getOppositeSide($this->secondFace));
		}

		if($this->thirdError < 0){
			$this->thirdError += self::$gridSize;
			$lastBlock = $lastBlock->getSide(Vector3::getOppositeSide($this->thirdFace));
		}

		$this->secondError -= self::$gridSize;
		$this->thirdError -= self::$gridSize;

		$this->positionQueue[0] = $lastBlock;

		$this->currentBlock = -1;

		$this->scan();

		$startBlockFound = false;

		for($cnt = $this->currentBlock; $cnt >= 0; --$cnt){
			if($this->posEquals($this->positionQueue[$cnt], $startBlock)){
				$this->currentBlock = $cnt;
				$startBlockFound = true;
				break;
			}
		}

		if(!$startBlockFound){
			throw new \InvalidStateException("The starting block is skipped in BlockIterator");
		}

		$this->maxDistanceInt = round($maxDistance / (sqrt($mainDirection ** 2 + $secondDirection ** 2 + $thirdDirection ** 2) / $mainDirection));
	}

	/**
	 * @param Vector3 $a
	 * @param Vector3 $b
	 *
	 * @return bool
	 */
	private function posEquals(Vector3 $a, Vector3 $b){
		return $a->x === $b->x and $a->y === $b->y and $a->z === $b->z;
	}

	/**
	 * @param Vector3 $direction
	 *
	 * @return int
	 */
	private function getXFace(Vector3 $direction){
		return (($direction->x) > 0) ? Vector3::SIDE_EAST : Vector3::SIDE_WEST;
	}

	/**
	 * @param Vector3 $direction
	 *
	 * @return int
	 */
	private function getYFace(Vector3 $direction){
		return (($direction->y) > 0) ? Vector3::SIDE_UP : Vector3::SIDE_DOWN;
	}

	/**
	 * @param Vector3 $direction
	 *
	 * @return int
	 */
	private function getZFace(Vector3 $direction){
		return (($direction->z) > 0) ? Vector3::SIDE_SOUTH : Vector3::SIDE_NORTH;
	}

	/**
	 * @param Vector3 $direction
	 *
	 * @return number
	 */
	private function getXLength(Vector3 $direction){
		return abs($direction->x);
	}

	/**
	 * @param Vector3 $direction
	 *
	 * @return number
	 */
	private function getYLength(Vector3 $direction){
		return abs($direction->y);
	}

	/**
	 * @param Vector3 $direction
	 *
	 * @return number
	 */
	private function getZLength(Vector3 $direction){
		return abs($direction->z);
	}

	/**
	 * @param $direction
	 * @param $position
	 * @param $blockPosition
	 *
	 * @return mixed
	 */
	private function getPosition($direction, $position, $blockPosition){
		return $direction > 0 ? ($position - $blockPosition) : ($blockPosition + 1 - $position);
	}

	/**
	 * @param Vector3 $direction
	 * @param Vector3 $position
	 * @param Vector3 $block
	 *
	 * @return mixed
	 */
	private function getXPosition(Vector3 $direction, Vector3 $position, Vector3 $block){
		return $this->getPosition($direction->x, $position->x, $block->x);
	}

	/**
	 * @param Vector3 $direction
	 * @param Vector3 $position
	 * @param Vector3 $block
	 *
	 * @return mixed
	 */
	private function getYPosition(Vector3 $direction, Vector3 $position, Vector3 $block){
		return $this->getPosition($direction->y, $position->y, $block->y);
	}

	/**
	 * @param Vector3 $direction
	 * @param Vector3 $position
	 * @param Vector3 $block
	 *
	 * @return mixed
	 */
	private function getZPosition(Vector3 $direction, Vector3 $position, Vector3 $block){
		return $this->getPosition($direction->z, $position->z, $block->z);
	}

	public function next(){
		$this->scan();

		if($this->currentBlock <= -1){
			throw new \OutOfBoundsException;
		}else{
			$this->currentBlockObject = $this->positionQueue[$this->currentBlock--];
		}
	}

	/**
	 * @return Block
	 *
	 * @throws \OutOfBoundsException
	 */
	public function current(){
		if($this->currentBlockObject === null){
			throw new \OutOfBoundsException;
		}
		return $this->currentBlockObject;
	}

	public function rewind(){
		throw new \InvalidStateException("BlockIterator does not support rewind()");
	}

	/**
	 * @return int
	 */
	public function key(){
		return $this->currentBlock - 1;
	}

	/**
	 * @return bool
	 */
	public function valid(){
		$this->scan();
		return $this->currentBlock !== -1;
	}

	private function scan(){
		if($this->currentBlock >= 0){
			return;
		}

		if($this->maxDistance !== 0 and $this->currentDistance > $this->maxDistanceInt){
			$this->end = true;
			return;
		}

		if($this->end){
			return;
		}

		++$this->currentDistance;

		$this->secondError += $this->secondStep;
		$this->thirdError += $this->thirdStep;

		if($this->secondError > 0 and $this->thirdError > 0){
			$this->positionQueue[2] = $this->positionQueue[0]->getSide($this->mainFace);

			if(($this->secondStep * $this->thirdError) < ($this->thirdStep * $this->secondError)){
				$this->positionQueue[1] = $this->positionQueue[2]->getSide($this->secondFace);
				$this->positionQueue[0] = $this->positionQueue[1]->getSide($this->thirdFace);
			}else{
				$this->positionQueue[1] = $this->positionQueue[2]->getSide($this->thirdFace);
				$this->positionQueue[0] = $this->positionQueue[1]->getSide($this->secondFace);
			}

			$this->thirdError -= self::$gridSize;
			$this->secondError -= self::$gridSize;
			$this->currentBlock = 2;
		}elseif($this->secondError > 0){
			$this->positionQueue[1] = $this->positionQueue[0]->getSide($this->mainFace);
			$this->positionQueue[0] = $this->positionQueue[1]->getSide($this->secondFace);
			$this->secondError -= self::$gridSize;
			$this->currentBlock = 1;
		}elseif($this->thirdError > 0){
			$this->positionQueue[1] = $this->positionQueue[0]->getSide($this->mainFace);
			$this->positionQueue[0] = $this->positionQueue[1]->getSide($this->thirdFace);
			$this->thirdError -= self::$gridSize;
			$this->currentBlock = 1;
		}else{
			$this->positionQueue[0] = $this->positionQueue[0]->getSide($this->mainFace);
			$this->currentBlock = 0;
		}
	}
}