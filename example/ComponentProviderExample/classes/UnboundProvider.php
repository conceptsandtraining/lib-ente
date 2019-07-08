<?php

/* Copyright (c) 2018 Richard Klees <richard.klees@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\ComponentProviderExample;

use \CaT\Ente\ILIAS\SeparatedUnboundProvider as Base;
use \CaT\Ente\Simple\AttachString;
use \CaT\Ente\Simple\AttachStringMemory;
use \CaT\Ente\Entity as IEntity;
use \CaT\Ente\Component as IComponent;

require_once __DIR__ . "/../vendor/autoload.php";

class UnboundProvider extends Base
{
    /**
     * @inheritdocs
     */
    public function componentTypes()
    {
        return [AttachString::class];
    }

    /**
     * @return IComponent[]
     */
    public function buildComponentsOf(string $component_type, IEntity $entity) : array
    {
        if ($component_type === AttachString::class) {
            $returns = [];
            foreach ($this->owner()->getProvidedStrings() as $s) {
                $returns[] = new AttachStringMemory($entity, $s);
            }
            return $returns;
        }
        throw new \InvalidArgumentException("Unexpected component type '$component_type'");
    }
}
