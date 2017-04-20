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
    public function create(\ilObject $owner, $obj_type, $class_name, $include_path) {
    }

    /**
     * @inheritdocs
     */
    public function delete(Provider $provider) {
    }

    /**
     * @inheritdocs
     */
    public function unboundProvidersOf(\ilObject $owner) {
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
                , "class_name" => ["type" => "string", "length" => 64, "notnull" => true]
                , "include_path" => ["type" => "string", "length" => 1024, "notnull" => true]
                ]);
        }
        if (!$this->ilDB->tableExists(ilProviderDB::COMPONENT_TABLE)) {
            $this->ilDB->createTable(ilProviderDB::COMPONENT_TABLE,
                [ "id" => ["type" => "integer", "length" => 4, "notnull" => true]
                , "component_type" => ["type" => "string", "length" => 64, "notnull" => true]
                ]);
        }
    }
}
