<?php
/******************************************************************************
 * An entity component framework for PHP.
 *
 * Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de>
 *
 * This software is licensed under GPLv3. You should have received a copy of
 * the license along with the code.
 */

use CaT\Ente\Simple\Entity;
use CaT\Ente\Simple\Component;

class Simple_ComponentTest extends PHPUnit_Framework_TestCase {
    public function setUp() {
        $this->id = rand();
        $this->entity = new Entity($this->id);
        $this->component = new Component($this->entity, function(Entity $e) {
            return $e->id();
        });
    }

    public function test_entity() {
        $this->assertEquals($this->entity, $this->component->entity());
    }

    public function test_run() {
        $this->assertEquals($this->id, $this->component->run());
    }
}
