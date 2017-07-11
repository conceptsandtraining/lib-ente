<?php
include_once("Services/Repository/classes/class.ilObjectPlugin.php");

use CaT\Plugins\ComponentProviderExample\UnboundProvider;

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
     * @var	ProviderDB|null
    */
    protected $provider_db = null;

    /**
     * Get ente-provider-db.
     *
     * @return \CaT\Ente\ILIAS\ProviderDB
     */
    protected function getProviderDB() {
        global $DIC;
        if ($this->provider_db === null) {
            $this->provider_db = new \CaT\Ente\ILIAS\ilProviderDB
                ( $DIC->database()
                , $DIC->repositoryTree()
                , $DIC["ilObjDataCache"]
                );
        }
        return $this->provider_db;
    }

    /**
     * Delete all unbound providers of this object.
     *
     * @return null
     */
    protected function deleteUnboundProviders() {
        $db = $this->getProviderDB();
        $ups = $db->unboundProvidersOf($this);
        foreach ($ups as $up) {
            $db->delete($up);
        }
    }

	/**
	 * Creates ente-provider.
	 */
	public function doCreate() {
        $this->getProviderDB()->create($this, "crs", UnboundProvider::class, __DIR__."/UnboundProvider.php");
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
