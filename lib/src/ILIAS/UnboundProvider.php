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
     * @var int
     */
    private $id;

    /**
     * @var \ilObject
     */
    private $owner;

    /**
     * @var string
     */
    private $object_type;

    /**
     * @var ArrayAccess|array
     */
    private $dic;

    final public function __construct($id, \ilObject $owner, $object_type, $dic) {
        assert('is_int($id)');
        $this->id = $id;
        $this->owner = $owner;
        assert('is_string($object_type)');
        $this->object_type = $object_type;
        assert('is_array($dic) || $dic instanceof \ArrayAccess');
        $this->dic = $dic;
    }

    /**
     * @inheritdocs
     */
    abstract public function componentTypes();

    /**
     * Build the component(s) of the given type for the given object.
     *
     * @param   string    $component_type
     * @param   Entity    $provider
     * @return  Component[]
     */
    abstract public function buildComponentsOf($component_type, Entity $entity);

    /**
     * Get the id of this.
     *
     * @return  int
     */
    final public function id() {
        return $this->id;
    }

    /**
     * Get the owner object of the component.
     *
     * @return  \ilObject
     */
    final public function owner() {
        return $this->owner;
    }

    /**
     * Get the object type this binds to.
     *
     * @return  string
     */
    final public function objectType() {
        return $this->object_type;
    }

    /**
     * Get the dependency injection container injected to the constructor.
     *
     * @return ArrayAccess|array
     */
    final public function DIC() {
        return $this->dic;
    }
}
