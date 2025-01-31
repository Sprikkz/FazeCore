<?php



namespace pocketmine\command;

use pocketmine\event\TextContainer;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\timings\TimingsHandler;
use pocketmine\event\TranslationContainer;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

abstract class Command{
	/** @var \stdClass */
	private static $defaultDataTemplate = null;

	/** @var string */
	private $name;
	/** @var \stdClass */
	protected $commandData = null;

	/** @var string */
	private $nextLabel;

	/** @var string */
	private $label;

	/** @var string[] */
	private $aliases = [];

	/**
	 * @var string[]
	 */
	private $activeAliases = [];

	/** @var CommandMap */
	private $commandMap = null;

	/** @var string */
	protected $description = "";

	/** @var string */
	protected $usageMessage;

	/** @var string */
	private $permissionMessage = null;

	/** @var TimingsHandler */
	public $timings;

	/**
	 * @param string   $name
	 * @param string   $description
	 * @param string   $usageMessage
	 * @param string[] $aliases
	 */
	public function __construct($name, $description = "", $usageMessage = null, array $aliases = []){
		$this->commandData = self::generateDefaultData();
		$this->name = $name;
		$this->setLabel($name);
		$this->setDescription($description);
		$this->usageMessage = $usageMessage === null ? "/" . $name : $usageMessage;
		$this->setAliases($aliases);
	}

    /**
     * Returns \stdClass containing command data
     *
     * @return \stdClass
     */
    public function getDefaultCommandData() : \stdClass{
        return $this->commandData;
    }

    /**
     * Generates modified command data for the specified player
     * for the available command package.
     *
     * @param Player $player
     *
     * @return \stdClass|null
     */
    public function generateCustomCommandData(Player $player){
        // TODO: fix command permission filtering on join
        /*if(!$this->testPermissionSilent($player)){
            return null;
        }*/
        $customData = clone $this->commandData;
        $customData->aliases = $this->getAliases();
        /*foreach($customData->overloads as &$overload){
            if(isset($overload->pocketminePermission) and !$player->hasPermission($overload->pocketminePermission)){
                unset($overload);
            }
        }*/
        return $customData;
    }

    /**
	 * @return \stdClass
	 */
	public function getOverloads() : \stdClass{
		return $this->commandData->overloads;
	}

	/**
	 * @param CommandSender $sender
	 * @param string        $commandLabel
	 * @param string[]      $args
	 *
	 * @return mixed
	 */
	public abstract function execute(CommandSender $sender, $commandLabel, array $args);

	/**
	 * @return string
	 */
	public function getName() : string{
		return $this->name;
	}

	/**
	 * @return string
	 */
	public function getPermission(){
		return $this->commandData->pocketminePermission ?? null;
	}


	/**
	 * @param string|null $permission
	 */
	public function setPermission($permission){
		if($permission !== null){
			$this->commandData->pocketminePermission = $permission;
		}else{
			unset($this->commandData->pocketminePermission);
		}
	}

	/**
	 * @param CommandSender $target
	 *
	 * @return bool
	 */
	public function testPermission(CommandSender $target){
		if($this->testPermissionSilent($target)){
			return true;
		}

		if($this->permissionMessage === null){
			$target->sendMessage(new TranslationContainer(TextFormat::RED . "%commands.generic.permission"));
		}elseif($this->permissionMessage !== ""){
			$target->sendMessage(str_replace("<permission>", $this->getPermission(), $this->permissionMessage));
		}

		return false;
	}

	/**
	 * @param CommandSender $target
	 *
	 * @return bool
	 */
	public function testPermissionSilent(CommandSender $target){
		if(($perm = $this->getPermission()) === null or $perm === ""){
			return true;
		}

		foreach(explode(";", $perm) as $permission){
			if($target->hasPermission($permission)){
				return true;
			}
		}

		return false;
	}

	/**
	 * @return string
	 */
	public function getLabel(){
		return $this->label;
	}

	/**
	 * @param $name
	 *
	 * @return bool
	 */
	public function setLabel($name){
		$this->nextLabel = $name;
		if(!$this->isRegistered()){
			if($this->timings instanceof TimingsHandler){
				$this->timings->remove();
			}
			$this->timings = new TimingsHandler("** Command: " . $name);
			$this->label = $name;

			return true;
		}

		return false;
	}

	/**
	 * Registers the command into a Command map
	 *
	 * @param CommandMap $commandMap
	 *
	 * @return bool
	 */
	public function register(CommandMap $commandMap){
		if($this->allowChangesFrom($commandMap)){
			$this->commandMap = $commandMap;

			return true;
		}

		return false;
	}

	/**
	 * @param CommandMap $commandMap
	 *
	 * @return bool
	 */
	public function unregister(CommandMap $commandMap){
		if($this->allowChangesFrom($commandMap)){
			$this->commandMap = null;
			$this->activeAliases = $this->commandData->aliases;
			$this->label = $this->nextLabel;

			return true;
		}

		return false;
	}

	/**
	 * @param CommandMap $commandMap
	 *
	 * @return bool
	 */
	private function allowChangesFrom(CommandMap $commandMap){
		return $this->commandMap === null or $this->commandMap === $commandMap;
	}

	/**
	 * @return bool
	 */
	public function isRegistered(){
		return $this->commandMap !== null;
	}

	/**
	 * @return string[]
	 */
	public function getAliases(){
		return $this->activeAliases;
	}

	/**
	 * @return string
	 */
	public function getPermissionMessage(){
		return $this->permissionMessage;
	}

	/**
	 * @return string
	 */
	public function getDescription(){
		return $this->commandData->description;
	}

	/**
	 * @return string
	 */
	public function getUsage(){
		return $this->usageMessage;
	}

	/**
	 * @param string[] $aliases
	 */
	public function setAliases(array $aliases){
		$this->commandData->aliases = $aliases;
		if(!$this->isRegistered()){
			$this->activeAliases = $aliases;
		}
	}

	/**
	 * @param string $description
	 */
	public function setDescription($description){
		$this->commandData->description = $description;
	}

	/**
	 * @param string $permissionMessage
	 */
	public function setPermissionMessage($permissionMessage){
		$this->permissionMessage = $permissionMessage;
	}

	/**
	 * @param string $usage
	 */
	public function setUsage($usage){
		$this->usageMessage = $usage;
	}

	/**
	 * @return \stdClass
	 */
	public static final function generateDefaultData() : \stdClass{
		if(self::$defaultDataTemplate === null){
			self::$defaultDataTemplate = json_decode(file_get_contents(Server::getInstance()->getFilePath() . "src/pocketmine/resources/command_default.json"));
		}
		return clone self::$defaultDataTemplate;
	}

	/**
	 * @param CommandSender $source
	 * @param string        $message
	 * @param bool          $sendToSource
	 */
	public static function broadcastCommandMessage(CommandSender $source, $message, $sendToSource = true){
		if($message instanceof TextContainer){
			$m = clone $message;
			$result = "§bConsole Sender:§7 " . ($source->getServer()->getLanguage()->get($m->getText()) !== $m->getText() ? "%" : "") . $m->getText() . "";

			$users = $source->getServer()->getPluginManager()->getPermissionSubscriptions(Server::BROADCAST_CHANNEL_ADMINISTRATIVE);
			$colored = TextFormat::GRAY . TextFormat::ITALIC . $result;

			$m->setText($result);
			$result = clone $m;
			$m->setText($colored);
			$colored = clone $m;
		}else{
			$users = $source->getServer()->getPluginManager()->getPermissionSubscriptions(Server::BROADCAST_CHANNEL_ADMINISTRATIVE);
			$result = new TranslationContainer("chat.type.admin", [$source->getName(), $message]);
			$colored = new TranslationContainer(TextFormat::GRAY . TextFormat::ITALIC . "%chat.type.admin", [$source->getName(), $message]);
		}

		if($sendToSource === true and !($source instanceof ConsoleCommandSender)){
			$source->sendMessage($message);
		}

		foreach($users as $user){
			if($user instanceof CommandSender){
				if($user instanceof ConsoleCommandSender){
					$user->sendMessage($result);
				}elseif($user !== $source){
					$user->sendMessage($colored);
				}
			}
		}
	}

	/**
	 * @return string
	 */
	public function __toString(){
		return $this->name;
	}
}
