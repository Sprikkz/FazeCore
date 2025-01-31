<?php



namespace pocketmine\block;

use pocketmine\entity\monster\walking\IronGolem;
use pocketmine\entity\monster\walking\SnowGolem;
use pocketmine\item\Item;
use pocketmine\level\particle\DestroyBlockParticle;
use pocketmine\item\Tool;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\Player;

class LitPumpkin extends Solid implements SolidLight {

	protected $id = self::LIT_PUMPKIN;

	/**
	 * @return int
	 */
	public function getLightLevel(){
		return 15;
	}

	/**
	 * @return int
	 */
	public function getHardness(){
		return 1;
	}

	/**
	 * @return int
	 */
	public function getToolType(){
		return Tool::TYPE_AXE;
	}

	/**
	 * @return string
	 */
	public function getName() : string{
		return "Jack o'Lantern";
	}

	/**
	 * LitPumpkin constructor.
	 *
	 * @param int $meta
	 */
	public function __construct($meta = 0){
		$this->meta = $meta;
	}

	/**
	 * @param Item        $item
	 * @param Block       $block
	 * @param Block       $target
	 * @param int         $face
	 * @param float       $fx
	 * @param float       $fy
	 * @param float       $fz
	 * @param Player|null $player
	 *
	 * @return bool
	 */
	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
		if($player instanceof Player){
			$this->meta = ((int) $player->getDirection() + 5) % 4;
		}
		$this->getLevel()->setBlock($block, $this, true, true);
		if($player != null){
			$level = $this->getLevel();
			if($player->getServer()->allowSnowGolem){
				$block0 = $level->getBlock($block->add(0, -1, 0));
				$block1 = $level->getBlock($block->add(0, -2, 0));
				if($block0->getId() == Item::SNOW_BLOCK and $block1->getId() == Item::SNOW_BLOCK){
					$level->setBlock($block, new Air());
					$level->addParticle(new DestroyBlockParticle($block, $block));
					$level->setBlock($block0, new Air());
					$level->addParticle(new DestroyBlockParticle($block0, $block0));
					$level->setBlock($block1, new Air());
					$level->addParticle(new DestroyBlockParticle($block1, $block1));
					$golem = new SnowGolem($player->getLevel(), new CompoundTag("", [
						"Pos" => new ListTag("Pos", [
							new DoubleTag("", $this->x),
							new DoubleTag("", $this->y),
							new DoubleTag("", $this->z)
						]),
						"Motion" => new ListTag("Motion", [
							new DoubleTag("", 0),
							new DoubleTag("", 0),
							new DoubleTag("", 0)
						]),
						"Rotation" => new ListTag("Rotation", [
							new FloatTag("", 0),
							new FloatTag("", 0)
						]),
					]));
					$golem->spawnToAll();
				}
			}
			if($player->getServer()->allowIronGolem){
				$block0 = $level->getBlock($block->add(0, -1, 0));
				$block1 = $level->getBlock($block->add(0, -2, 0));
				$block2 = $level->getBlock($block->add(-1, -1, 0));
				$block3 = $level->getBlock($block->add(1, -1, 0));
				$block4 = $level->getBlock($block->add(0, -1, -1));
				$block5 = $level->getBlock($block->add(0, -1, 1));
				if($block0->getId() == Item::IRON_BLOCK and $block1->getId() == Item::IRON_BLOCK){
					if($block2->getId() == Item::IRON_BLOCK and $block3->getId() == Item::IRON_BLOCK and $block4->getId() == Item::AIR and $block5->getId() == Item::AIR){
						$level->setBlock($block2, new Air());
						$level->addParticle(new DestroyBlockParticle($block2, $block2));
						$level->setBlock($block3, new Air());
						$level->addParticle(new DestroyBlockParticle($block3, $block3));
					}elseif($block4->getId() == Item::IRON_BLOCK and $block5->getId() == Item::IRON_BLOCK and $block2->getId() == Item::AIR and $block3->getId() == Item::AIR){
						$level->setBlock($block4, new Air());
						$level->addParticle(new DestroyBlockParticle($block4, $block4));
						$level->setBlock($block5, new Air());
						$level->addParticle(new DestroyBlockParticle($block5, $block5));
					}else return false;
					$level->setBlock($block, new Air());
					$level->addParticle(new DestroyBlockParticle($block, $block));
					$level->setBlock($block0, new Air());
					$level->addParticle(new DestroyBlockParticle($block0, $block0));
					$level->setBlock($block1, new Air());
					$level->addParticle(new DestroyBlockParticle($block1, $block1));
					$golem = new IronGolem($player->getLevel(), new CompoundTag("", [
						"Pos" => new ListTag("Pos", [
							new DoubleTag("", $this->x),
							new DoubleTag("", $this->y),
							new DoubleTag("", $this->z)
						]),
						"Motion" => new ListTag("Motion", [
							new DoubleTag("", 0),
							new DoubleTag("", 0),
							new DoubleTag("", 0)
						]),
						"Rotation" => new ListTag("Rotation", [
							new FloatTag("", 0),
							new FloatTag("", 0)
						]),
					]));
					$golem->spawnToAll();
				}
			}
		}

		return true;
	}
}
