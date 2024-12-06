<?php



namespace pocketmine\command;

use pocketmine\command\defaults\BanCidByNameCommand;
use pocketmine\command\defaults\BanCidCommand;
use pocketmine\command\defaults\BanCommand;
use pocketmine\command\defaults\BanIpByNameCommand;
use pocketmine\command\defaults\BanIpCommand;
use pocketmine\command\defaults\BanListCommand;
use pocketmine\command\defaults\DefaultGamemodeCommand;
use pocketmine\command\defaults\DeopCommand;
use pocketmine\command\defaults\DifficultyCommand;
use pocketmine\command\defaults\EffectCommand;
use pocketmine\command\defaults\EnchantCommand;
use pocketmine\command\defaults\ExtractPharCommand;
use pocketmine\command\defaults\ExtractPluginCommand;
use pocketmine\command\defaults\FillCommand;
use pocketmine\command\defaults\GamemodeCommand;
use pocketmine\command\defaults\GarbageCollectorCommand;
use pocketmine\command\defaults\GiveCommand;
use pocketmine\command\defaults\HelpCommand;
use pocketmine\command\defaults\KickCommand;
use pocketmine\command\defaults\KillCommand;
use pocketmine\command\defaults\KillAllCommand;
use pocketmine\command\defaults\ListCommand;
use pocketmine\command\defaults\IdCommand;
use pocketmine\command\defaults\MeCommand;
use pocketmine\command\defaults\MakeServerCommand;
use pocketmine\command\defaults\MakePluginCommand;
use pocketmine\command\defaults\OpCommand;
use pocketmine\command\defaults\PardonCidCommand;
use pocketmine\command\defaults\PardonCommand;
use pocketmine\command\defaults\PardonIpCommand;
use pocketmine\command\defaults\PluginsCommand;
use pocketmine\command\defaults\PingCommand;
use pocketmine\command\defaults\ReloadCommand;
use pocketmine\command\defaults\SaveCommand;
use pocketmine\command\defaults\SaveOffCommand;
use pocketmine\command\defaults\SaveOnCommand;
use pocketmine\command\defaults\SayCommand;
use pocketmine\command\defaults\SeedCommand;
use pocketmine\command\defaults\SetBlockCommand;
use pocketmine\command\defaults\SetWorldSpawnCommand;
use pocketmine\command\defaults\SpawnpointCommand;
use pocketmine\command\defaults\StatusCommand;
use pocketmine\command\defaults\StopCommand;
use pocketmine\command\defaults\SummonCommand;
use pocketmine\command\defaults\TeleportCommand;
use pocketmine\command\defaults\TransferServerCommand;
use pocketmine\command\defaults\TellCommand;
use pocketmine\command\defaults\TimeCommand;
use pocketmine\command\defaults\TimingsCommand;
use pocketmine\command\defaults\TpsCommand;
use pocketmine\command\defaults\VanillaCommand;
use pocketmine\command\defaults\VersionCommand;
use pocketmine\command\defaults\WeatherCommand;
use pocketmine\command\defaults\WhitelistCommand;
use pocketmine\command\defaults\XpCommand;
//use pocketmine\command\defaults\XyzCommand;
use pocketmine\event\TranslationContainer;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class SimpleCommandMap implements CommandMap {

	/**
	 * @var Command[]
	 */
	protected $knownCommands = [];

	/**
	 * @var bool[]
	 */
	protected $commandConfig = [];

	/** @var Server */
	private $server;

	/**
	 * SimpleCommandMap constructor.
	 *
	 * @param Server $server
	 */
	public function __construct(Server $server){
		$this->server = $server;
		/** @var bool[] */
		$this->commandConfig = $this->server->getProperty("commands");
		$this->setDefaultCommands();
	}

	private function setDefaultCommands(){
		$this->register("pocketmine", new WeatherCommand("weather"));
		$this->register("pocketmine", new BanCidCommand("bancid"));
		$this->register("pocketmine", new PardonCidCommand("pardoncid"));
		$this->register("pocketmine", new BanCidByNameCommand("bancidbyname"));
		$this->register("pocketmine", new BanIpByNameCommand("banipbyname"));
		$this->register("pocketmine", new VersionCommand("version"));
		$this->register("pocketmine", new FillCommand("fill"));
		$this->register("pocketmine", new PluginsCommand("plugins"));
		$this->register("pocketmine", new SeedCommand("seed"));
		$this->register("pocketmine", new HelpCommand("help"), null, true);
		$this->register("pocketmine", new StopCommand("stop"), null, true);
		$this->register("pocketmine", new TellCommand("tell"));
		$this->register("pocketmine", new DefaultGamemodeCommand("defaultgamemode"));
		$this->register("pocketmine", new BanCommand("ban"));
		$this->register("pocketmine", new BanIpCommand("ban-ip"));
		$this->register("pocketmine", new BanListCommand("banlist"));
		$this->register("pocketmine", new PardonCommand("pardon"));
		$this->register("pocketmine", new ExtractPharCommand("extractphar"));
		$this->register("pocketmine", new ExtractPluginCommand("extractplugin"));
		$this->register("pocketmine", new PardonIpCommand("pardon-ip"));
		$this->register("pocketmine", new SayCommand("say"));
		$this->register("pocketmine", new MeCommand("me"));
		$this->register("pocketmine", new MakeServerCommand("makeserver"));
		$this->register("pocketmine", new MakePluginCommand("makeplugin"));
		$this->register("pocketmine", new ListCommand("list"));
		$this->register("pocketmine", new IdCommand("id"));
		$this->register("pocketmine", new DifficultyCommand("difficulty"));
		$this->register("pocketmine", new KickCommand("kick"));
		$this->register("pocketmine", new OpCommand("op"));
		$this->register("pocketmine", new DeopCommand("deop"));
		$this->register("pocketmine", new WhitelistCommand("whitelist"));
		$this->register("pocketmine", new SaveOnCommand("save-on"));
		$this->register("pocketmine", new SaveOffCommand("save-off"));
		$this->register("pocketmine", new SaveCommand("save-all"), null, true);
		$this->register("pocketmine", new GiveCommand("give"));
		$this->register("pocketmine", new EffectCommand("effect"));
		$this->register("pocketmine", new EnchantCommand("enchant"));
		$this->register("pocketmine", new PingCommand("ping"));
		$this->register("pocketmine", new GamemodeCommand("gamemode"));
		$this->register("pocketmine", new KillCommand("kill"));
		$this->register("pocketmine", new KillAllCommand("killall"));
		$this->register("pocketmine", new SpawnpointCommand("spawnpoint"));
		$this->register("pocketmine", new SetWorldSpawnCommand("setworldspawn"));
		$this->register("pocketmine", new SummonCommand("summon"));
		$this->register("pocketmine", new TeleportCommand("tp"));
		$this->register("pocketmine", new TransferServerCommand("transfer"));
		$this->register("pocketmine", new TimeCommand("time"));
		$this->register("pocketmine", new TimingsCommand("timings"));
		$this->register("pocketmine", new TpsCommand("tps"));
		$this->register("pocketmine", new ReloadCommand("reload"), null, true);
		$this->register("pocketmine", new XpCommand("xp"));
		//$this->register("pocketmine", new XyzCommand("xyz"));
		$this->register("pocketmine", new SetBlockCommand("setblock"));
		$this->register("pocketmine", new StatusCommand("status"), null, true);
		$this->register("pocketmine", new GarbageCollectorCommand("gc"), null, true);
	}


	/**
	 * @param string $fallbackPrefix
	 * @param array  $commands
	 */
	public function registerAll($fallbackPrefix, array $commands){
		foreach($commands as $command){
			$this->register($fallbackPrefix, $command);
		}
	}

	/**
	 * @param string  $fallbackPrefix
	 * @param Command $command
	 * @param null    $label
	 * @param bool    $overrideConfig
	 *
	 * @return bool
	 */
	public function register($fallbackPrefix, Command $command, $label = null, $overrideConfig = false){
		if($label === null){
			$label = $command->getName();
		}
		$label = strtolower(trim($label));

		//Check if command was disabled in config and for override
		if(!(($this->commandConfig[$label] ?? $this->commandConfig["default"] ?? true) or $overrideConfig)){
			return false;
		}
		$fallbackPrefix = strtolower(trim($fallbackPrefix));

		$registered = $this->registerAlias($command, false, $fallbackPrefix, $label);

		$aliases = $command->getAliases();
		foreach($aliases as $index => $alias){
			if(!$this->registerAlias($command, true, $fallbackPrefix, $alias)){
				unset($aliases[$index]);
			}
		}
		$command->setAliases($aliases);

		if(!$registered){
			$command->setLabel($fallbackPrefix . ":" . $label);
		}

		$command->register($this);

		return $registered;
	}

	/**
	 * @param Command $command
	 * @param         $isAlias
	 * @param         $fallbackPrefix
	 * @param         $label
	 *
	 * @return bool
	 */
	private function registerAlias(Command $command, $isAlias, $fallbackPrefix, $label){
		$this->knownCommands[$fallbackPrefix . ":" . $label] = $command;
		if(($command instanceof VanillaCommand or $isAlias) and isset($this->knownCommands[$label])){
			return false;
		}

		if(isset($this->knownCommands[$label]) and $this->knownCommands[$label]->getLabel() !== null and $this->knownCommands[$label]->getLabel() === $label){
			return false;
		}

		if(!$isAlias){
			$command->setLabel($label);
		}

		$this->knownCommands[$label] = $command;

		return true;
	}

	/**
     * Возвращает команду, соответствующую указанной командной строке, или null, если соответствующая команда не найдена.
     * Этот метод предназначен для обеспечения возможности обработки команд с пробелами в имени.
     * Указанные параметры будут соответственно изменены в зависимости от полученной согласованной команды.
     *
	 * @param string   $commandName reference parameter
	 * @param string[] $args reference parameter
	 *
	 * @return Command|null
	 */
	public function matchCommand(string &$commandName, array &$args){
		$count = min(count($args), 255);

		for($i = 0; $i < $count; ++$i){
			$commandName .= array_shift($args);
			if(($command = $this->getCommand($commandName)) instanceof Command){
				return $command;
			}

			$commandName .= " ";
		}

		return null;
	}

	/**
	 * @param CommandSender $sender
	 * @param Command       $command
	 * @param               $label
	 * @param array         $args
	 * @param int           $offset
	 */
	private function dispatchAdvanced(CommandSender $sender, Command $command, $label, array $args, $offset = 0){
		if(isset($args[$offset])){
			$argsTemp = $args;
			switch($args[$offset]){
				case "@a":
					$p = $this->server->getOnlinePlayers();
					if(count($p) <= 0){
						$sender->sendMessage(TextFormat::RED . "Нет игроков онлайн"); //TODO: add language
					}else{
						foreach($p as $player){
							$argsTemp[$offset] = $player->getName();
							$this->dispatchAdvanced($sender, $command, $label, $argsTemp, $offset + 1);
						}
					}
					break;
				case "@r":
					$players = $this->server->getOnlinePlayers();
					if(count($players) > 0){
						$argsTemp[$offset] = $players[array_rand($players)]->getName();
						$this->dispatchAdvanced($sender, $command, $label, $argsTemp, $offset + 1);
					}
					break;
				case "@p":
					if($sender instanceof Player){
						$argsTemp[$offset] = $sender->getName();
						$this->dispatchAdvanced($sender, $command, $label, $argsTemp, $offset + 1);
					}else{
						$sender->sendMessage(TextFormat::RED . "Вы должны быть игроком!"); //TODO: add language
					}
					break;
				default:
					$this->dispatchAdvanced($sender, $command, $label, $argsTemp, $offset + 1);
			}
		}else $command->execute($sender, $label, $args);
	}

	/**
	 * @param CommandSender $sender
	 * @param string        $commandLine
	 *
	 * @return bool
	 */
	public function dispatch(CommandSender $sender, $commandLine){
		$args = [];
		preg_match_all('/"((?:\\\\.|[^\\\\"])*)"|(\S+)/u', $commandLine, $matches);
		foreach($matches[0] as $k => $_){
			for($i = 1; $i <= 2; ++$i){
				if($matches[$i][$k] !== ""){
					$args[$k] = stripslashes($matches[$i][$k]);
					break;
				}
			}
		}

		if(count($args) === 0){
			return false;
		}

		$sentCommandLabel = strtolower(array_shift($args));
		$target = $this->getCommand($sentCommandLabel);

		if($target === null){
			return false;
		}

		$target->timings->startTiming();
		try{
			if($this->server->advancedCommandSelector){
				$this->dispatchAdvanced($sender, $target, $sentCommandLabel, $args);
			}else{
				$target->execute($sender, $sentCommandLabel, $args);
			}
		}catch(\Throwable $e){
			$sender->sendMessage(new TranslationContainer(TextFormat::RED . "%commands.generic.exception"));
			$this->server->getLogger()->critical($this->server->getLanguage()->translateString("pocketmine.command.exception", [$commandLine, (string) $target, $e->getMessage()]));
			$logger = $sender->getServer()->getLogger()->logException($e);
		}
		$target->timings->stopTiming();

		return true;
	}

	public function unregister(Command $command){
		foreach($this->knownCommands as $lbl => $cmd){
			if($cmd === $command){
				unset($this->knownCommands[$lbl]);
			}
		}

		$command->unregister($this);
		
		return true;
	}

	public function clearCommands(){
		foreach($this->knownCommands as $command){
			$command->unregister($this);
		}
		$this->knownCommands = [];
		$this->setDefaultCommands();
	}

	/**
	 * @param string $name
	 *
	 * @return null|Command
	 */
	public function getCommand($name){
		if(isset($this->knownCommands[$name])){
			return $this->knownCommands[$name];
		}

		return null;
	}

	/**
	 * @return Command[]
	 */
	public function getCommands(){
		return $this->knownCommands;
	}


	/**
	 * @return void
	 */
	public function registerServerAliases(){
		$values = $this->server->getCommandAliases();

		foreach($values as $alias => $commandStrings){
			if(strpos($alias, ":") !== false or strpos($alias, " ") !== false){
				$this->server->getLogger()->warning($this->server->getLanguage()->translateString("pocketmine.command.alias.illegal", [$alias]));
				continue;
			}

			$targets = [];
			$bad = [];
			$recursive = [];

			foreach($commandStrings as $commandString){
				$args = explode(" ", $commandString);
				$commandName = "";
				$command = $this->matchCommand($commandName, $args);

				if($command === null){
					$bad[] = $commandString;
				}elseif(strcasecmp($commandName, $alias) === 0){
					$recursive[] = $commandString;
				}else{
					$targets[] = $commandString;
				}
			}

			if(count($recursive) > 0){
				$this->server->getLogger()->warning($this->server->getLanguage()->translateString("pocketmine.command.alias.recursive", [$alias, implode(", ", $recursive)]));
				continue;
			}

			if(count($bad) > 0){
				$this->server->getLogger()->warning($this->server->getLanguage()->translateString("pocketmine.command.alias.notFound", [$alias, implode(", ", $bad)]));
				continue;
			}

			//These registered commands have absolute priority
			if(count($targets) > 0){
				$this->knownCommands[strtolower($alias)] = new FormattedCommandAlias(strtolower($alias), $targets);
			}else{
				unset($this->knownCommands[strtolower($alias)]);
			}

		}
	}
}