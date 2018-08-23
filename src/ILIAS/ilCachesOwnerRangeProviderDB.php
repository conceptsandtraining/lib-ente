<?php
/******************************************************************************
 * An entity component framework for PHP.
 *
 * Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de>
 *
 * This software is licensed under GPLv3. You should have received a copy of
 * the license along with the code.
 */

namespace CaT\Ente\ILIAS;

/**
 * A database that stores ILIAS providers and uses a cache.
 */
class ilCachesOwnerRangeProviderDB extends ilProviderDB {
    /**
     * @var Cache
     */
    protected $cache;

    /**
     * @var	int
     */
    protected $shard_size;

    /**
     * @var array
     */
    protected $shards;

    /**
     * @var \ilDBInterface
     */
    private $ilDB;

    /**
     * @param   int $shard_size how many owners are stored in one cache entry?
     */
    public function __construct(\ilDBInterface $ilDB, \ilTree $tree, \ilObjectDataCache $obj_cache, Cache $cache, int $shard_size = 1000) {
        parent::__construct($ilDB, $tree, $obj_cache);
        $this->ilDB = $ilDB;
        $this->cache = $cache;
        $this->shard_size = $shard_size;
        $this->shards = [];
    }

    /**
     * @inheritdocs
     */
    public function createSeparatedUnboundProvider(\ilObject $owner, $object_type, $class_name, $include_path) {
        $res = parent::createSeparatedUnboundProvider($owner, $object_type, $class_name, $include_path);
        $this->refreshShardOf($owner->getId());
        return $res;
    }

    /**
     * @inheritdocs
     */
    public function createSharedUnboundProvider(\ilObject $owner, $object_type, $class_name, $include_path) {
        $res = parent::createSharedUnboundProvider($owner, $object_type, $class_name, $include_path);
        $this->refreshShardOf($owner->getId());
        return $res;
    }

    /**
     * @inheritdocs
     */
    public function delete(UnboundProvider $provider, \ilObject $owner) {
        parent::delete($provider, $owner);
        $this->refreshShardOf($owner->getId());
    }

    /**
     * Get the data of the separated unbound providers of the given nodes.
     *
     * @param   int[]       $node_ids
     * @param   string      $object_type
     * @param   string|null $component_type
     * @return  array
     */
    protected function getSeperatedUnboundProviderDataOf($node_ids, string $object_type, string $component_type = null) {
        if ($component_type !== null) {
            return parent::getSharedUnboundProviderDataOf($node_ids, $object_type, $component_type);
        }

        $ret = [];
        foreach($node_ids as $node_id) {
            $ret[] = $this->getDataOf($node_id, $object_type, "separated", $component_type);
        }
        return call_user_func_array("array_merge", $ret);
    }

    /**
     * Get the data of the shared unbound providers of the given nodes.
     *
     * @param   int[]       $node_ids
     * @param   string      $object_type
     * @param   string|null $component_type
     * @return  array
     */
    protected function getSharedUnboundProviderDataOf($node_ids, string $object_type, string $component_type = null) {
        if ($component_type !== null) {
            return parent::getSharedUnboundProviderDataOf($node_ids, $object_type, $component_type);
        }

        $data = [];
        foreach($node_ids as $node_id) {
            $ds = $this->getDataOf($node_id, $object_type, "shared");
            foreach ($ds as $d) {
                $key = $d["class_name"]." ".$d["include_path"];
                if(!isset($data[$key])) {
                    $data[$key] = [
                        "owners" => [],
                        "ids" => [],
                        "class_name" => $d["class_name"],
                        "include_path" => $d["include_path"]
                    ];
                } 
                $data[$key]["owners"][] = $d["owner"];
                $data[$key]["ids"][] = $d["id"];
            }
        }
        return array_values($data);
    }

    /**
     * Refreshes a shard in the cache from the database.
     */
    protected function refreshShardOf(int $node_id) {
        $shard_id = $this->getShardIdOf($node_id);
        return $this->refreshShard($shard_id);
    } 

    /**
     * Refreshes a shard in the cache from the database.
     */
    protected function refreshShard(int $shard_id) {
        unset($this->shards[$shard_id]);
        $data = $this->loadShardDataFromDB($shard_id);
        // For some reason we get a null in maybeLoadShardDataFromCache if the array
        // is completely empty. This produces (wrong) cache misses. To prevent them
        // we set this key.
        $data["i am"] = "here";
        $this->shards[$shard_id] = $data;
        $this->cache->set("$shard_id", $data);
    } 

    /**
     * Get the data for a specific node.
     */
    protected function getDataOf(int $node_id, string $object_type, string $which) {
        $shard_id = $this->getShardIdOf($node_id);
        $this->maybeLoadShardDataFromCache($shard_id);
        if (!isset($this->shards[$shard_id][$node_id][$object_type])) {
            return [];
        }
        return $this->shards[$shard_id][$node_id][$object_type][$which];
    }

    /**
     * Get the id of the shard the node is in.
     */
    protected function getShardIdOf(int $node_id) {
        return (int)floor($node_id/$this->shard_size);
    }

    /**
     * Load shard data from cache if not already loaded.
     */
    protected function maybeLoadShardDataFromCache(int $shard_id) {
        if (isset($this->shards[$shard_id])) {
            return;
        }
        $data = $this->cache->get("$shard_id");
        if ($data !== null) {
            $this->shards[$shard_id] = $data;
            return;
        }
        $this->refreshShard($shard_id);
    }

    /**
     * Load the data for a shard from the db.
     */
    protected function loadShardDataFromDB($shard_id) {
        $data = [];
        $l = $shard_id * $this->shard_size;
        $r = ($shard_id + 1) * $this->shard_size;
        foreach ($this->getUnboundProviderDataOf($l, $r) as $d) {
            $owner = $d["owner"];
            $object_type = $d["object_type"];
            if (!isset($data[$owner])) {
                $data[$owner] = [];
            }
            if (!isset($data[$owner][$object_type])) {
                $data[$owner][$object_type] = [ "separated" => [], "shared" => []];
            }
            $data[$owner][$object_type][$d["which"]][] = $d;
        }
        return $data;
    }

    /**
     * Get the data of the seperated unbound providers of the given range of owners.
     *
     * @param   int         $left   first owner to be used
     * @param   int         $right  first owner not to be used
     * @return  \Iterator 
     */
    protected function getUnboundProviderDataOf(int $left, int $right) {
        assert($left < $right);
        assert($right - $left > 0);
        $query = $this->buildUnboundProviderQueryForObjects($left, $right);
        $res = $this->ilDB->query($query);
        while ($row = $this->ilDB->fetchAssoc($res)) {
            yield [
                "id" => (int)$row["id"],
                "owner" => (int)$row["owner"],
                "class_name" => $row["class_name"],
                "include_path" => $row["include_path"],
                "object_type" => $row["object_type"],
                "which" => $row["shared"] == 0 ? "separated" : "shared"
            ];
        }
    }

    /**
     * Get a query for all SeparatedUnboundProviders that are owned by the given nodes
     * providing for a given object type.
     *
     * @param   int         $left   first owner to be used
     * @param   int         $right  first owner not to be used
     * @return  string
     */
    protected function buildUnboundProviderQueryForObjects(int $left, int $right) {
        assert($left < $right);
        assert($right - $left > 0);
        return
            "SELECT id, owner, class_name, include_path, object_type, shared ".
            "FROM ".ilProviderDB::PROVIDER_TABLE." ".
            "WHERE owner >= ".$this->ilDB->quote($left, "integer").
            " AND owner < ".$this->ilDB->quote($right, "integer");
    }
}
