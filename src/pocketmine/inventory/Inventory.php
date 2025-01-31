<?php



namespace pocketmine\inventory;

use pocketmine\item\Item;
use pocketmine\Player;

interface Inventory {
	const MAX_STACK = 64;

	public function getSize();

	public function getMaxStackSize();

	/**
	 * @param int $size
	 */
	public function setMaxStackSize($size);

	public function getName();

	public function getTitle();

	/**
	 * @param int $index
	 *
	 * @return Item
	 */
	public function getItem($index);

	/**
	 * Puts an Item in a slot.
	 * If a plugin refuses the update or $index is invalid, it'll return false
	 * If a source Player is specified, it won't send a Inventory update to it
	 *
	 * @param int  $index
	 * @param Item $item
	 *
	 * @return bool
	 */
	public function setItem($index, Item $item);

	/**
	 * Stores the given Items in the inventory. This will try to fill
	 * existing stacks and empty slots as well as it can.
	 *
	 * Returns the Items that did not fit.
	 *
	 * @param array $slots
	 *
	 * @return Item[]
	 * @internal param Item ...$item
	 *
	 */
	public function addItem(...$slots);

	/**
	 * Checks if a given Item can be added to the inventory
	 *
	 * @param Item $item
	 *
	 * @return bool
	 */
	public function canAddItem(Item $item);

	/**
	 * Removes the given Item from the inventory.
	 * It will return the Items that couldn't be removed.
	 *
	 * @param array $slots
	 *
	 * @return Item[]
	 * @internal param Item ...$item
	 *
	 */
	public function removeItem(...$slots);

	/**
	 * @param bool $includeEmpty
	 *
	 * @return Item[]
	 */
	public function getContents(bool $includeEmpty = false) : array;

	/**
	 * @param Item[] $items
	 * @param bool   $send
	 */
	public function setContents(array $items, bool $send = true);

	/**
	 * @param Player|Player[] $target
	 */
	public function sendContents($target);

	/**
	 * @param int             $index
	 * @param Player|Player[] $target
	 */
	public function sendSlot($index, $target);

	/**
	 * Checks if the inventory contains any Item with the same material data.
	 * It will check id, amount, and metadata (if not null)
	 *
	 * @param Item $item
	 *
	 * @return bool
	 */
	public function contains(Item $item);

	/**
	 * Will return all the Items that has the same id and metadata (if not null).
	 * Won't check amount
	 *
	 * @param Item $item
	 *
	 * @return Item[]
	 */
	public function all(Item $item);

	/**
	 * Will return the first slot has the same id and metadata (if not null) as the Item.
	 * -1 if not found, will check amount
	 *
	 * @param Item $item
	 *
	 * @return int
	 */
	public function first(Item $item);

	/**
	 * Returns the first empty slot, or -1 if not found
	 *
	 * @return int
	 */
	public function firstEmpty();

	/**
	 * Returns whether the given slot is empty.
	 *
	 * @param int $index
	 *
	 * @return bool
	 */
	public function isSlotEmpty(int $index) : bool;

	/**
	 * Will remove all the Items that has the same id and metadata (if not null)
	 *
	 * @param Item $item
	 */
	public function remove(Item $item);

	/**
	 * Will clear a specific slot
	 *
	 * @param int $index
	 *
	 * @return bool
	 */
	public function clear($index);

	/**
	 * Clears all the slots
	 */
	public function clearAll();

	/**
	 * Gets all the Players viewing the inventory
	 * Players will view their inventory at all times, even when not open.
	 *
	 * @return Player[]
	 */
	public function getViewers();

	/**
	 * @return InventoryType
	 */
	public function getType();

	/**
	 * @return InventoryHolder
	 */
	public function getHolder();

	/**
	 * @param Player $who
	 */
	public function onOpen(Player $who);

	/**
	 * Tries to open the inventory to a player
	 *
	 * @param Player $who
	 *
	 * @return bool
	 */
	public function open(Player $who);

	/**
	 * @param Player $who
	 *
	 * @return mixed
	 */
	public function close(Player $who);

	/**
	 * @param Player $who
	 */
	public function onClose(Player $who);

	/**
	 * @param int  $index
	 * @param Item $before
	 * @param bool $send
	 */
	public function onSlotChange($index, $before, $send);
}
