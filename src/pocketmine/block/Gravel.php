<?php



namespace pocketmine\block;

use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\Item;
use pocketmine\item\Tool;

class Gravel extends Fallable {

	protected $id = self::GRAVEL;

	/**
	 * Gravel constructor.
	 */
	public function __construct($meta = 0){
		$this->meta = $meta;
	}

	/**
	 * @return string
	 */
	public function getName() : string{
		return "Gravel";
	}

	/**
	 * @return float
	 */
	public function getHardness(){
		return 0.6;
	}

	/**
	 * @return int
	 */
	public function getToolType(){
		return Tool::TYPE_SHOVEL;
	}

	/**
	 * @param Item $item
	 *
	 * @return array
	 */
	public function getDrops(Item $item) : array{
		$drops = [];
		if($item->getEnchantmentLevel(Enchantment::TYPE_MINING_SILK_TOUCH) > 0){//使用精准采集附魔 不掉落燧石
			$drops[] = [Item::GRAVEL, 0, 1];
			return $drops;
		}
		$fortunel = $item->getEnchantmentLevel(Enchantment::TYPE_MINING_FORTUNE);
		$fortunel = $fortunel > 3 ? 3 : $fortunel;
		$rates = [10, 7, 4, 1];
		if(mt_rand(1, $rates[$fortunel]) === 1){//10% 14% 25% 100%
			$drops[] = [Item::FLINT, 0, 1];
		}
		if(mt_rand(1, 10) !== 1){//90%
			$drops[] = [Item::GRAVEL, 0, 1];
		}
		return $drops;
	}
}
