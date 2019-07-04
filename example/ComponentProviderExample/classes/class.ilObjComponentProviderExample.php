<?php

/* Copyright (c) 2018 Richard Klees <richard.klees@concepts-and-training.de> */

declare(strict_types=1);

use CaT\Plugins\ComponentProviderExample\UnboundProvider;
use CaT\Ente\ILIAS\ilProviderObjectHelper;

require_once "Services/Repository/classes/class.ilObjectPlugin.php";

/**
 * Object of the plugin
 */
class ilObjComponentProviderExample extends \ilObjectPlugin
{
    use ilProviderObjectHelper;

    protected function getDIC()
    {
        return $GLOBALS["DIC"];
    }

    /**
     * Init the type of the plugin. Same value as choosen in plugin.php
     */
    public function initType()
    {
        $this->setType("xlep");
    }

    /**
     * Creates ente-provider.
     */
    public function doCreate()
    {
        $this->createUnboundProvider(
            "crs",
            UnboundProvider::class,
            __DIR__ . "/UnboundProvider.php"
        );
    }

    /**
     * Get called if the object should be deleted.
     * Delete additional settings
     */
    public function doDelete()
    {
        $db = $this->plugin->settingsDB();
        $db->deleteFor($this->getId());
        $this->deleteUnboundProviders();
    }

    /**
     * Get the strings provided by this object.
     *
     * @return    string[]
     */
    public function getProvidedStrings()
    {
        $settings = $this->plugin->settingsDB()->getFor((int)$this->getId());
        $returns = [];
        foreach ($settings->providedStrings() as $s) {
            $returns[] = $s;
        }
        return $returns;
    }

    public function getTxtClosure()
    {
        return function ($code) {
            return $this->getPlugin()->txt($code);
        };
    }
}
