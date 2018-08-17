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
class ilCachedProviderDB extends ilProviderDB {
    /**
     * @var Cache
     */
    protected $cache;

    public function __construct(\ilDBInterface $ilDB, \ilTree $tree, \ilObjectDataCache $obj_cache, Cache $cache) {
        parent::__construct($ilDB, $tree, $obj_cache);
        $this->cache = $cache;
    }

    /**
     * @inheritdocs
     */
    public function createSeparatedUnboundProvider(\ilObject $owner, $object_type, $class_name, $include_path) {
        $this->cache->delete($owner->getId()."-$object_type-separated");
        return parent::createSeparatedUnboundProvider($owner, $object_type, $class_name, $include_path);
    }

    /**
     * @inheritdocs
     */
    public function createSharedUnboundProvider(\ilObject $owner, $object_type, $class_name, $include_path) {
        $this->cache->delete($owner->getId()."-$object_type-shared");
        return parent::createSharedUnboundProvider($owner, $object_type, $class_name, $include_path);
    }

    /**
     * @inheritdocs
     */
    public function delete(UnboundProvider $provider, \ilObject $owner) {
        $object_type = $provider->objectType();
        $this->cache->delete($owner->getId()."-$object_type-separated");
        $this->cache->delete($owner->getId()."-$object_type-shared");
        parent::delete($provider, $owner);
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
            return parent::getSeperatedUnboundProviderDataOf($node_ids, $object_type, $component_type);
        }

        $ret = [];
        foreach($node_ids as $node_id) {
            $key = "$node_id-$object_type-separated";
            $data = $this->cache->get($key);
            if ($data === null) {
                $data = iterator_to_array(parent::getSeperatedUnboundProviderDataOf([$node_id], $object_type));
                $this->cache->set($key, $data);
            }
            $ret[] = $data;
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

        $ret = [];
        foreach($node_ids as $node_id) {
            $key = "$node_id-$object_type-shared";
            $data = $this->cache->get($key);
            if ($data === null) {
                $data = iterator_to_array(parent::getSharedUnboundProviderDataOf([$node_id], $object_type));
                $this->cache->set($key, $data);
            }
            $ret[] = $data;
        }
        return call_user_func_array("array_merge", $ret);
    }
}
