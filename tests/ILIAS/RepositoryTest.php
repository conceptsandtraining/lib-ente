<?php
/******************************************************************************
 * An entity component framework for PHP.
 *
 * Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de>
 *
 * This software is licensed under GPLv3. You should have received a copy of
 * the license along with the code.
 */

use CaT\Ente\ILIAS\Entity;
use CaT\Ente\ILIAS\Provider;
use CaT\Ente\ILIAS\Repository;
use CaT\Ente\Simple;
use CaT\Ente\Simple\AttachString;
use CaT\Ente\Simple\AttachStringMemory;
use CaT\Ente\Simple\AttachInt;
use CaT\Ente\Simple\AttachIntMemory;

require_once(__DIR__."/../RepositoryTest.php");

if (!class_exists("ilObject")) {
    require_once(__DIR__."/ilObject.php");
}

class ILIAS_RepositoryTest extends RepositoryTest {
    /**
     * @inheritdocs
     */
    protected function repository() {
        return new Repository();
    }

    /**
     * @inheritdocs
     */
    protected function hasProvidersForEntities() {
        return [];
    }

    /**
     * @inheritdocs
     */
    protected function hasProvidersForComponentTypes() {
        return [AttachInt::class, AttachString::class];
    }
}
