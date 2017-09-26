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
use CaT\Ente\ILIAS\UnboundProvider;
use CaT\Ente\Simple;
use CaT\Ente\Simple\AttachString;
use CaT\Ente\Simple\AttachStringMemory;
use CaT\Ente\Simple\AttachInt;
use CaT\Ente\Simple\AttachIntMemory;

if (!class_exists("ilObject")) {
    require_once(__DIR__."/ilObject.php");
}

class Test_UnboundProvider extends UnboundProvider {
    public function componentTypes() {
        return [AttachString::class, AttachInt::class];
    }

    public function buildComponentsOf($component_type, Entity $entity) {
        assert(is_string($component_type));
        $this->callsTo_buildComponentsOf[] = $component_type;
        $object = $entity->object();
        $entity = $entity;
        if ($component_type == AttachString::class) {
            return [new AttachStringMemory($entity, "id: {$object->getId()}")];
        }
        if ($component_type == AttachInt::class) {
            return [new AttachIntMemory($entity, $object->getId())];
        }
        return [];
    }

    public function _DIC() {
        return $this->DIC();
    }
}

class ILIAS_UnboundProviderTest extends PHPUnit_Framework_TestCase {
    /**
     * @inheritdocs
     */
    protected function unboundProvider($dic = []) {
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

        $provider = new Test_UnboundProvider($this->unbound_provider_id, $owner, $this->object_type, $dic);

        return $provider;
    }

    public function test_componentTypes() {
        $unbound_provider = $this->unboundProvider();
        $this->assertEquals([AttachString::class, AttachInt::class], $unbound_provider->componentTypes());
    }

    public function test_owner() {
        $owner = $this->unboundProvider()->owner();
        $this->assertInstanceOf(\ilObject::class, $owner);
        $this->assertEquals($this->owner_id, $owner->getId());
    }

    public function test_id() {
        $unbound_provider = $this->unboundProvider();
        $this->assertEquals($this->unbound_provider_id, $unbound_provider->id());
    }

    public function test_object_type() {
        $unbound_provider = $this->unboundProvider();
        $this->assertEquals($this->object_type, $unbound_provider->objectType());
    }

    public function test_dic() {
        $dic = ["foo" => "bar"];
        $unbound_provider = $this->unboundProvider($dic);
        $this->assertEquals($dic, $unbound_provider->_DIC());
    }
}
