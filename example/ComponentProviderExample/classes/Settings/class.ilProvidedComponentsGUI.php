<?php

/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

use CaT\Plugins\ComponentProviderExample\Settings\DB;

class ilProvidedComponentsGUI
{
    const VALUES_FIELD_NAME = "values";
    const CMD_SAVE = "saveForm";
    const CMD_SHOW_CONTENT = "showContent";

    /**
     * @var ilTemplate
     */
    protected $tpl;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var Closure
     */
    protected $txt;

    /**
     * @var DB
     */
    protected $db;

    public function __construct(
        ilTemplate $tpl,
        ilCtrl $ctrl,
        Closure $txt,
        DB $db
    ) {
        $this->tpl = $tpl;
        $this->ctrl = $ctrl;
        $this->txt = $txt;
        $this->db = $db;
    }

    /**
     * Handles all commmands of this class, centralizes permission checks
     */
    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd();
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
        $settings = $this->db->getFor((int)$this->object->getId());
        $settings = $settings->withProvidedStrings($_POST[self::VALUES_FIELD_NAME]);
        $this->db->update($settings);
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

    protected function txt(string $code) :  string
    {
        return call_user_func($this->txt, $code);
    }
}
