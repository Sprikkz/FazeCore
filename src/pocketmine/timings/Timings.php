<?php



namespace pocketmine\timings;

use pocketmine\entity\Entity;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\Player;
use pocketmine\scheduler\PluginTask;
use pocketmine\scheduler\TaskHandler;
use pocketmine\tile\Tile;
use function dechex;

abstract class Timings{
	/** @var bool */
	private static $initialized = false;

	/** @var TimingsHandler */
	public static $fullTickTimer;
	/** @var TimingsHandler */
	public static $serverTickTimer;
	/** @var TimingsHandler */
	public static $memoryManagerTimer;
	/** @var TimingsHandler */
	public static $garbageCollectorTimer;
	/** @var TimingsHandler */
	public static $titleTickTimer;
	/** @var TimingsHandler */
	public static $serverRawPacketTimer;
	/** @var TimingsHandler */
	public static $playerNetworkTimer;
	/** @var TimingsHandler */
	public static $playerNetworkReceiveTimer;
	/** @var TimingsHandler */
	public static $playerChunkOrderTimer;
	/** @var TimingsHandler */
	public static $playerChunkSendTimer;
	/** @var TimingsHandler */
	public static $connectionTimer;
	/** @var TimingsHandler */
	public static $schedulerTimer;
	/** @var TimingsHandler */
	public static $serverCommandTimer;
	/** @var TimingsHandler */
	public static $worldLoadTimer;
	/** @var TimingsHandler */
	public static $worldSaveTimer;
	/** @var TimingsHandler */
	public static $populationTimer;
	/** @var TimingsHandler */
	public static $generationCallbackTimer;
	/** @var TimingsHandler */
	public static $permissibleCalculationTimer;
	/** @var TimingsHandler */
	public static $permissionDefaultTimer;

	/** @var TimingsHandler */
	public static $entityMoveTimer;
	/** @var TimingsHandler */
	public static $playerCheckNearEntitiesTimer;
	/** @var TimingsHandler */
	public static $tickEntityTimer;
	/** @var TimingsHandler */
	public static $tickTileEntityTimer;

	/** @var TimingsHandler */
	public static $timerEntityBaseTick;
	/** @var TimingsHandler */
	public static $timerLivingEntityBaseTick;

	/** @var TimingsHandler */
	public static $schedulerSyncTimer;
	/** @var TimingsHandler */
	public static $schedulerAsyncTimer;

	/** @var TimingsHandler */
	public static $playerCommandTimer;

	/** @var TimingsHandler */
	public static $craftingDataCacheRebuildTimer;

	/** @var TimingsHandler[] */
	public static $entityTypeTimingMap = [];
	/** @var TimingsHandler[] */
	public static $tileEntityTypeTimingMap = [];
	/** @var TimingsHandler[] */
	public static $packetReceiveTimingMap = [];
	/** @var TimingsHandler[] */
	public static $packetSendTimingMap = [];
	/** @var TimingsHandler[] */
	public static $pluginTaskTimingMap = [];

	public static function init(){
		if(self::$initialized){
			return;
		}
		self::$initialized = true;

		self::$fullTickTimer = new TimingsHandler("Full Server Tick");
		self::$serverTickTimer = new TimingsHandler("** Full Server Tick", self::$fullTickTimer);
		self::$memoryManagerTimer = new TimingsHandler("Memory Manager");
		self::$garbageCollectorTimer = new TimingsHandler("Garbage Collector", self::$memoryManagerTimer);
		self::$titleTickTimer = new TimingsHandler("Console Title Tick");
		self::$serverRawPacketTimer = new TimingsHandler("Raw packets (Query)");
		self::$playerNetworkTimer = new TimingsHandler("Player Network Send");
		self::$playerNetworkReceiveTimer = new TimingsHandler("Player Network Receive");
		self::$playerChunkOrderTimer = new TimingsHandler("Player Order Chunks");
		self::$playerChunkSendTimer = new TimingsHandler("Player Send Chunks");
		self::$connectionTimer = new TimingsHandler("Connection Handler");
		self::$schedulerTimer = new TimingsHandler("Scheduler");
		self::$serverCommandTimer = new TimingsHandler("Server Command");
		self::$worldLoadTimer = new TimingsHandler("World Load");
		self::$worldSaveTimer = new TimingsHandler("World Save");
		self::$populationTimer = new TimingsHandler("World Population");
		self::$generationCallbackTimer = new TimingsHandler("World Generation Callback");
		self::$permissibleCalculationTimer = new TimingsHandler("Permissible Calculation");
		self::$permissionDefaultTimer = new TimingsHandler("Default Permission Calculation");

		self::$entityMoveTimer = new TimingsHandler("** entityMove");
		self::$playerCheckNearEntitiesTimer = new TimingsHandler("** checkNearEntities");
		self::$tickEntityTimer = new TimingsHandler("** tickEntity");
		self::$tickTileEntityTimer = new TimingsHandler("** tickTileEntity");

		self::$timerEntityBaseTick = new TimingsHandler("** entityBaseTick");
		self::$timerLivingEntityBaseTick = new TimingsHandler("** livingEntityBaseTick");

		self::$schedulerSyncTimer = new TimingsHandler("** Scheduler - Sync Tasks");
		self::$schedulerAsyncTimer = new TimingsHandler("** Scheduler - Async Tasks");

		self::$playerCommandTimer = new TimingsHandler("** playerCommand");
		self::$craftingDataCacheRebuildTimer = new TimingsHandler("** craftingDataCacheRebuild");

	}

	/**
	 * @param TaskHandler $task
	 * @param             $period
	 *
	 * @return TimingsHandler
	 */
	public static function getPluginTaskTimings(TaskHandler $task, $period){
		$ftask = $task->getTask();
		if($ftask instanceof PluginTask and $ftask->getOwner() !== null){
			$plugin = $ftask->getOwner()->getDescription()->getFullName();
		}elseif($task->timingName !== null){
			$plugin = "Scheduler";
		}else{
			$plugin = "Unknown";
		}

		$taskname = $task->getTaskName();

		$name = "Task: " . $plugin . " Runnable: " . $taskname;

		if($period > 0){
			$name .= "(interval:" . $period . ")";
		}else{
			$name .= "(Single)";
		}

		if(!isset(self::$pluginTaskTimingMap[$name])){
			self::$pluginTaskTimingMap[$name] = new TimingsHandler($name, self::$schedulerSyncTimer);
		}

		return self::$pluginTaskTimingMap[$name];
	}

	/**
	 * @param Entity $entity
	 *
	 * @return TimingsHandler
	 */
	public static function getEntityTimings(Entity $entity){
		$entityType = (new \ReflectionClass($entity))->getShortName();
		if(!isset(self::$entityTypeTimingMap[$entityType])){
			if($entity instanceof Player){
				self::$entityTypeTimingMap[$entityType] = new TimingsHandler("** tickEntity - EntityPlayer", self::$tickEntityTimer);
			}else{
				self::$entityTypeTimingMap[$entityType] = new TimingsHandler("** tickEntity - " . $entityType, self::$tickEntityTimer);
			}
		}

		return self::$entityTypeTimingMap[$entityType];
	}

	/**
	 * @param Tile $tile
	 *
	 * @return TimingsHandler
	 */
	public static function getTileEntityTimings(Tile $tile){
		$tileType = (new \ReflectionClass($tile))->getShortName();
		if(!isset(self::$tileEntityTypeTimingMap[$tileType])){
			self::$tileEntityTypeTimingMap[$tileType] = new TimingsHandler("** tickTileEntity - " . $tileType, self::$tickTileEntityTimer);
		}

		return self::$tileEntityTypeTimingMap[$tileType];
	}

	/**
	 * @param DataPacket $pk
	 *
	 * @return TimingsHandler
	 */
	public static function getReceiveDataPacketTimings(DataPacket $pk){
		if(!isset(self::$packetReceiveTimingMap[$pk::NETWORK_ID])){
			$pkName = (new \ReflectionClass($pk))->getShortName();
			self::$packetReceiveTimingMap[$pk::NETWORK_ID] = new TimingsHandler("** receivePacket - " . $pkName . " [0x" . dechex($pk::NETWORK_ID) . "]", self::$playerNetworkReceiveTimer);
		}

		return self::$packetReceiveTimingMap[$pk::NETWORK_ID];
	}


	/**
	 * @param DataPacket $pk
	 *
	 * @return TimingsHandler
	 */
	public static function getSendDataPacketTimings(DataPacket $pk){
		if(!isset(self::$packetSendTimingMap[$pk::NETWORK_ID])){
			$pkName = (new \ReflectionClass($pk))->getShortName();
			self::$packetSendTimingMap[$pk::NETWORK_ID] = new TimingsHandler("** sendPacket - " . $pkName . " [0x" . dechex($pk::NETWORK_ID) . "]", self::$playerNetworkTimer);
		}

		return self::$packetSendTimingMap[$pk::NETWORK_ID];
	}

}