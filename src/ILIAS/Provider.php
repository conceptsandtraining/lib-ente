<?php
/******************************************************************************
 * An entity component framework for PHP.
 *
 * Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de>
 *
 * This software is licensed under GPLv3. You should have received a copy of
 * the license along with the code.
 */

namespace CaT\Ente\ILIAS;

use CaT\Ente\Component;

/**
 * Simple implementation for a provider, works in memory.
 */
abstract class Provider implements \CaT\Ente\Provider {
    /**
     * @var \ilObject
     */
    private $object;

    /**
     * @var Entity
     */
    private $entity;

    /**
     * @var array<string,Component>
     */
    private $components;

    final public function __construct(\ilObject $object) {
        $this->object = $object;
        $this->entity = new Entity($object);
    }

    /**
     * Build the component(s) of the given type for the given object.
     *
     * @param   string    $component_type
     * @param   \ilObject $object
     * @return  Component[]
     */
    abstract function buildComponentsOf($component_type, \ilObject $object);

    /**
     * @inheritdocs
     */
    abstract public function componentTypes();

    /**
     * @inheritdocs
     */
    final public function componentsOfType($component_type) {
        if (isset($this->components[$component_type])) {
            return $this->components[$component_type];
        }

        $components = $this->buildComponentsOf($component_type, $this->object);
        $this->checkComponentArray($components, $component_type);
        $this->components[$component_type] = $components;
        return $components;
    }

    /**
     * @inheritdocs
     */
    final public function entity() {
        return $this->entity;
    }

    /**
     * Checks if the $var is a valid component array for the given type.
     *
     * @param   mixed   $var
     * @param   string  $component_type
     * @return  bool
     */
    private function checkComponentArray($var, $component_type) {
        if (!is_array($var)) {
            throw new \UnexpectedValueException(
                "Expected buildComponentsOf to return an array, got ".gettype($var));
        }

        foreach($var as $component) {
            if (!($component instanceof $component_type)) {
                throw new \UnexpectedValueException(
                    "Expected build components to have the type $component_type, got ".get_class($component));
            }
            if (!$component->entity() === $this->entity()) {
                throw new \UnexpectedValueException(
                    'Expected build components to have the same entity as $this.');
            }
        }

    }
}
