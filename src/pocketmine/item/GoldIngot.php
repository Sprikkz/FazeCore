<?php



namespace pocketmine\item;


class GoldIngot extends Item {
	/**
	 * GoldIngot constructor.
	 *
	 * @param int $meta
	 * @param int $count
	 */
	public function __construct($meta = 0, $count = 1){
		parent::__construct(self::GOLD_INGOT, $meta, $count, "Gold Ingot");
	}

}