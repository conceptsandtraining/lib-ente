<?php
/******************************************************************************
 * An entity component framework for PHP.
 *
 * Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de>
 *
 * This software is licensed under GPLv3. You should have received a copy of
 * the license along with the code.
 */

use CaT\Ente\ILIAS\Entity;
use CaT\Ente\ILIAS\Provider;
use CaT\Ente\Simple;
use CaT\Ente\Simple\AttachString;
use CaT\Ente\Simple\AttachStringMemory;
use CaT\Ente\Simple\AttachInt;
use CaT\Ente\Simple\AttachIntMemory;

if (!class_exists("ilObject")) {
    require_once(__DIR__."/ilObject.php");
}

require_once(__DIR__."/../ProviderTest.php");

class Test_Provider extends Provider {
    public $callsTo_buildComponentsOf = [];

    public function componentTypes() {
        return [AttachString::class, AttachInt::class];
    }

    public function buildComponentsOf($component_type, \ilObject $object) {
        assert(is_string($object));
        $this->callsTo_buildComponentsOf[] = $component_type;
        if ($component_type == AttachString::class) {
            return [new AttachStringMemory($this->entity(), "id: {$object->getId()}")];
        }
        if ($component_type == AttachInt::class) {
            return [new AttachIntMemory($this->entity(), $object->getId())];
        }
        return [];
    }
}

class ILIAS_ProviderTest extends ProviderTest {
    /**
     * @inheritdocs
     */
    protected function provider() {
        $object = $this
            ->getMockBuilder(\ilObject::class)
            ->setMethods(["getId"])
            ->getMock();

        $this->object_id = 23;
        $object
            ->method("getId")
            ->willReturn($this->object_id);

        $owner = $this
            ->getMockBuilder(\ilObject::class)
            ->setMethods(["getId"])
            ->getMock();

        $this->owner_id = 42;
        $owner
            ->method("getId")
            ->willReturn($this->owner_id);

        $provider = new Test_Provider($object, $owner);

        return $provider;
    }

    /**
     * @inheritdocs
     */
    protected function doesNotProvideComponentType() {
        return [self::class];
    }

    public function test_entity_id_is_object_id() {
        $provider = $this->provider();
        $this->assertEquals($this->object_id, $provider->entity()->id());
    }

    public function test_componentTypes() {
        $provider = $this->provider();
        $this->assertEquals([AttachString::class, AttachInt::class], $provider->componentTypes());
    }

    public function test_provided_components() {
        $provider = $this->provider();

        $attached_strings = $provider->componentsOfType(AttachString::class);
        $this->assertCount(1, $attached_strings);
        $attached_string = $attached_strings[0];
        $this->assertEquals("id: {$this->object_id}", $attached_string->attachedString());

        $attached_ints = $provider->componentsOfType(AttachInt::class);
        $this->assertCount(1, $attached_ints);
        $attached_int = $attached_ints[0];
        $this->assertEquals($this->object_id, $attached_int->attachedInt());
    }

    public function test_caching() {
        $provider = $this->provider();
        $provider->componentsOfType(AttachString::class);
        $provider->componentsOfType(AttachString::class);

        $this->assertEquals([AttachString::class], $provider->callsTo_buildComponentsOf);
    }

    public function test_object() {
        $object = $this->provider()->object();
        $this->assertInstanceOf(\ilObject::class, $object);
        $this->assertEquals($this->object_id, $object->getId());
    }

    public function test_owner() {
        $owner = $this->provider()->owner();
        $this->assertInstanceOf(\ilObject::class, $owner);
        $this->assertEquals($this->owner_id, $owner->getId());
    }
}
