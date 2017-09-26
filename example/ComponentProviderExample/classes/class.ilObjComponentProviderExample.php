<?php
include_once("Services/Repository/classes/class.ilObjectPlugin.php");

use CaT\Plugins\ComponentProviderExample\UnboundProvider;
use CaT\Ente\ILIAS\ilProviderObjectHelper;

/**
 * Object of the plugin
 */
class ilObjComponentProviderExample extends ilObjectPlugin {
	use ilProviderObjectHelper;

	protected function getDIC() {
		return $GLOBALS["DIC"];
	}

	/**
	 * Init the type of the plugin. Same value as choosen in plugin.php
	 */
	public function initType() {
		$this->setType("xlep");
	}

	/**
	 * Creates ente-provider.
	 */
	public function doCreate() {
		$this->createUnboundProvider("crs", UnboundProvider::class, __DIR__."/UnboundProvider.php");
	}

	/**
	 * Get called if the object should be deleted.
	 * Delete additional settings
	 */
	public function doDelete() {
        $db = $this->plugin->settingsDB();
        $db->deleteFor($this->getId());
        $this->deleteUnboundProviders();
	}
}
