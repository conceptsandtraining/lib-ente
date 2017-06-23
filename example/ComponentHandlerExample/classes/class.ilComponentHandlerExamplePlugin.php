<?php
include_once("./Services/Repository/classes/class.ilRepositoryObjectPlugin.php");
require_once(__DIR__."/../vendor/autoload.php");

/**
 * Plugin base class. Keeps all information the plugin needs
 */
class ilComponentHandlerExamplePlugin extends ilRepositoryObjectPlugin {
    /**
     * @var \ilDBInterface
     */
    protected $ilDB;

	/**
	 * Get the name of the Plugin
	 *
	 * @return string
	 */
	function getPluginName() {
		return "ComponentHandlerExample";
	}

   /**
    * Defines custom uninstall action like delete table or something else
    */
    protected function uninstallCustom() {
    }

    /**
     * @var \CaT\Ente\ILIAS\Repository|null
     */
    protected $repository = null;

    /**
     * Get a repository for components.
     *
     * @return \CaT\Ente\Repository
     */
    public function getRepository() {
        global $DIC;
        if ($this->repository === null) {
            $this->repository = new \CaT\Ente\ILIAS\Repository($this->getProviderDB());
        }
        return $this->repository;
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
}
