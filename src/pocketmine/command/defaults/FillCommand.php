<?php



namespace pocketmine\command\defaults;


use pocketmine\command\CommandSender;
use pocketmine\event\TranslationContainer;
use pocketmine\item\Item;
use pocketmine\item\ItemBlock;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class FillCommand extends VanillaCommand {

	/**
	 * FillCommand constructor.
	 *
	 * @param $name
	 */
	public function __construct($name){
		parent::__construct(
			$name,
			"%pocketmine.command.fill.description",
			"%commands.fill.usage"
		);
		$this->setPermission("pocketmine.command.fill");
	}

	/**
	 * @param CommandSender $sender
	 * @param string        $label
	 * @param array         $args
	 *
	 * @return bool
	 */
	public function execute(CommandSender $sender, $label, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}

		for($a = 0; $a < 6; $a++){
			if(isset($args[$a])){
				if(is_numeric($args[$a]) and is_integer($args[$a] + 0)){
					$item = Item::fromString($args[6]);
					if($item instanceof ItemBlock){
						$xmin = min($args[0] + 0, $args[3] + 0);
						$xmax = max($args[0] + 0, $args[3] + 0);
						$ymin = min($args[1] + 0, $args[4] + 0);
						$ymax = max($args[1] + 0, $args[4] + 0);
						$zmin = min($args[2] + 0, $args[5] + 0);
						$zmax = max($args[2] + 0, $args[5] + 0);
						$level = ($sender instanceof Player) ? $sender->getLevel() : $sender->getServer()->getDefaultLevel();
						$n = 0;
						$nmax = ($xmax - $xmin + 1) * ($ymax - $ymin + 1) * ($zmax - $zmin + 1);
						for($x = $xmin; $x <= $xmax; $x++){
							for($y = $ymin; $y <= $ymax; $y++){
								for($z = $zmin; $z <= $zmax; $z++){
									if($this->setBlock(new Vector3($x, $y, $z), $level, $item, $args[7] ?? 0)){
										$n++;
										if(is_int($n / 10000)){
											$sender->sendMessage(new TranslationContainer("$n out of $nmax blocks filled, now at $x $y $z", []));
										}
									}else{
										$sender->sendMessage(TextFormat::RED . new TranslationContainer("Error after filling $n out of $nmax blocks.", []));
										return false;
									}
								}
							}
						}
						$sender->sendMessage(new TranslationContainer("Total of $n blocks filled.", []));
						return true;
					}
					$sender->sendMessage(TextFormat::RED . new TranslationContainer($args[6] . " is not a valid block.", []));
					return false;
				}
				$sender->sendMessage(TextFormat::RED . new TranslationContainer($args[$a] . " is not a valid coordinate.", []));
				$sender->sendMessage(new TranslationContainer("commands.generic.usage", [$this->usageMessage]));
				return false;
			}
			$sender->sendMessage(TextFormat::RED . new TranslationContainer("pocketmine.command.fill.missingParameter", []));
			$sender->sendMessage(new TranslationContainer("commands.generic.usage", [$this->usageMessage]));
			return false;
		}

		return false;
	}

	/**
	 * @param Vector3   $p
	 * @param Level     $lvl
	 * @param ItemBlock $b
	 * @param int       $meta
	 *
	 * @return bool
	 */
	private function setBlock(Vector3 $p, Level $lvl, ItemBlock $b, int $meta = 0) : bool{
		$block = $b->getBlock();
		$block->setDamage($meta);
		$lvl->setBlock($p, $block);
		return true;
	}
}
