<?php

/* Copyright (c) 2018 Richard Klees <richard.klees@concepts-and-training.de> */

declare(strict_types=1);

require_once "./Services/Repository/classes/class.ilObjectPluginGUI.php";
require_once "./Services/Form/classes/class.ilPropertyFormGUI.php";
require_once "./Services/Form/classes/class.ilTextInputGUI.php";

use CaT\Plugins\ComponentProviderExample\DI;

/**
 * Plugin object GUI class. Baseclass for all GUI action in ILIAS
 *
 * @ilCtrl_isCalledBy ilObjComponentProviderExampleGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
 * @ilCtrl_Calls ilObjComponentProviderExampleGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI, ilCommonActionDispatcherGUI
 * @ilCtrl_Calls ilObjComponentProviderExampleGUI: ilProvidedComponentsGUI
 */
class ilObjComponentProviderExampleGUI extends ilObjectPluginGUI
{
    use DI;

    const CMD_SHOW_CONTENT = "showContent";

    const TAB_SETTINGS = "tab_settings";

    /**
     * @var \ilTemplate
     */
    protected $tpl;

    /**
     * @var \ilCtrl
     */
    protected $ctrl;

    /**
     * @var ArrayAccess
     */
    protected $dic;

    /**
     * @var ilAccess
     */
    protected $access;

    /**
     * @var ilTabsGUI
     */
    protected $tabs;

    /**
     * Get type.  Same value as choosen in plugin.php
     */
    final function getType()
    {
        return "xlep";
    }

    /**
     * Handles all commmands of this class, centralizes permission checks
     */
    function performCommand($cmd)
    {
        $this->initClassProperties();

        $next_class = $this->ctrl->getNextClass();
        switch($next_class) {
            case "ilprovidedcomponentsgui":
                $gui = $this->dic["settings.gui"];
                $this->ctrl->forwardCommand($gui);
                break;
            default:
                switch ($cmd) {
                    case self::CMD_SHOW_CONTENT:
                        $this->redirectSettings();
                        break;
                    default:
                        throw new \InvalidArgumentException("Unknown Command: '$cmd'");
                }
        }

    }

    protected function initClassProperties()
    {
        $this->dic = $this->getObjDic();
        $this->tpl = $this->dic["tpl"];
        $this->ctrl = $this->dic["ilCtrl"];
        $this->access = $this->dic["ilAccess"];
        $this->tabs = $this->dic["ilTabs"];
    }

    protected function redirectSettings()
    {
        $link = $this->dic["settings.gui.link"];
        $this->ctrl->redirectToUrl($link);
    }

    protected function setTabs()
    {
        $this->addInfoTab();

        if ($this->access->checkAccess("write", "", $this->object->getRefId())) {
            $link = $this->dic["settings.gui.link"];
            $this->tabs->addTab(
                self::TAB_SETTINGS,
                $this->txt(self::TAB_SETTINGS),
                $link
            );
        }

        $this->addPermissionTab();
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

    protected function txt(string $code) :  string
    {
        return $this->txt($code);
    }

    protected function getObjDic() : Pimple\Container
    {
        global $DIC;
        return $this->getObjectDIC($this->object, $DIC);
    }
}
