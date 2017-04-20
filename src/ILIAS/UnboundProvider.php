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

/**
 * An unbound provider is a provider that currently is not bound to an
 * entity and can thus not produce components.
 */
abstract class UnboundProvider {
    /**
     * @var \ilObject
     */
    private $owner;


    final public function __construct(\ilObject $owner) {
        $this->owner = $owner;
    }


    /**
     * @inheritdocs
     */
    abstract public function componentTypes();

    /**
     * Build the component(s) of the given type for the given object.
     *
     * @param   string    $component_type
     * @param   Provider  $provider
     * @return  Component[]
     */
    abstract function buildComponentsOf($component_type, Provider $provider);

    /**
     * Get the owner object of the component.
     *
     * @return  \ilObject
     */
    final public function owner() {
        return $this->owner;
    }
}
