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

    public function buildComponentsOf($component_type, Provider $provider) {
        assert(is_string($component_type));
        $provider->callsTo_buildComponentsOf[] = $component_type;
        $object = $provider->object();
        $entity = $provider->entity();
        if ($component_type == AttachString::class) {
            return [new AttachStringMemory($entity, "id: {$object->getId()}")];
        }
        if ($component_type == AttachInt::class) {
            return [new AttachIntMemory($entity, $object->getId())];
        }
        return [];
    }
}

class ILIAS_UnboundProviderTest extends PHPUnit_Framework_TestCase {
    /**
     * @inheritdocs
     */
    protected function unboundProvider() {
        $owner = $this
            ->getMockBuilder(\ilObject::class)
            ->setMethods(["getId"])
            ->getMock();

        $this->owner_id = 42;
        $owner
            ->method("getId")
            ->willReturn($this->owner_id);

        $provider = new Test_UnboundProvider($owner);

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
}
