<?php



namespace pocketmine\event\player;

use pocketmine\event\TextContainer;
use pocketmine\Player;

/**
 * Called when a player joins the server, after all spawn packets have been sent.
 */
class PlayerJoinEvent extends PlayerEvent {
	public static $handlerList = null;

	/** @var string|TextContainer */
	protected $joinMessage;

	/**
	 * PlayerJoinEvent constructor.
	 *
	 * @param Player $player
	 * @param        $joinMessage
	 */
	public function __construct(Player $player, $joinMessage){
		$this->player = $player;
		$this->joinMessage = $joinMessage;
	}

	/**
	 * @param string|TextContainer $joinMessage
	 */
	public function setJoinMessage($joinMessage){
		$this->joinMessage = $joinMessage;
	}

	/**
	 * @return string|TextContainer
	 */
	public function getJoinMessage(){
		return $this->joinMessage;
	}

}