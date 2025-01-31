<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____  
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \ 
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/ 
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_| 
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 * 
 *
*/

namespace pocketmine\level\generator\populator;

use pocketmine\block\Block;
use pocketmine\block\Sapling;
use pocketmine\level\ChunkManager;
use pocketmine\level\generator\object\Tree as ObjectTree;
use pocketmine\utils\Random;

class Tree extends Populator {
	private $randomAmount = 1;
	private $baseAmount = 0;

	private $type;

	/**
	 * Tree constructor.
	 *
	 * @param int $type
	 */
	public function __construct($type = Sapling::OAK){
		$this->type = $type;
	}

	/**
	 * @param $amount
	 */
	public function setRandomAmount($amount){
		$this->randomAmount = $amount;
	}

	/**
	 * @param $amount
	 */
	public function setBaseAmount($amount){
		$this->baseAmount = $amount;
	}

	/**
	 * @param ChunkManager $level
	 * @param              $chunkX
	 * @param              $chunkZ
	 * @param Random       $random
	 *
	 * @return mixed|void
	 */
	public function populate(ChunkManager $level, $chunkX, $chunkZ, Random $random){
		$amount = $random->nextRange(0, $this->randomAmount) + $this->baseAmount;
		for($i = 0; $i < $amount; ++$i){
			$x = $random->nextRange($chunkX << 4, ($chunkX << 4) + 15);
			$z = $random->nextRange($chunkZ << 4, ($chunkZ << 4) + 15);
			$y = $this->getHighestWorkableBlock($level, $x, $z);
			if($y === -1){
				continue;
			}
			ObjectTree::growTree($level, $x, $y, $z, $random, $this->type);
		}
	}

	private function getHighestWorkableBlock(ChunkManager $level, int $x, int $z) : int{
		for($y = 127; $y >= 0; --$y){
			$b = $level->getBlockIdAt($x, $y, $z);
			if($b === Block::DIRT or $b === Block::GRASS){
				return $y + 1;
			}elseif($b !== 0 and $b !== Block::SNOW_LAYER){
				return -1;
			}
		}

		return -1;
	}
}