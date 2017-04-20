<?php
/******************************************************************************
 * An entity component framework for PHP.
 *
 * Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de>
 *
 * This software is licensed under GPLv3. You should have received a copy of
 * the license along with the code.
 */

use CaT\Ente\ILIAS\ilProviderDB;

if (!class_exists("ilObject")) {
    require_once(__DIR__."/ilObject.php");
}

if (!interface_exists("ilDBInterface")) {
    require_once(__DIR__."/ilDBInterface.php");
}

class ILIAS_ilProviderDBTest extends PHPUnit_Framework_TestCase {
    public function test_createTables() {
        $il_db = $this
            ->getMockBuilder(\ilDBInterface::class)
            ->setMethods(["nextId","createTable","addPrimaryKey","createSequence",
                          "tableExists","addIndex","query","insert","fetchAssoc","quote"])
            ->getMock();

        $provider_table =
            [ "id" => ["type" => "integer", "length" => 4, "notnull" => true]
            , "owner" => ["type" => "integer", "length" => 4, "notnull" => true]
            , "object_type" => ["type" => "string", "length" => 4, "notnull" => true]
            , "class_name" => ["type" => "string", "length" => 64, "notnull" => true]
            , "include_path" => ["type" => "string", "length" => 1024, "notnull" => true]
            ];
        $component_table =
            [ "id" => ["type" => "integer", "length" => 4, "notnull" => true]
            , "component_type" => ["type" => "string", "length" => 64, "notnull" => true]
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
   
        $db = new ilProviderDB($il_db);
        $db->createTables();
    }
}
