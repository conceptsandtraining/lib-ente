<?php

/* Copyright (c) 2018 Richard Klees <richard.klees@concepts-and-training.de> */

declare(strict_types=1);

use \CaT\Plugins\ComponentProviderExample\Settings;

require_once "./Services/Repository/classes/class.ilRepositoryObjectPlugin.php";
require_once __DIR__ . "/../vendor/autoload.php";

/**
 * Plugin base class. Keeps all information the plugin needs
 */
class ilComponentProviderExamplePlugin extends \ilRepositoryObjectPlugin
{
    /**
     * @var \ilDBInterface
     */
    protected $db;

    /**
     * @var Settings\DB | null
     */
    protected $settings_db = null;

    /**
     * Object initialisation. Overwritten from ilPlugin.
     */
    protected function init()
    {
        global $DIC;
        $this->db = $DIC["ilDB"];
    }

    /**
     * Get the name of the Plugin
     *
     * @return string
     */
    function getPluginName()
    {
        return "ComponentProviderExample";
    }

    /**
     * Defines custom uninstall action like delete table or something else
     */
    protected function uninstallCustom()
    {
    }

    public function settingsDB() : Settings\DB
    {
        if ($this->settings_db === null) {
            $this->settings_db = new Settings\ilDB($this->db);
        }
        return $this->settings_db;
    }
}
