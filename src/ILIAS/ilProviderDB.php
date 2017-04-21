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
    const PROVIDER_TABLE = "ente_providers";
    const COMPONENT_TABLE = "ente_provider_components";

    const CLASS_NAME_LENGTH = 128;
    const PATH_LENGTH = 1024;

    /**
     * @var \ilDBInterface
     */
    private $ilDB;

    public function __construct(\ilDBInterface $ilDB) {
        $this->ilDB = $ilDB;
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

        $id= $this->ilDB->nextId(ilProviderDB::PROVIDER_TABLE);
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
            "WHERE owner_id = ".$this->ilDB->quote($owner->getId(), "integer");
        $res = $this->ilDB->query($query);

        while($row = $this->ilDB->fetchAssoc($res)) {
            $ret[] = $this->buildUnboundProvider($row["id"], $owner, $row["object_type"], $row["class_name"], $row["include_path"]);
        }

        return $ret;
    }

    /**
     * @inheritdocs
     */
    public function providersFor(\ilObject $object) {
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
                , "object_type" => ["type" => "string", "length" => 4, "notnull" => true]
                , "class_name" => ["type" => "string", "length" => ilProviderDB::CLASS_NAME_LENGTH, "notnull" => true]
                , "include_path" => ["type" => "string", "length" => ilProviderDB::PATH_LENGTH, "notnull" => true]
                ]);
            $this->ilDB->addPrimaryKey(ilProviderDB::PROVIDER_TABLE, ["id"]);
        }
        if (!$this->ilDB->tableExists(ilProviderDB::COMPONENT_TABLE)) {
            $this->ilDB->createTable(ilProviderDB::COMPONENT_TABLE,
                [ "id" => ["type" => "integer", "length" => 4, "notnull" => true]
                , "component_type" => ["type" => "string", "length" => ilProviderDB::CLASS_NAME_LENGTH, "notnull" => true]
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
            throw new UnexpectedValueException(
                        "Class '$class_name' does not extend UnboundProvider.");
        }

        return new $class_name($id, $owner, $object_type);
    }
}
