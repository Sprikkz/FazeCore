<?php



namespace pocketmine\block;

class DetectorRail extends PoweredRail {

	protected $id = self::DETECTOR_RAIL;

	/**
	 * DetectorRail constructor.
	 *
	 * @param int $meta
	 */
	public function __construct($meta = 0){
		$this->meta = $meta;
	}

	/**
	 * @return string
	 */
	public function getName() : string{
		return "Detector Rail";
	}
}
