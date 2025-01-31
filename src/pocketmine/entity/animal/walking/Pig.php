<?php

namespace pocketmine\entity\animal\walking;

use pocketmine\entity\animal\WalkingAnimal;
use pocketmine\entity\Rideable;
use pocketmine\item\Item as ItemItem;
use pocketmine\Player;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\entity\Creature;

class Pig extends WalkingAnimal implements Rideable{

	const NETWORK_ID = 12;

	public $width = 1.45;
	public $height = 1.12;

	public function getName() : string{
		return "Pig";
	}

	public function initEntity(){
		parent::initEntity();

		$this->setMaxHealth(10);
	}
	
	public function isBaby() : bool{
    return $this->getDataFlag(self::DATA_FLAGS, self::DATA_FLAG_BABY);
  }

	public function targetOption(Creature $creature, $distance){
		if($creature instanceof Player){
			return $creature->spawned && $creature->isAlive() && !$creature->closed && $creature->getInventory()->getItemInHand()->getId() == Item::CARROT && $distance <= 39;
		}
		return false;
	}

    /**
     * Получаем дроп свиньи
     */
	public function getDrops(){
          if($this->isBaby()) return [];
		return [
        ItemItem::get($this->isOnFire() ? ItemItem::COOKED_PORKCHOP : ItemItem::RAW_PORKCHOP, 0, mt_rand(1, 3))
		];
	}
}
