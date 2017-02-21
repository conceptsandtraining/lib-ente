<?php
/******************************************************************************
 * An entity component framework for PHP.
 *
 * Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de>
 *
 * This software is licensed under GPLv3. You should have received a copy of
 * the license along with the code.
 */

namespace CaT\Ente\Simple;

/**
 * A simple component based on a closure.
 *
 * Evaluates the closure when run is called and returns the result.
 *
 * Intended to be used for testing.
 */
class CallClosure implements \CaT\Ente\Simple\Run {
    /**
     * @var \CaT\Ente\Component
     */
    private $entity;

    /**
     * @var \Closure
     */
    private $closure;

    public function __construct(Entity $entity, \Closure $closure) {
        $this->entity = $entity;
        $this->closure = $closure;
    }

    /**
     * @inheritdocs
     */
    public function entity() {
        return $this->entity;
    }

    /**
     * Call the closure with the entity and get the result.
     *
     * @return mixed
     */
    public function run() {
        $clsr = $this->closure;
        return $clsr($this->entity);
    }
}
