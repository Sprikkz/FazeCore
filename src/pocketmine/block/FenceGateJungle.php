<?php



namespace pocketmine\block;


class FenceGateJungle extends FenceGate {

	protected $id = self::FENCE_GATE_JUNGLE;

	/**
	 * @return string
	 */
	public function getName() : string{
		return "Jungle Fence Gate";
	}
}