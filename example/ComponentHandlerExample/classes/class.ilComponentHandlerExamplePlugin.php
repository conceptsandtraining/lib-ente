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
     * @var \CaT\Plugins\ComponentHandlerExamplePlugin\Settings\DB|null
     */
    protected $settings_db = null;

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
}
