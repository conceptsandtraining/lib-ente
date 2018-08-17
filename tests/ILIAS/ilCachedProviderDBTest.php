<?php
/******************************************************************************
 * An entity component framework for PHP.
 *
 * Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de>
 *
 * This software is licensed under GPLv3. You should have received a copy of
 * the license along with the code.
 */

use CaT\Ente\ILIAS\SeparatedUnboundProvider;
use CaT\Ente\ILIAS\UnboundProvider;
use CaT\Ente\Provider;
use CaT\Ente\ILIAS\ilCachedProviderDB;
use CaT\Ente\ILIAS\ilProviderDB;
use CaT\Ente\ILIAS\Cache;
use CaT\Ente\Simple\AttachString;
use CaT\Ente\Simple\AttachInt;

if (!class_exists("ilObject")) {
    require_once(__DIR__."/ilObject.php");
}

if (!interface_exists("ilDBInterface")) {
    require_once(__DIR__."/ilDBInterface.php");
}

if (!interface_exists("ilTree")) {
    require_once(__DIR__."/ilTree.php");
}

if (!interface_exists("ilObjectDataCache")) {
    require_once(__DIR__."/ilObjectDataCache.php");
}

class Test_ilCachedProviderDB extends ilCachedProviderDB {
    public $object_ref = [];
	public $throws = false;
    protected function buildObjectByRefId($ref_id) {
		if ($this->throws) {
			throw new \InvalidArgumentException();
		}
        assert(isset($this->object_ref[$ref_id]));
        return $this->object_ref[$ref_id];
    }
    public $object_obj = [];
    protected function buildObjectByObjId($obj_id) {
		if ($this->throws) {
			throw new \InvalidArgumentException();
		}
        assert(isset($this->object_obj[$obj_id]));
        return $this->object_obj[$obj_id];
    }
    public $reference_ids = [];
    protected function getAllReferenceIdsFor($obj_id) {
        assert(isset($this->reference_ids[$obj_id]));
        return $this->reference_ids[$obj_id];
    }
}

class ILIAS_ilCachedProviderDBTest extends PHPUnit_Framework_TestCase {
    protected function il_db_mock() {
        return $this->createMock(\ilDBInterface::class);
    }

    public function il_tree_mock() {
        return $this
            ->getMockBuilder(\ilTree::class)
            ->setMethods(["getSubTreeIds", "getNodePath"])
            ->getMock();
    }

    public function il_object_data_cache_mock() {
        return $this
            ->getMockBuilder(\ilObjectDataCache::class)
            ->setMethods(["preloadReferenceCache", "lookupObjId"])
            ->getMock();
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
        $class_name = Test_SeparatedUnboundProvider::class;
        $include_path = __DIR__."/SeparatedUnboundProviderTest.php";

        $insert_provider =
            [ "id" => ["integer", $new_provider_id]
            , "owner" => ["integer", $owner_id]
            , "object_type" => ["string", $object_type]
            , "class_name" => ["string", $class_name]
            , "include_path" => ["string", $include_path]
			, "shared" => ["integer", 0]
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

        $cache = $this->createMock(Cache::class);

        $cache
            ->expects($this->once())
            ->method("delete")
            ->with("$owner_id-$object_type-separated");

        $db = new ilCachedProviderDB($il_db, $this->il_tree_mock(), $this->il_object_data_cache_mock(), $cache);
        $unbound_provider = $db->createSeparatedUnboundProvider($owner, $object_type, $class_name, $include_path);

        $this->assertInstanceOf(Test_SeparatedUnboundProvider::class, $unbound_provider);
    }

    public function test_delete() {
        $il_db = $this->il_db_mock();

        $owner = $this
            ->getMockBuilder(\ilObject::class)
            ->setMethods(["getId"])
            ->getMock();

        $owner_id = 42;
        $owner
            ->method("getId")
            ->willReturn($owner_id);

        $unbound_provider = $this->createMock(UnboundProvider::class);

        $unbound_provider_id = 23;
        $unbound_provider
            ->expects($this->once())
            ->method("idFor")
            ->with($owner)
            ->willReturn($unbound_provider_id);
        $object_type = "otype";
        $unbound_provider
            ->method("objectType")
            ->willReturn($object_type);

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

        $cache = $this->createMock(Cache::class);

        $cache
            ->expects($this->exactly(2))
            ->method("delete")
            ->withConsecutive(
                ["$owner_id-$object_type-separated"],
                ["$owner_id-$object_type-shared"]
            );

        $db = new ilCachedProviderDB($il_db, $this->il_tree_mock(), $this->il_object_data_cache_mock(), $cache);
        $db->delete($unbound_provider, $owner);
    }

    public function test_providersFor_no_cache() {
        $il_db = $this->il_db_mock();
        $il_tree = $this->il_tree_mock();
        $il_cache = $this->il_object_data_cache_mock();

        $object_ref_id = 42;
        $object_type = "crs";
        $object = $this
            ->getMockBuilder(\ilObject::class)
            ->setMethods(["getRefId", "getType"])
            ->getMock();

        $object
            ->expects($this->atLeastOnce())
            ->method("getRefId")
            ->willReturn($object_ref_id);

        $object
            ->expects($this->atLeastOnce())
            ->method("getType")
            ->willReturn($object_type);

        $sub_tree_id1 = 3;
        $sub_tree_id2 = 14;
        $sub_tree_ids = ["$sub_tree_id1", "$sub_tree_id2"];
        $il_tree
            ->expects($this->once())
            ->method("getSubTreeIds")
            ->with($object_ref_id)
            ->willReturn($sub_tree_ids);

		$tree_ids = array_merge([$object_ref_id], $sub_tree_ids);
        $il_cache
            ->expects($this->once())
            ->method("preloadReferenceCache")
            ->with($tree_ids);

        $il_cache
            ->expects($this->exactly(3))
            ->method("lookupObjId")
            ->withConsecutive([$tree_ids[0]],[$tree_ids[1]], [$tree_ids[2]])
            ->will($this->onConsecutiveCalls($tree_ids[0], $tree_ids[1], $tree_ids[2]));


        $class_name = Test_SeparatedUnboundProvider::class;
        $include_path = __DIR__."/SeparatedUnboundProviderTest.php";

        $cache = $this->createMock(Cache::class);
        $cache
            ->expects($this->exactly(6))
            ->method("get")
            ->withConsecutive(
                ["$object_ref_id-$object_type-separated"],
                ["$sub_tree_id1-$object_type-separated"],
                ["$sub_tree_id2-$object_type-separated"],
                ["$object_ref_id-$object_type-shared"],
                ["$sub_tree_id1-$object_type-shared"],
                ["$sub_tree_id2-$object_type-shared"]
            )
            ->willReturn(null);
        $cache
            ->expects($this->exactly(6))
            ->method("set")
            ->withConsecutive(
                ["$object_ref_id-$object_type-separated", []],
                ["$sub_tree_id1-$object_type-separated",
                    [
                        [
                            "id" => 1,
                            "owner" => $sub_tree_id1,
                            "class_name" => $class_name,
                            "include_path" => $include_path
                        ]
                    ]
                ],
                ["$sub_tree_id2-$object_type-separated",
                    [
                        [
                            "id" => 2,
                            "owner" => $sub_tree_id2,
                            "class_name" => $class_name,
                            "include_path" => $include_path
                        ]
                    ]
                ],
                ["$object_ref_id-$object_type-shared", []],
                ["$sub_tree_id1-$object_type-shared", []],
                ["$sub_tree_id2-$object_type-shared", []]
            );

        $il_db
            ->expects($this->exactly(6))
            ->method("in")
            ->withConsecutive(
                ["owner", [$object_ref_id], false, "integer"],
                ["owner", [$sub_tree_id1], false, "integer"],
                ["owner", [$sub_tree_id2], false, "integer"],
                ["owner", [$object_ref_id], false, "integer"],
                ["owner", [$sub_tree_id1], false, "integer"],
                ["owner", [$sub_tree_id2], false, "integer"]
            )
            ->willReturnOnConsecutiveCalls("~IN1~", "~IN2~", "~IN3~", "~IN1~", "~IN2~", "~IN3~");
        $il_db
            ->expects($this->exactly(6))
            ->method("quote")
            ->with($object_type)
            ->willReturn("~TYPE~");
        $il_db
            ->expects($this->exactly(6))
            ->method("query")
            ->withConsecutive
                ( ["SELECT id, owner, class_name, include_path FROM ".ilProviderDB::PROVIDER_TABLE." WHERE shared = 0 AND ~IN1~ AND object_type = ~TYPE~"]
                , ["SELECT id, owner, class_name, include_path FROM ".ilProviderDB::PROVIDER_TABLE." WHERE shared = 0 AND ~IN2~ AND object_type = ~TYPE~"]
                , ["SELECT id, owner, class_name, include_path FROM ".ilProviderDB::PROVIDER_TABLE." WHERE shared = 0 AND ~IN3~ AND object_type = ~TYPE~"]
                , ["SELECT GROUP_CONCAT(id SEPARATOR \",\") ids, GROUP_CONCAT(owner SEPARATOR \",\") owners, class_name, include_path FROM ".ilProviderDB::PROVIDER_TABLE." WHERE shared = 1 AND ~IN1~ AND object_type = ~TYPE~ GROUP BY class_name, include_path"]
                , ["SELECT GROUP_CONCAT(id SEPARATOR \",\") ids, GROUP_CONCAT(owner SEPARATOR \",\") owners, class_name, include_path FROM ".ilProviderDB::PROVIDER_TABLE." WHERE shared = 1 AND ~IN2~ AND object_type = ~TYPE~ GROUP BY class_name, include_path"]
                , ["SELECT GROUP_CONCAT(id SEPARATOR \",\") ids, GROUP_CONCAT(owner SEPARATOR \",\") owners, class_name, include_path FROM ".ilProviderDB::PROVIDER_TABLE." WHERE shared = 1 AND ~IN3~ AND object_type = ~TYPE~ GROUP BY class_name, include_path"]
                )
            ->willReturnOnConsecutiveCalls("R1", "R2", "R3", "R4", "R5", "R6");

        $il_db
            ->expects($this->exactly(8))
            ->method("fetchAssoc")
            ->withConsecutive(["R1"],["R2"],["R2"],["R3"],["R3"],["R4"],["R5"],["R6"])
            ->will($this->onConsecutiveCalls(
                null,
                ["id" => 1, "owner" => $sub_tree_ids[0], "class_name" => $class_name, "include_path" => $include_path],
                null,
                ["id" => 2, "owner" => $sub_tree_ids[1], "class_name" => $class_name, "include_path" => $include_path],
                null,
                null,
                null,
                null));

        $db = new Test_ilCachedProviderDB($il_db, $il_tree, $il_cache, $cache);

        $owner_1 = $this
            ->getMockBuilder(\ilObject::class)
            ->getMock();
        $db->object_ref[$sub_tree_ids[0]] = $owner_1;

        $owner_2 = $this
            ->getMockBuilder(\ilObject::class)
            ->getMock();
        $db->object_ref[$sub_tree_ids[1]] = $owner_2;

        $providers = $db->providersFor($object);
        $this->assertCount(2, $providers);

        foreach ($providers as $provider) {
            $this->assertInstanceOf(Provider::class, $provider);
            $this->assertEquals($object, $provider->object());
            $this->assertEquals($object_type, $provider->unboundProvider()->objectType());
        }

        list($provider1, $provider2) = $providers;
        $this->assertEquals(1, $provider1->unboundProvider()->idFor($owner_1));
        $this->assertEquals([$owner_1], $provider1->owners());

        $this->assertEquals(2, $provider2->unboundProvider()->idFor($owner_2));
        $this->assertEquals([$owner_2], $provider2->owners());
    }

    public function test_providersFor_with_cache() {
        $il_db = $this->il_db_mock();
        $il_tree = $this->il_tree_mock();
        $il_cache = $this->il_object_data_cache_mock();

        $object_ref_id = 42;
        $object_type = "crs";
        $object = $this
            ->getMockBuilder(\ilObject::class)
            ->setMethods(["getRefId", "getType"])
            ->getMock();

        $object
            ->expects($this->atLeastOnce())
            ->method("getRefId")
            ->willReturn($object_ref_id);

        $object
            ->expects($this->atLeastOnce())
            ->method("getType")
            ->willReturn($object_type);

        $sub_tree_id1 = 3;
        $sub_tree_id2 = 14;
        $sub_tree_ids = ["$sub_tree_id1", "$sub_tree_id2"];
        $il_tree
            ->expects($this->once())
            ->method("getSubTreeIds")
            ->with($object_ref_id)
            ->willReturn($sub_tree_ids);

		$tree_ids = array_merge([$object_ref_id], $sub_tree_ids);
        $il_cache
            ->expects($this->once())
            ->method("preloadReferenceCache")
            ->with($tree_ids);

        $il_cache
            ->expects($this->exactly(3))
            ->method("lookupObjId")
            ->withConsecutive([$tree_ids[0]],[$tree_ids[1]], [$tree_ids[2]])
            ->will($this->onConsecutiveCalls($tree_ids[0], $tree_ids[1], $tree_ids[2]));


        $class_name = Test_SeparatedUnboundProvider::class;
        $include_path = __DIR__."/SeparatedUnboundProviderTest.php";

        $cache = $this->createMock(Cache::class);

        $cache
            ->expects($this->exactly(6))
            ->method("get")
            ->withConsecutive(
                ["$object_ref_id-$object_type-separated"],
                ["$sub_tree_id1-$object_type-separated"],
                ["$sub_tree_id2-$object_type-separated"],
                ["$object_ref_id-$object_type-shared"],
                ["$sub_tree_id1-$object_type-shared"],
                ["$sub_tree_id2-$object_type-shared"]
            )
            ->willReturnOnConsecutiveCalls(
                null,
                [[
                    "id" => 1,
                    "owner" => $sub_tree_id1,
                    "class_name" => $class_name,
                    "include_path" => $include_path
                ]],
                null,
                null,
                [],
                null
            );

        $cache
            ->expects($this->exactly(4))
            ->method("set")
            ->withConsecutive(
                ["$object_ref_id-$object_type-separated", []],
                ["$sub_tree_id2-$object_type-separated",
                    [
                        [
                            "id" => 2,
                            "owner" => $sub_tree_id2,
                            "class_name" => $class_name,
                            "include_path" => $include_path
                        ]
                    ]
                ],
                ["$object_ref_id-$object_type-shared", []],
                ["$sub_tree_id2-$object_type-shared", []]
            );

        $il_db
            ->expects($this->exactly(4))
            ->method("in")
            ->withConsecutive(
                ["owner", [$object_ref_id], false, "integer"],
                ["owner", [$sub_tree_id2], false, "integer"],
                ["owner", [$object_ref_id], false, "integer"],
                ["owner", [$sub_tree_id2], false, "integer"]
            )
            ->willReturnOnConsecutiveCalls("~IN1~", "~IN3~", "~IN1~", "~IN3~");

        $il_db
            ->expects($this->exactly(4))
            ->method("quote")
            ->with($object_type)
            ->willReturn("~TYPE~");

        $il_db
            ->expects($this->exactly(4))
            ->method("query")
            ->withConsecutive
                ( ["SELECT id, owner, class_name, include_path FROM ".ilProviderDB::PROVIDER_TABLE." WHERE shared = 0 AND ~IN1~ AND object_type = ~TYPE~"]
                , ["SELECT id, owner, class_name, include_path FROM ".ilProviderDB::PROVIDER_TABLE." WHERE shared = 0 AND ~IN3~ AND object_type = ~TYPE~"]
                , ["SELECT GROUP_CONCAT(id SEPARATOR \",\") ids, GROUP_CONCAT(owner SEPARATOR \",\") owners, class_name, include_path FROM ".ilProviderDB::PROVIDER_TABLE." WHERE shared = 1 AND ~IN1~ AND object_type = ~TYPE~ GROUP BY class_name, include_path"]
                , ["SELECT GROUP_CONCAT(id SEPARATOR \",\") ids, GROUP_CONCAT(owner SEPARATOR \",\") owners, class_name, include_path FROM ".ilProviderDB::PROVIDER_TABLE." WHERE shared = 1 AND ~IN3~ AND object_type = ~TYPE~ GROUP BY class_name, include_path"]
                )
            ->willReturnOnConsecutiveCalls("R1", "R2", "R5", "R6");

        $il_db
            ->expects($this->exactly(5))
            ->method("fetchAssoc")
            ->withConsecutive(["R1"],["R2"],["R2"],["R5"],["R6"])
            ->will($this->onConsecutiveCalls(
                null,
                ["id" => 2, "owner" => $sub_tree_ids[1], "class_name" => $class_name, "include_path" => $include_path],
                null,
                null,
                null));

        $db = new Test_ilCachedProviderDB($il_db, $il_tree, $il_cache, $cache);

        $owner_1 = $this
            ->getMockBuilder(\ilObject::class)
            ->getMock();
        $db->object_ref[$sub_tree_ids[0]] = $owner_1;

        $owner_2 = $this
            ->getMockBuilder(\ilObject::class)
            ->getMock();
        $db->object_ref[$sub_tree_ids[1]] = $owner_2;

        $providers = $db->providersFor($object);
        $this->assertCount(2, $providers);

        foreach ($providers as $provider) {
            $this->assertInstanceOf(Provider::class, $provider);
            $this->assertEquals($object, $provider->object());
            $this->assertEquals($object_type, $provider->unboundProvider()->objectType());
        }

        list($provider1, $provider2) = $providers;
        $this->assertEquals(1, $provider1->unboundProvider()->idFor($owner_1));
        $this->assertEquals([$owner_1], $provider1->owners());

        $this->assertEquals(2, $provider2->unboundProvider()->idFor($owner_2));
        $this->assertEquals([$owner_2], $provider2->owners());
    }
}
