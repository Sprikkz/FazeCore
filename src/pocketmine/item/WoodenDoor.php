<?php



namespace pocketmine\item;

use pocketmine\block\Block;

class WoodenDoor extends Door {
	/**
	 * WoodenDoor constructor.
	 *
	 * @param int $meta
	 * @param int $count
	 */
	public function __construct($meta = 0, $count = 1){
		$this->block = Block::get(Item::WOODEN_DOOR_BLOCK);
		parent::__construct(self::WOODEN_DOOR, $meta, $count, "Wooden Door");
	}
}