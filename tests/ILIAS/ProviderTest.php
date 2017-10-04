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

require_once(__DIR__."/UnboundProviderTest.php");
require_once(__DIR__."/../ProviderTest.php");

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

        $this->unbound_provider_id = 23;
        $this->object_type = "object_type";
        $this->unbound_provider = new Test_UnboundProvider($this->unbound_provider_id, $owner, $this->object_type, []);

        $provider = new Provider($object, $this->unbound_provider);

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

        $this->assertEquals([AttachString::class], $this->unbound_provider->callsTo_buildComponentsOf);
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

    public function test_unboundProvider() {
        $provider = $this->provider();
        $this->assertEquals($this->unbound_provider, $provider->unboundProvider());
    }
}
