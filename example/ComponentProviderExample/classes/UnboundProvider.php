<?php

namespace CaT\Plugins\ComponentProviderExample;

require_once(__DIR__."/../vendor/autoload.php");

use \CaT\Ente\ILIAS\UnboundProvider as Base;
use \CaT\Ente\Simple\AttachString;
use \CaT\Ente\Simple\AttachStringMemory;
use \CaT\Ente\ILIAS\Provider;

class UnboundProvider extends Base {
    /**
     * @inheritdocs
     */
    public function componentTypes() {
        return [AttachString::class];
    }

    /**
     * Build the component(s) of the given type for the given object.
     *
     * @param   string    $component_type
     * @param   Provider  $provider
     * @return  Component[]
     */
    public function buildComponentsOf($component_type, Provider $provider) {
        if ($component_type === AttachString::class) {
            $settings = $this->getSettingsDB()->getFor($this->owner()->getId());
            $returns = [];
            foreach($settings->providedStrings() as $s) {
                $returns[] = new AttachStringMemory($provider->entity(), $s);
            }
            return $returns;
        }
        throw new \InvalidArgumentException("Unexpected component type '$component_type'");
    }

    /**
     * Get the settings DB.
     *
     * @return Settings\DB
     */
    public function getSettingsDB() {
        $DIC = $this->DIC();
        return new Settings\ilDB($DIC["ilDB"]);
    }
}
