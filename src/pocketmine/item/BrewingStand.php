<?php



namespace pocketmine\item;

use pocketmine\block\Block;

class BrewingStand extends Item {
	/**
	 * BrewingStand constructor.
	 *
	 * @param int $meta
	 * @param int $count
	 */
	public function __construct($meta = 0, $count = 1){
		$this->block = Block::get(Block::BREWING_STAND_BLOCK);
		parent::__construct(self::BREWING_STAND, $meta, $count, "Brewing Stand");
	}
}
