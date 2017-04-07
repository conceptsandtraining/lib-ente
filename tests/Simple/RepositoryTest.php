<?php
/******************************************************************************
 * An entity component framework for PHP.
 *
 * Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de>
 *
 * This software is licensed under GPLv3. You should have received a copy of
 * the license along with the code.
 */

use CaT\Ente\Simple\Entity;
use CaT\Ente\Simple\Repository;
use CaT\Ente\Simple\Provider;
use CaT\Ente\Simple\Run;
use CaT\Ente\Simple\CallClosure;

require_once(__DIR__."/../RepositoryTest.php");

class Simple_RepositoryTest extends RepositoryTest {
    protected function entities() {
        return 
            [new Entity(0),
             new Entity(1),
             new Entity(2),
             new Entity(3)];
            
    }

    /**
     * @inheritdocs
     */
    protected function repository() {
        $entities = $this->entities();
        $repo = new Repository();

        foreach ($entities as $e) {
            $p = new Provider($e);
            $p->addComponent(new CallClosure($e, function($e) {
                return $e->id();
            }));
            $repo->addProvider($p);
        }

        return $repo;
    }

    /**
     * @inheritdocs
     */
    protected function hasProvidersForEntities() {
        return $this->entities();
    }

    /**
     * The component types the repository has providers for.
     *
     * @return  string[]
     */
    protected function hasProvidersForComponentTypes() {
        return [Run::class];
    }
}
