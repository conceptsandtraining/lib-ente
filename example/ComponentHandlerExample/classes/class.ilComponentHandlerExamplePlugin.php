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
     * Object initialisation. Overwritten from ilPlugin.
     */
    protected function init() {
    }

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
     * Get a repository for components.
     *
     * @return \CaT\Ente\Repository
     */
    public function getRepository() {
        $repo = new \CaT\Ente\Simple\Repository();
        $entity = new \CaT\Ente\Simple\Entity(0); 
        $provider1 = new \CaT\Ente\Simple\Provider($entity);
        $provider1->addComponent
            (new \CaT\Ente\Simple\AttachStringMemory($entity, "a string"));
        $provider1->addComponent
            (new \CaT\Ente\Simple\AttachStringMemory($entity, "another string"));
        $provider2 = new \CaT\Ente\Simple\Provider($entity);
        $provider2->addComponent
            (new \CaT\Ente\Simple\AttachStringMemory($entity, "yet another string"));
        $repo->addProvider($provider1);
        $repo->addProvider($provider2);
        return $repo;
    }
}
