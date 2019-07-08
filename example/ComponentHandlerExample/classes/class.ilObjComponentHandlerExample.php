<?php

/* Copyright (c) 2018 Richard Klees <richard.klees@concepts-and-training.de> */

declare(strict_types=1);

use CaT\Ente\Simple;
use CaT\Ente\ILIAS;

require_once "Services/Repository/classes/class.ilObjectPlugin.php";

/**
 * Object of the plugin
 */
class ilObjComponentHandlerExample extends ilObjectPlugin
{
    use ILIAS\ilHandlerObjectHelper;

    protected function getDIC()
    {
        return $GLOBALS["DIC"];
    }

    /**
     * Init the type of the plugin. Same value as choosen in plugin.php
     */
    public function initType()
    {
        $this->setType("xleh");
    }

    /**
     * Returns an array with title => string[] entries containing the strings
     * provided for the object this plugin object is contained in.
     */
    public function getProvidedStrings() : array
    {
        $components = $this->getComponentsOfType(Simple\AttachString::class);

        $provided_strings = [];
        foreach ($components as $component) {
            $provided_strings[] = $component->attachedString();
        }

        return $provided_strings;
    }

    protected function getEntityRefId() : int
    {
        return (int)$$this->getDIC()["tree"]->getParentId($this->getRefId());
    }
}
