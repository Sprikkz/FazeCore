<?php



namespace pocketmine\level\particle;

use pocketmine\math\Vector3;

class WaterParticle extends GenericParticle {
	/**
	 * WaterParticle constructor.
	 *
	 * @param Vector3 $pos
	 */
	public function __construct(Vector3 $pos){
		parent::__construct($pos, Particle::TYPE_WATER_WAKE);
	}
}
