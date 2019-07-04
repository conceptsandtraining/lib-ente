<?php

/* Copyright (c) 2018 Richard Klees <richard.klees@concepts-and-training.de> */

declare(strict_types=1);

require_once "./Services/Repository/classes/class.ilRepositoryObjectPlugin.php";
require_once __DIR__ . "/../vendor/autoload.php";

/**
 * Plugin base class. Keeps all information the plugin needs
 */
class ilComponentHandlerExamplePlugin extends ilRepositoryObjectPlugin
{
    /**
     * Get the name of the Plugin
     *
     * @return string
     */
    function getPluginName()
    {
        return "ComponentHandlerExample";
    }

    /**
     * Defines custom uninstall action like delete table or something else
     */
    protected function uninstallCustom()
    {
    }
}
