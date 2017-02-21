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
use CaT\Ente\Simple\Provider;
use CaT\Ente\Simple\Run;
use CaT\Ente\Simple\CallClosure;

require_once(__DIR__."/../ProviderTest.php");

class Simple_ProviderTest extends ProviderTest {
    /**
     * @inheritdocs
     */
    protected function provider() {
        $entity = new Entity(0);
        $provider = new Provider($entity);
        $component = new CallClosure($entity, function(Entity $e) {
            return $e->id();
        }); 
        $provider->addComponent($component);
        return $provider;
    }

    /**
     * @inheritdocs
     */
    protected function doesNotProvideComponentType() {
        return [self::class];
    }

    public function test_cannot_add_for_other_entity() {
        $provider = $this->provider();

        $entity = new Entity(1);
        $component = new CallClosure($entity, function(Entity $e) {
            return $e->id();
        }); 

        try {
            $provider->addComponent($component);
            $this->assertFalse("This should not happen.");
        } 
        catch (\InvalidArgumentException $e) {
            $this->assertTrue(true);
        }
    }

    public function test_componentTypes() {
        $provider = $this->provider();
        $this->assertEquals([Run::class], $provider->componentTypes());
    }
}
