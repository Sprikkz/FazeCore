<?php



namespace pocketmine\item;

class GlisteringMelon extends Item {
	/**
	 * GlisteringMelon constructor.
	 *
	 * @param int $meta
	 * @param int $count
	 */
	public function __construct($meta = 0, $count = 1){
		parent::__construct(self::GLISTERING_MELON, $meta, $count, "Glistering Melon");
	}
}
