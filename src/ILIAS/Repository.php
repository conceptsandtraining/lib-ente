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
 * An repository over ILIAS objects.
 */
class Repository implements \CaT\Ente\Repository {
    /**
     * @inheritdocs
     */
    public function providersForEntity(\CaT\Ente\Entity $entity, $component_type = null) {
        return [];
    }

    /**
     * @inheritdocs
     */
    public function providersForComponentType($component_type, $entities = null) {
        return [];
    }
}
