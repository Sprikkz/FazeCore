<?php

namespace pocketmine\command\defaults;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\TranslationContainer;
use pocketmine\level\Position;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class SpawnpointCommand extends VanillaCommand {

	/**
	 * SpawnpointCommand constructor.
	 *
	 * @param $name
	 */
	public function __construct($name){
		parent::__construct(
			$name,
			"%pocketmine.command.spawnpoint.description",
			"%pocketmine.command.spawnpoint.usage"
		);
		$this->setPermission("pocketmine.command.spawnpoint");
	}

	/**
	 * @param CommandSender $sender
	 * @param string        $currentAlias
	 * @param array         $args
	 *
	 * @return bool
	 */
	public function execute(CommandSender $sender, $currentAlias, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}

		$target = null;

		if(count($args) === 0){
			if($sender instanceof Player){
				$target = $sender;
			}else{
				$sender->sendMessage(TextFormat::RED . "Please provide a player!");

				return true;
			}
		}else{
			$target = $sender->getServer()->getPlayer($args[0]);
			if($target === null){
				$sender->sendMessage(new TranslationContainer(TextFormat::RED . "%commands.generic.player.notFound"));

				return true;
			}
		}

		if(count($args) === 4){
			if($target->isValid()){
				$level = $target->getLevel();
				$pos = $sender instanceof Player ? $sender->getPosition() : $level->getSpawnLocation();
				$x = $this->getRelativeDouble($pos->x, $sender, $args[1]);
				$y = $this->getRelativeDouble($pos->y, $sender, $args[2], 0, 128);
				$z = $this->getRelativeDouble($pos->z, $sender, $args[3]);
				$target->setSpawn(new Position($x, $y, $z, $level));

				Command::broadcastCommandMessage($sender, new TranslationContainer("commands.spawnpoint.success", [$target->getName(), round($x, 2), round($y, 2), round($z, 2)]));

				return true;
			}
		}elseif(count($args) <= 1){
			if($sender instanceof Player){
                $pos = new Position($sender->getFloorX(), $sender->getFloorY(), $sender->getFloorZ(), $sender->getLevel());
				$target->setSpawn($pos);

				Command::broadcastCommandMessage($sender, new TranslationContainer("commands.spawnpoint.success", [$target->getName(), round($pos->x, 2), round($pos->y, 2), round($pos->z, 2)]));
            }else{
				$sender->sendMessage(TextFormat::RED . "Please provide a player!");

            }
            return true;
        }

		$sender->sendMessage(new TranslationContainer("commands.generic.usage", [$this->usageMessage]));

		return true;
	}
}
