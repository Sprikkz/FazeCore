<?php



namespace pocketmine\block;

use pocketmine\item\Item;
use pocketmine\item\Tool;

class Diamond extends Solid {

	protected $id = self::DIAMOND_BLOCK;

	/**
	 * Diamond constructor.
	 *
	 * @param int $meta
	 */
	public function __construct($meta = 0){
		$this->meta = $meta;
	}

	/**
	 * @return int
	 */
	public function getHardness(){
		return 5;
	}

	/**
	 * @return string
	 */
	public function getName() : string{
		return "Diamond Block";
	}

	/**
	 * @return int
	 */
	public function getToolType(){
		return Tool::TYPE_PICKAXE;
	}

	/**
	 * @param Item $item
	 *
	 * @return array
	 */
	public function getDrops(Item $item) : array{
		if($item->isPickaxe() >= 4){
			return [
				[Item::DIAMOND_BLOCK, 0, 1],
			];
		}else{
			return [];
		}
	}
}