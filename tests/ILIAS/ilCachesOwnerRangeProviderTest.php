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
use CaT\Ente\ILIAS\ilCachesOwnerRangeProviderDB;
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

class Test_ilCachesOwnerRangeProviderDB extends ilCachesOwnerRangeProviderDB {
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

class ILIAS_ilCachesOwnerRangeProviderDBTest extends PHPUnit_Framework_TestCase {
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
            ->expects($this->once())
            ->method("get")
            ->with("0")
            ->willReturn(null);
        $cache
            ->expects($this->once())
            ->method("set")
            ->with("0",
				[
					$sub_tree_id1 => [ $object_type => [
						"shared" => [],
						"separated" => 
							[
								[
									"id" => 1,
									"owner" => $sub_tree_id1,
									"class_name" => $class_name,
									"include_path" => $include_path,
									"object_type" => $object_type,
									"which" => "separated"
								]
							]
					]],
					$sub_tree_id2 => [ $object_type => [
						"shared" => [],
						"separated" => 
							[
								[
									"id" => 2,
									"owner" => $sub_tree_id2,
									"class_name" => $class_name,
									"include_path" => $include_path,
									"object_type" => $object_type,
									"which" => "separated"
								]
							]
					]],
                ]
            );

        $il_db
            ->expects($this->exactly(2))
            ->method("quote")
            ->withConsecutive([0],[1000])
            ->willReturn("~RANGE~");

        $il_db
            ->expects($this->once())
            ->method("query")
            ->with
                ("SELECT id, owner, class_name, include_path, object_type, shared FROM ".ilProviderDB::PROVIDER_TABLE." WHERE owner >= ~RANGE~ AND owner < ~RANGE~")
            ->willReturn("R1");

        $il_db
            ->expects($this->exactly(3))
            ->method("fetchAssoc")
            ->withConsecutive(["R1"],["R1"],["R1"])
            ->will($this->onConsecutiveCalls(
                ["id" => 1, "owner" => $sub_tree_ids[0], "class_name" => $class_name, "include_path" => $include_path, "object_type" => $object_type, "shared" => 0],
                ["id" => 2, "owner" => $sub_tree_ids[1], "class_name" => $class_name, "include_path" => $include_path, "object_type" => $object_type, "shared" => 0],
                null));

        $db = new Test_ilCachesOwnerRangeProviderDB($il_db, $il_tree, $il_cache, $cache);

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
            ->expects($this->once())
            ->method("get")
            ->with("0")
            ->willReturn(
				[
					$object_ref_id => [ $object_type => [ "separated" => [], "shared" => []]],
					$sub_tree_id1 => [ $object_type => [
						"separated" => 
							[
								[
									"id" => 1,
									"owner" => $sub_tree_id1,
									"class_name" => $class_name,
									"include_path" => $include_path
								]
							],
						"shared" => []
					]],
					$sub_tree_id2 => [ $object_type => [
						"separated" => 
							[
								[
									"id" => 2,
									"owner" => $sub_tree_id2,
									"class_name" => $class_name,
									"include_path" => $include_path
								]
							],
						"shared" => []
					]],
                ]
            );

        $cache
            ->expects($this->never())
            ->method("set");

        $il_db
            ->expects($this->never())
            ->method("in");

        $il_db
            ->expects($this->never())
            ->method("quote");

        $il_db
            ->expects($this->never())
            ->method("query");

        $il_db
            ->expects($this->never())
            ->method("fetchAssoc");

        $db = new Test_ilCachesOwnerRangeProviderDB($il_db, $il_tree, $il_cache, $cache);

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
