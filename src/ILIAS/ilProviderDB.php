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
 * A database that stores ILIAS providers.
 */
class ilProviderDB implements ProviderDB {
    const PROVIDER_TABLE = "ente_prvs";
    const COMPONENT_TABLE = "ente_prv_cmps";

    const CLASS_NAME_LENGTH = 128;
    const PATH_LENGTH = 1024;

    /**
     * @var \ilDBInterface
     */
    private $ilDB;

    /**
     * @var \ilTree
     */
    private $ilTree;

    /**
     * @var \ilObjectDataCache
     */
    private $ilObjectDataCache;

    public function __construct(\ilDBInterface $ilDB, \ilTree $tree, \ilObjectDataCache $cache) {
        $this->ilDB = $ilDB;
        $this->ilTree = $tree;
        $this->ilObjectDataCache = $cache;
    }

    /**
     * @inheritdocs
     */
    public function createSeperatedUnboundProvider(\ilObject $owner, $object_type, $class_name, $include_path) {
        assert('is_string($object_type)');
        assert('is_string($class_name)');
        assert('is_string($include_path)');
        if (strlen($object_type) > 4) {
            throw new \LogicException("Expected object type '$object_type' to have four or less chars.");
        }
        if (strlen($class_name) > ilProviderDB::CLASS_NAME_LENGTH) {
            throw new \LogicException(
                        "Expected class name '$class_name' to have at most "
                        .ilProviderDB::CLASS_NAME_LENGTH." chars.");
        }
        if (strlen($include_path) > ilProviderDB::PATH_LENGTH) {
            throw new \LogicException(
                        "Expected include path '$include_path' to have at most "
                        .ilProviderDB::PATH_LENGTH." chars.");
        }

        // TODO: check if class exist first
        $id = (int)$this->ilDB->nextId(ilProviderDB::PROVIDER_TABLE);
        $this->ilDB->insert(ilProviderDB::PROVIDER_TABLE,
            [ "id" => ["integer", $id]
            , "owner" => ["integer", $owner->getId()]
            , "object_type" => ["string", $object_type]
            , "class_name" => ["string", $class_name]
            , "include_path" => ["string", $include_path]
            ]);

        $unbound_provider = $this->buildUnboundProvider($id, $owner, $class_name, $class_name, $include_path);

        foreach ($unbound_provider->componentTypes() as $component_type) {
            if (strlen($component_type) > ilProviderDB::CLASS_NAME_LENGTH) {
                throw new \LogicException(
                            "Expected component type '$class_name' to have at most "
                            .ilProviderDB::CLASS_NAME_LENGTH." chars.");
            }
            $this->ilDB->insert(ilProviderDB::COMPONENT_TABLE,
                [ "id" => ["integer", $id]
                , "component_type" => ["string", $component_type]
                ]);
        }

        return $unbound_provider;
    }

    /**
     * @inheritdocs
     */
    public function load($id) {
        assert('is_int($id)');

        $query =
            "SELECT owner, object_type, class_name, include_path ".
            "FROM ".ilProviderDB::PROVIDER_TABLE." ".
            "WHERE id = ".$this->ilDB->quote($id, "integer");
        $res = $this->ilDB->query($query);

        if($row = $this->ilDB->fetchAssoc($res)) {
            $owner = $this->buildObjectByObjId($row["owner"]);
            return $this->buildUnboundProvider($id, $owner, $row["object_type"], $row["class_name"], $row["include_path"]);
        }
        else {
            throw new \InvalidArgumentException("Unbound provider with id '$id' does not exist.");
        }
    }

    /**
     * @inheritdocs
     */
    public function delete(UnboundProvider $provider) {
        $id = $provider->id();

        $this->ilDB->manipulate("DELETE FROM ".ilProviderDB::PROVIDER_TABLE." WHERE id = ".$this->ilDB->quote($id, "integer"));
        $this->ilDB->manipulate("DELETE FROM ".ilProviderDB::COMPONENT_TABLE." WHERE id = ".$this->ilDB->quote($id, "integer"));
    }

    /**
     * @inheritdocs
     */
    public function update(UnboundProvider $provider) {
        $id = $provider->id();
        $this->ilDB->manipulate("DELETE FROM ".ilProviderDB::COMPONENT_TABLE." WHERE id = ".$this->ilDB->quote($id, "integer"));

        foreach ($provider->componentTypes() as $component_type) {
            if (strlen($component_type) > ilProviderDB::CLASS_NAME_LENGTH) {
                throw new \LogicException(
                            "Expected component type '$class_name' to have at most "
                            .ilProviderDB::CLASS_NAME_LENGTH." chars.");
            }
            $this->ilDB->insert(ilProviderDB::COMPONENT_TABLE,
                [ "id" => ["integer", $id]
                , "component_type" => ["string", $component_type]
                ]);
        }
    }

    /**
     * @inheritdocs
     */
    public function unboundProvidersOf(\ilObject $owner) {
        $ret = [];

        $query =
            "SELECT id, object_type, class_name, include_path ".
            "FROM ".ilProviderDB::PROVIDER_TABLE." ".
            "WHERE owner = ".$this->ilDB->quote($owner->getId(), "integer");
        $res = $this->ilDB->query($query);

        while($row = $this->ilDB->fetchAssoc($res)) {
            $ret[] = $this->buildUnboundProvider((int)$row["id"], $owner, $row["object_type"], $row["class_name"], $row["include_path"]);
        }

        return $ret;
    }

    /**
     * @inheritdocs
     */
    public function providersFor(\ilObject $object, $component_type = null) {
        assert('is_null($component_type) || is_string($component_type)');

        list($nodes_ids, $nodes_id_mapping) = $this->getSubtreeObjectIdsAndRefIdMapping((int)$object->getRefId());
        $object_type = $object->getType();

        $query = $this->buildSeperatedUnboundProviderQueryForObjects($nodes_ids, $object_type, $component_type);
        $ret = [];
        $res = $this->ilDB->query($query);
        while ($row = $this->ilDB->fetchAssoc($res)) {
            $obj_id = $row["owner"];
            $ref_id = $nodes_id_mapping[$obj_id];
            $owner = $this->buildObjectByRefId($ref_id);
            $ret[] = new Provider
                ( $object
                , $this->buildUnboundProvider
                    ( (int)$row["id"]
                    , $owner
                    , $object_type
                    , $row["class_name"]
                    , $row["include_path"]
                    )
                );
        }

        return $ret;
    }

    /**
     * Get the object ids of the subtree starting at and including $ref_id with
     * a mapping from $obj_id to $ref_id.
     *
     * @param   int $ref_id
     * @return  array   [int[], array<int,int>]
     */
    protected function getSubtreeObjectIdsAndRefIdMapping($ref_id) {
        $sub_nodes_refs = $this->ilTree->getSubTreeIds($ref_id);
		$all_nodes_refs = array_merge([$ref_id], $sub_nodes_refs);
        $this->ilObjectDataCache->preloadReferenceCache($all_nodes_refs);

        $nodes_id_mapping = [];
        $nodes_ids = [];
        foreach ($all_nodes_refs as $ref_id) {
            $id = $this->ilObjectDataCache->lookupObjId($ref_id);
            $nodes_id_mapping[$id] = $ref_id;
            $nodes_ids[] = $id;
        }
        return [$nodes_ids, $nodes_id_mapping];
    }

    /**
     * Get a query for all SharedUnboundProviders that are owned by the given nodes
     * providing for a given object type.
     *
     * @param   int[]       $node_ids
     * @param   string      $object_type
     * @param   string|null $component_type
     * @return  string
     */
    protected function buildSeperatedUnboundProviderQueryForObjects(array $node_ids, $object_type, $component_type) {
        assert('is_string($object_type)');
        assert('is_null($component_type) || is_string($component_type)');
        if ($component_type === null) {
            return
                "SELECT id, owner, class_name, include_path ".
                "FROM ".ilProviderDB::PROVIDER_TABLE." ".
                "WHERE ".$this->ilDB->in("owner", $node_ids, false, "integer").
                " AND object_type = ".$this->ilDB->quote($object_type, "string");
        }
        else {
            return
                "SELECT prv.id, prv.owner, prv.class_name, prv.include_path ".
                "FROM ".ilProviderDB::PROVIDER_TABLE." prv ".
                "JOIN ".ilProviderDB::COMPONENT_TABLE." cmp ".
                "ON prv.id = cmp.id ".
                "WHERE ".$this->ilDB->in("owner", $node_ids, false, "integer").
                " AND object_type = ".$this->ilDB->quote($object_type, "string").
                " AND component_type = ".$this->ilDB->quote($component_type, "string");
        }
    }

    /**
     * Create the tables for the providers in the ILIAS db.
     *
     * @return  null
     */
    public function createTables() {
        if (!$this->ilDB->tableExists(ilProviderDB::PROVIDER_TABLE)) {
            $this->ilDB->createTable(ilProviderDB::PROVIDER_TABLE,
                [ "id" => ["type" => "integer", "length" => 4, "notnull" => true]
                , "owner" => ["type" => "integer", "length" => 4, "notnull" => true]
                , "object_type" => ["type" => "text", "length" => 4, "notnull" => true]
                , "class_name" => ["type" => "text", "length" => ilProviderDB::CLASS_NAME_LENGTH, "notnull" => true]
                , "include_path" => ["type" => "text", "length" => ilProviderDB::PATH_LENGTH, "notnull" => true]
                ]);
            $this->ilDB->addPrimaryKey(ilProviderDB::PROVIDER_TABLE, ["id"]);
            $this->ilDB->createSequence(ilProviderDB::PROVIDER_TABLE);
        }
        if (!$this->ilDB->tableExists(ilProviderDB::COMPONENT_TABLE)) {
            $this->ilDB->createTable(ilProviderDB::COMPONENT_TABLE,
                [ "id" => ["type" => "integer", "length" => 4, "notnull" => true]
                , "component_type" => ["type" => "text", "length" => ilProviderDB::CLASS_NAME_LENGTH, "notnull" => true]
                ]);
            $this->ilDB->addPrimaryKey(ilProviderDB::COMPONENT_TABLE, ["id", "component_type"]);
        }
    }

    /**
     * Create an unbound provider.
     *
     * @param   \ilObject   $owner
     * @param   string      $object_type
     * @param   string      $class_name
     * @param   string      $include_path
     * @return  UnboundProvider
     */
    protected function buildUnboundProvider($id, \ilObject $owner, $object_type, $class_name, $include_path) {
        assert('is_int($id)');
        assert('is_string($object_type)');
        assert('is_string($class_name)');
        assert('is_string($include_path)');
        assert('file_exists($include_path)');

        require_once($include_path);

        assert('class_exists($class_name)');

        if (!is_subclass_of($class_name, UnboundProvider::class)) {
            throw new \UnexpectedValueException(
                        "Class '$class_name' does not extend UnboundProvider.");
        }

        return new $class_name($id, $owner, $object_type);
    }

    /**
     * Build an object by its reference id.
     *
     * @param   int     $ref_id
     * @throws  \InvalidArgumentException if object could not be build
     * @return  \ilObject
     */
    protected function buildObjectByRefId($ref_id) {
        return \ilObjectFactory::getInstanceByRefId($ref_id);
    }

    /**
     * Build an object by its object id.
     *
     * @param   int     $ref_id
     * @throws  \InvalidArgumentException if object could not be build
     * @return  \ilObject
     */
    protected function buildObjectByObjId($ref_id) {
        return \ilObjectFactory::getInstanceByObjId($ref_id);
    }

    /**
     * Get all reference ids for an object id.
     *
     * @param   int     $obj_id
     * @return  int[]
     */
    protected function getAllReferenceIdsFor($obj_id) {
        return \ilObject::_getAllReferences($obj_id);
    }
}
