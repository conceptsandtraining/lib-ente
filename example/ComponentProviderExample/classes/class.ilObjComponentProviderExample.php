<?php
include_once("Services/Repository/classes/class.ilObjectPlugin.php");

/**
 * Object of the plugin
 */
class ilObjComponentProviderExample extends ilObjectPlugin {
	/**
	 * Init the type of the plugin. Same value as choosen in plugin.php
	 */
	public function initType() {
		$this->setType("xlep");
	}

	/**
	 * Get called if the object get be updated
	 * Update additoinal setting values
	 */
	public function doUpdate() {

	}

	/**
	 * Get called after object creation to read further information
	 */
	public function doRead() {

	}

	/**
	 * Get called if the object should be deleted.
	 * Delete additional settings
	 */
	public function doDelete() {
        $db = $this->plugin->settingsDB();
        $db->deleteFor($this->getId());
	}

	/**
	 * Get called if the object get be coppied.
	 * Copy additional settings to new object
	 */
	public function doCloneObject($new_obj, $a_target_id, $a_copy_id = null) {

	}
}
