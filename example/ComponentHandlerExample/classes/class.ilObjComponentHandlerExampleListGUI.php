<?php

/* Copyright (c) 2018 Richard Klees <richard.klees@concepts-and-training.de> */

declare(strict_types=1);

require_once "./Services/Repository/classes/class.ilObjectPluginListGUI.php";

/**
 * List gui class for plugin object in repository
 */
class ilObjComponentHandlerExampleListGUI extends ilObjectPluginListGUI
{
    /**
     * @inheritDoc
     */
    public function initType()
    {
        $this->setType("xleh");
    }

    /**
     * @inheritDoc
     */
    function getGuiClass()
    {
        return "ilObjComponentHandlerExampleGUI";
    }

    /**
     * @inheritDoc
     */
    function initCommands()
    {
        return array(array("permission" => "read",
            "cmd" => "showContent",
            "default" => true
        ),
            array("permission" => "write",
                "cmd" => "editProperties",
                "txt" => $this->txt("edit"),
                "default" => false
            )
        );
    }

    /**
     * @inheritDoc
     */
    protected function initListActions()
    {
        $this->info_screen_enabled = true;
        $this->copy_enabled = true;
        $this->cut_enabled = true;
        $this->subscribe_enabled = true;
        $this->link_enabled = false;
        $this->payment_enabled = false;
        $this->timings_enabled = false;
    }
}
