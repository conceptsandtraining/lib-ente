<?php

/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\ComponentProviderExample;

use function foo\func;
use Pimple\Container;

trait DI
{
    public function getObjectDIC(
        \ilObjComponentProviderExample $object,
        $dic
    ): Container {
        $container = new Container();

        $container["ilDB"] = function ($c) use ($dic) {
            return $dic["ilDB"];
        };
        $container["ilCtrl"] = function ($c) use ($dic) {
            return $dic["ilCtrl"];
        };
        $container["tpl"] = function ($c) use ($dic) {
            return $dic["tpl"];
        };
        $container["ilAccess"] = function ($c) use ($dic) {
            return $dic["ilAccess"];
        };
        $container["ilTabs"] = function ($c) use ($dic) {
            return $dic["ilTabs"];
        };
        $container["txtclosure"] = function ($c) use ($object) {
            return $object->getTxtClosure();
        };

        $container["settings.db"] = function ($c) {
            return new Settings\ilDB($c["ilDB"]);
        };
        $container["settings.gui"] = function ($c) {
            require_once "Settings/class.ilProvidedComponentsGUI.php";
            return $c["ilCtrl"]->getLinkTargetByClass(
                "ilProvidedComponentsGUI",
                \ilProvidedComponentsGUI::CMD_SHOW_CONTENT,
                "",
                false,
                false
            );
        };
        $container["settings.gui.link"] = function ($c) {
            require_once "Settings/class.ilProvidedComponentsGUI.php";
            return new \ilProvidedComponentsGUI(
                $c["tpl"],
                $c["ilCtrl"],
                $c["txtclosure"],
                $c["settings.db"]
            );
        };

        return $container;
    }
}
