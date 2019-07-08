<?php

/* Copyright (c) 2018 Richard Klees <richard.klees@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\ComponentProviderExample\Settings;

/**
 * Implementation of database for settings.
 */
class ilDB
{
    const TABLE_NAME = "xlep_strings";

    /**
     * @var \ilDBInterface
     */
    protected $db;

    public function __construct(\ilDBInterface $db)
    {
        $this->db = $db;
    }

    /**
     * @inheritdoc
     */
    public function update(ComponentProviderExample $settings)
    {
        $obj_id = $settings->objId();
        $this->deleteFor($obj_id);
        foreach ($settings->providedStrings() as $value) {
            $this->db->insert(
                self::TABLE_NAME,
                [
                    "obj_id" => [
                        "integer",
                        $obj_id
                    ],
                    "value" => [
                        "string",
                        $value
                    ]
                ]
            );
        }
    }

    /**
     * @inheritdoc
     */
    public function getFor(int $obj_id) : ComponentProviderExample
    {
        $query = "SELECT value FROM " . self::TABLE_NAME . PHP_EOL
            ." WHERE obj_id = " . $this->db->quote($obj_id, "integer");
        $res = $this->db->query($query);

        $values = [];
        while ($row = $this->db->fetchAssoc($res)) {
            $values[] = $row["value"];
        }

        return new ComponentProviderExample($obj_id, $values);
    }

    /**
     * @inheritdoc
     */
    public function deleteFor(int $obj_id)
    {
        $statement = "DELETE FROM " . self::TABLE_NAME . PHP_EOL
            ."WHERE obj_id = " . $this->db->quote($obj_id, "integer");
        $this->ilDB->manipulate($statement);
    }

    public function install()
    {
        if (!$this->db->tableExists(self::TABLE_NAME)) {
            $this->db->createTable(
                self::TABLE_NAME,
                [
                    "obj_id" => [
                        "type" => "integer",
                        "length" => 4,
                        "notnull" => true
                    ],
                    "value" => [
                        "type" => "text",
                        "length" => 64,
                        "notnull" => true
                    ]
                ]
            );

            $this->db->addPrimaryKey(self::TABLE_NAME, ["obj_id", "value"]);
        }
    }
}
