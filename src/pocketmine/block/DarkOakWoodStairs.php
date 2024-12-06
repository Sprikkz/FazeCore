<?php



namespace pocketmine\block;

class DarkOakWoodStairs extends WoodStairs {

	protected $id = self::DARK_OAK_WOOD_STAIRS;

	/**
	 * @return string
	 */
	public function getName() : string{
		return "Dark Oak Wood Stairs";
	}
}