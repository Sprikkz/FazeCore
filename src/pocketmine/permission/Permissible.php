<?php



namespace pocketmine\permission;

use pocketmine\plugin\Plugin;

interface Permissible extends ServerOperator {

	/**
     * Checks if this instance has an overridden permission
	 *
	 * @param string|Permission $name
	 *
	 * @return bool
	 */
	public function isPermissionSet($name);

	/**
     * Returns the permission value if it is overridden, or the default value if not.
	 *
	 * @param string|Permission $name
	 *
	 * @return mixed
	 */
	public function hasPermission($name);

	/**
	 * @param Plugin $plugin
	 * @param string $name
	 * @param bool   $value
	 *
	 * @return PermissionAttachment
	 */
	public function addAttachment(Plugin $plugin, $name = null, $value = null);

	/**
	 * @param PermissionAttachment $attachment
	 *
	 * @return void
	 */
	public function removeAttachment(PermissionAttachment $attachment);


	/**
	 * @return void
	 */
	public function recalculatePermissions();



	/**
	 * @return Permission[]
	 */
	public function getEffectivePermissions();

}