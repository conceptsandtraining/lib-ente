<?php

/* Copyright (c) 2018 Richard Klees <richard.klees@concepts-and-training.de> */

declare(strict_types=1);

require_once "./Services/Repository/classes/class.ilObjectPluginGUI.php";
require_once "./Services/Form/classes/class.ilPropertyFormGUI.php";
require_once "./Services/Form/classes/class.ilTextInputGUI.php";

/**
 * Plugin object GUI class. Baseclass for all GUI action in ILIAS
 *
 * @ilCtrl_isCalledBy ilObjComponentProviderExampleGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
 * @ilCtrl_Calls ilObjComponentProviderExampleGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI, ilCommonActionDispatcherGUI
 */
class ilObjComponentProviderExampleGUI extends ilObjectPluginGUI
{
    const VALUES_FIELD_NAME = "values";
    const CMD_SAVE = "saveForm";
    const CMD_SHOW_CONTENT = "showContent";

    /**
     * @var \ilTemplate
     */
    protected $tpl;

    /**
     * @var \ilCtrl
     */
    protected $ctrl;

    /**
     * Called after parent constructor. It's possible to define some plugin special values
     */
    protected function afterConstructor()
    {
        global $DIC;
        $this->tpl = $DIC["tpl"];
        $this->ctrl = $DIC["ilCtrl"];
    }

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
        switch ($cmd) {
            case self::CMD_SAVE:
                $this->saveForm();
                break;
            case self::CMD_SHOW_CONTENT:
                $this->showContent();
                break;
            default:
                throw new \InvalidArgumentException("Unknown Command: '$cmd'");
        }
    }

    /**
     * Save values provided from form.
     */
    protected function saveForm()
    {
        $db = $this->plugin->settingsDB();
        $settings = $db->getFor((int)$this->object->getId());
        $settings = $settings->withProvidedStrings($_POST[self::VALUES_FIELD_NAME]);
        $db->update($settings);
        $this->ctrl->redirect($this, self::CMD_SHOW_CONTENT);
    }

    /**
     * Show the edit form.
     *
     * @return string
     */
    public function showContent()
    {
        $db = $this->plugin->settingsDB();
        $settings = $db->getFor((int)$this->object->getId());

        $form = new \ilPropertyFormGUI();
        $form->setTitle($this->txt("settings_form_title"));
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->addCommandButton(self::CMD_SAVE, $this->txt("save"));

        $input = new \ilTextInputGUI($this->txt("values"), self::VALUES_FIELD_NAME);
        $input->setMulti(true);
        $input->setMaxLength(64);
        $input->setValue($settings->providedStrings());
        $form->addItem($input);

        $this->tpl->setContent( $form->getHTML());
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
        return $this->plugin->txt($code);
    }
}
