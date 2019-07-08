<?php

/* Copyright (c) 2018 Richard Klees <richard.klees@concepts-and-training.de> */

declare(strict_types=1);

require_once("./Services/Repository/classes/class.ilObjectPluginGUI.php");

/**
 * Plugin object GUI class. Baseclass for all GUI action in ILIAS
 *
 * @ilCtrl_isCalledBy ilObjComponentHandlerExampleGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
 * @ilCtrl_Calls ilObjComponentHandlerExampleGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI, ilCommonActionDispatcherGUI
 */
class ilObjComponentHandlerExampleGUI extends ilObjectPluginGUI
{
    const CMD_SHOW_CONTENT = "showContent";

    /**
     * @var \ilTemplate
     */
    protected $tpl;

    /**
     * @var \ILIAS\UI\Factory
     */
    protected $ui_factory;

    /**
     * @var \ILIAS\UI\Renderer
     */
    protected $ui_renderer;

    /**
     * Called after parent constructor. It's possible to define some plugin special values
     */
    protected function afterConstructor()
    {
        global $DIC;
        $this->tpl = $DIC["tpl"];
        $this->ui_factory = $DIC["ui.factory"];
        $this->ui_renderer = $DIC["ui.renderer"];
    }

    /**
     * Get type.  Same value as choosen in plugin.php
     */
    final function getType()
    {
        return "xleh";
    }

    /**
     * Handles all commmands of this class, centralizes permission checks
     */
    function performCommand($cmd)
    {
        switch ($cmd) {
            case self::CMD_SHOW_CONTENT:
                $this->showContent();
                break;
            default:
                throw new \InvalidArgumentException("Unknown Command: '$cmd'");
        }
    }

    public function showContent()
    {
        $items = $this->object->getProvidedStrings();
        $listing = $this->ui_factory->listing()->ordered($items);
        $this->tpl->setContent(
            $this->ui_renderer->render($listing)
        );
    }

    /**
     * After object has been created -> jump to this command
     */
    public function getAfterCreationCmd()
    {
        return self::CMD_SHOW_CONTENT;
    }

    /**
     * Get standard command
     */
    public function getStandardCmd()
    {
        return self::CMD_SHOW_CONTENT;
    }
}
