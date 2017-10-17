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
    public function create(\ilObject $owner, $object_type, $class_name, $include_path) {
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
    public function unboundProvidersOf(\ilObject $owner) {
        $ret = [];

        $query =
            "SELECT id, object_type, class_name, include_path ".
            "FROM ".ilProviderDB::PROVIDER_TABLE." ".
            "WHERE owner = ".$this->ilDB->quote($owner->getId(), "integer");
        $res = $this->ilDB->query($query);

        while($row = $this->ilDB->fetchAssoc($res)) {
            $ret[] = $this->buildUnboundProvider($row["id"], $owner, $row["object_type"], $row["class_name"], $row["include_path"]);
        }

        return $ret;
    }

    /**
     * @inheritdocs
     */
    public function providersFor(\ilObject $object, $component_type = null) {
        assert('is_null($component_type) || is_string($component_type)');
        $ref_id = $object->getRefId();
        $sub_nodes_refs = $this->ilTree->getSubTreeIds($ref_id);
        $this->ilObjectDataCache->preloadReferenceCache($sub_nodes_refs);
        $sub_nodes_id_mapping = [];
        $sub_nodes_ids = [];
        foreach ($sub_nodes_refs as $ref_id) {
            $id = $this->ilObjectDataCache->lookupObjId($ref_id);
            $sub_nodes_id_mapping[$id] = $ref_id;
            $sub_nodes_ids[] = $id;
        }

        $object_type = $object->getType();
        if ($component_type === null) {
            $query =
                "SELECT id, owner, class_name, include_path ".
                "FROM ".ilProviderDB::PROVIDER_TABLE." ".
                "WHERE ".$this->ilDB->in("owner", $sub_nodes_ids, false, "integer").
                " AND object_type = ".$this->ilDB->quote($object_type, "string");
        }
        else {
            $query =
                "SELECT prv.id, prv.owner, prv.class_name, prv.include_path ".
                "FROM ".ilProviderDB::PROVIDER_TABLE." prv ".
                "JOIN ".ilProviderDB::COMPONENT_TABLE." cmp ".
                "ON prv.id = cmp.id ".
                "WHERE ".$this->ilDB->in("owner", $sub_nodes_ids, false, "integer").
                " AND object_type = ".$this->ilDB->quote($object_type, "string").
                " AND component_type = ".$this->ilDB->quote($component_type, "string");
        }

        $ret = [];
        $res = $this->ilDB->query($query);
        while ($row = $this->ilDB->fetchAssoc($res)) {
            $obj_id = $row["owner"];
            $ref_id = $sub_nodes_id_mapping[$obj_id];
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
     * TODO: This could be made faster for the filtered case by using getSubtree.
	 *
     * @inheritdocs
     */
    public function providersOf($component_type, array $objects = null) {
        $query = "SELECT id, owner, object_type, class_name, include_path "
                ."FROM ".ilProviderDB::PROVIDER_TABLE." prv "
                ."JOIN ".ilProviderDB::COMPONENT_TABLE." cmp ON prv.id = cmp.id "
                ."WHERE cmp.component_type = ".$this->ilDB->quote($component_type, "string");
        $res = $this->ilDB->query($query);

        $ret = [];
        if ($objects !== null) {
            $filter_ref_ids = array_map(function($o) { return $o->getRefId(); }, $objects);
        }
        else {
            $filter_ref_ids = null;
        }
        while ($row = $this->ilDB->fetchAssoc($res)) {
            $ref_ids = $this->getAllReferenceIdsFor($row["owner"]);
            foreach ($ref_ids as $ref_id) {
                $owner = $this->buildObjectByRefId($ref_id);
                $unbound_provider =
                    $this->buildUnboundProvider
                        ( $row["id"]
                        , $owner
                        , $row["object_type"]
                        , $row["class_name"]
                        , $row["include_path"]
                        );
                $path = $this->ilTree->getNodePath($ref_id);
                if ($path !== null) {
                    foreach ($path as $node) {
                        if ($node["type"] === $row["object_type"]) {
                            if ($filter_ref_ids === null || in_array($node["child"], $filter_ref_ids)) {
                                $object = $this->buildObjectByRefId($node["child"]);
                                $ret[] = new Provider($object, $unbound_provider);
                            }
                        }
                    }
                }
            }
        }

        return $ret;
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
