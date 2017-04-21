<?php
/******************************************************************************
 * An entity component framework for PHP.
 *
 * Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de>
 *
 * This software is licensed under GPLv3. You should have received a copy of
 * the license along with the code.
 */

use CaT\Ente\ILIAS\UnboundProvider;
use CaT\Ente\ILIAS\ilProviderDB;
use CaT\Ente\Simple\AttachString;
use CaT\Ente\Simple\AttachInt;

if (!class_exists("ilObject")) {
    require_once(__DIR__."/ilObject.php");
}

if (!interface_exists("ilDBInterface")) {
    require_once(__DIR__."/ilDBInterface.php");
}

class Test_ilProviderDB extends ilProviderDB {
    public $object_ref = [];
    protected function buildObjectByRefId($ref_id) {
        assert(isset($this->object_ref[$ref_id]));
        return $this->object_ref[$ref_id];
    }
    public $object_obj = [];
    protected function buildObjectByObjId($obj_id) {
        assert(isset($this->object_obj[$obj_id]));
        return $this->object_obj[$obj_id];
    }
}

class ILIAS_ilProviderDBTest extends PHPUnit_Framework_TestCase {
    protected function il_db_mock() {
        return $this
            ->getMockBuilder(\ilDBInterface::class)
            ->setMethods(["nextId","createTable","addPrimaryKey","createSequence",
                          "tableExists","addIndex","query","insert","fetchAssoc","quote", "manipulate"])
            ->getMock();
    }

    public function test_createTables() {
        $il_db = $this->il_db_mock();

        $provider_table =
            [ "id" => ["type" => "integer", "length" => 4, "notnull" => true]
            , "owner" => ["type" => "integer", "length" => 4, "notnull" => true]
            , "object_type" => ["type" => "string", "length" => 4, "notnull" => true]
            , "class_name" => ["type" => "string", "length" => ilProviderDB::CLASS_NAME_LENGTH, "notnull" => true]
            , "include_path" => ["type" => "string", "length" => ilProviderDB::PATH_LENGTH, "notnull" => true]
            ];
        $component_table =
            [ "id" => ["type" => "integer", "length" => 4, "notnull" => true]
            , "component_type" => ["type" => "string", "length" => ilProviderDB::CLASS_NAME_LENGTH, "notnull" => true]
            ];

        $il_db
            ->expects($this->exactly(2))
            ->method("createTable")
            ->withConsecutive(
                [ilProviderDB::PROVIDER_TABLE, $provider_table],
                [ilProviderDB::COMPONENT_TABLE, $component_table]);

        $il_db
            ->expects($this->exactly(2))
            ->method("tableExists")
            ->withConsecutive([ilProviderDB::PROVIDER_TABLE], [ilProviderDB::COMPONENT_TABLE])
            ->will($this->onConsecutiveCalls(false, false));

        $il_db
            ->expects($this->exactly(2))
            ->method("addPrimaryKey")
            ->withConsecutive(
                [ilProviderDB::PROVIDER_TABLE, ["id"]],
                [ilProviderDB::COMPONENT_TABLE, ["id", "component_type"]]);
   
        $db = new ilProviderDB($il_db);
        $db->createTables();
    }

    public function test_create() {
        $il_db = $this->il_db_mock();

        $owner = $this
            ->getMockBuilder(\ilObject::class)
            ->setMethods(["getId"])
            ->getMock();

        $owner_id = 42;
        $owner
            ->method("getId")
            ->willReturn($owner_id);

        $new_provider_id = 23;
        $object_type = "crs";
        $class_name = Test_UnboundProvider::class;
        $include_path = __DIR__."/UnboundProviderTest.php";

        $insert_provider =
            [ "id" => ["integer", $new_provider_id]
            , "owner" => ["integer", $owner_id]
            , "object_type" => ["string", $object_type]
            , "class_name" => ["string", $class_name]
            , "include_path" => ["string", $include_path]
            ];

        $insert_component_1 =
            [ "id" => ["integer", $new_provider_id]
            , "component_type" => ["string", AttachString::class]
            ];

        $insert_component_2 =
            [ "id" => ["integer", $new_provider_id]
            , "component_type" => ["string", AttachInt::class]
            ];

        $il_db
            ->expects($this->exactly(3))
            ->method("insert")
            ->withConsecutive(
                [ilProviderDB::PROVIDER_TABLE, $insert_provider],
                [ilProviderDB::COMPONENT_TABLE, $insert_component_1],
                [ilProviderDB::COMPONENT_TABLE, $insert_component_2]);

        $il_db
            ->expects($this->once())
            ->method("nextId")
            ->with(ilProviderDB::PROVIDER_TABLE)
            ->willReturn($new_provider_id);

        $db = new ilProviderDB($il_db);
        $unbound_provider = $db->create($owner, $object_type, $class_name, $include_path);

        $this->assertInstanceOf(Test_UnboundProvider::class, $unbound_provider);
    }

    public function test_delete() {
        $il_db = $this->il_db_mock();

        $owner = $this
            ->getMockBuilder(\ilObject::class)
            ->setMethods(["getId"])
            ->getMock();

        $unbound_provider_id = 23;

        $unbound_provider = $this
            ->getMockBuilder(UnboundProvider::class)
            ->setConstructorArgs([$unbound_provider_id, $owner, "type"])
            ->setMethods(["componentTypes", "buildComponentsOf", "id"])
            ->getMock();

        $il_db
            ->expects($this->atLeastOnce())
            ->method("quote")
            ->with($unbound_provider_id, "integer")
            ->willReturn("~$unbound_provider_id~");

        $il_db
            ->expects($this->exactly(2))
            ->method("manipulate")
            ->withConsecutive(
                ["DELETE FROM ".ilProviderDB::PROVIDER_TABLE." WHERE id = ~$unbound_provider_id~"],
                ["DELETE FROM ".ilProviderDB::COMPONENT_TABLE." WHERE id = ~$unbound_provider_id~"]);

        $db = new ilProviderDB($il_db);
        $db->delete($unbound_provider);
    }

    public function test_unboundProvidersOf() {
        $il_db = $this->il_db_mock();

        $owner = $this
            ->getMockBuilder(\ilObject::class)
            ->setMethods(["getId"])
            ->getMock();
        $owner_id = 42;
        $owner
            ->expects($this->atLeastOnce())
            ->method("getId")
            ->willReturn(42);

        $il_db
            ->expects($this->atLeastOnce())
            ->method("quote")
            ->with($owner_id, "integer")
            ->willReturn("~$owner_id~");

        $result = "RESULT";
        $il_db
            ->expects($this->once())
            ->method("query")
            ->with("SELECT id, object_type, class_name, include_path FROM ".ilProviderDB::PROVIDER_TABLE." WHERE owner = ~$owner_id~")
            ->willReturn($result);

        $object_type = "type";
        $class_name = "Test_UnboundProvider";
        $include_path = __DIR__."/UnboundProviderTest.php";

        $il_db
            ->expects($this->exactly(3))
            ->method("fetchAssoc")
            ->with("RESULT")
            ->will($this->onConsecutiveCalls(
                ["id" => 1, "object_type" => $object_type, "class_name" => $class_name, "include_path" => $include_path],
                ["id" => 2, "object_type" => $object_type, "class_name" => $class_name, "include_path" => $include_path],
                null));

        $db = new ilProviderDB($il_db);
        $providers = $db->unboundProvidersOf($owner);

        $this->assertCount(2, $providers);

        foreach ($providers as $provider) {
            $this->assertInstanceOf(Test_UnboundProvider::class, $provider);
            $this->assertEquals($object_type, $provider->objectType());
            $this->assertEquals($owner, $provider->owner());
        }

        list($provider1, $provider2) = $providers;
        $this->assertEquals(1, $provider1->id());
        $this->assertEquals(2, $provider2->id());
    }

    public function test_load() {
        $il_db = $this->il_db_mock();

        $provider_id = 23;
        $owner_id = 42;

        $il_db
            ->expects($this->atLeastOnce())
            ->method("quote")
            ->with($provider_id, "integer")
            ->willReturn("~$provider_id~");

        $result = "RESULT";
        $il_db
            ->expects($this->once())
            ->method("query")
            ->with("SELECT owner, object_type, class_name, include_path FROM ".ilProviderDB::PROVIDER_TABLE." WHERE id = ~$provider_id~")
            ->willReturn($result);

        $object_type = "type";
        $class_name = "Test_UnboundProvider";
        $include_path = __DIR__."/UnboundProviderTest.php";

        $il_db
            ->expects($this->once())
            ->method("fetchAssoc")
            ->with("RESULT")
            ->willReturn(
                ["owner" => $owner_id, "object_type" => $object_type, "class_name" => $class_name, "include_path" => $include_path]
                );

        $db = new Test_ilProviderDB($il_db);
        $owner = $this
            ->getMockBuilder(\ilObject::class)
            ->getMock();
        $db->object_obj[$owner_id] = $owner;
        $provider = $db->load($provider_id);

        $this->assertInstanceOf(Test_UnboundProvider::class, $provider);
        $this->assertEquals($object_type, $provider->objectType());
        $this->assertEquals($owner, $provider->owner());
        $this->assertEquals($provider_id, $provider->id());
    }

}
