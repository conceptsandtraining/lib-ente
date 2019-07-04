<?php
/******************************************************************************
 * An entity component framework for PHP.
 *
 * Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de>
 *
 * This software is licensed under GPLv3. You should have received a copy of
 * the license along with the code.
 */

declare(strict_types=1);

namespace CaT\Ente\ILIAS;

use \CaT\Ente\Component AS IComponent;
use \CaT\Ente\Entity AS IEntity;
use \CaT\Ente\CachedRepository;

/**
 * Helper for repository objects that want to handle components.
 */
trait ilHandlerObjectHelper
{
    use ilObjectHelper;

    /**
     * Get a repository for providers and components.
     *
     * @return \CaT\Ente\Repository
     */
    protected function getRepository()
    {
        $DIC = $this->getDIC();
        if (!isset($DIC["ente.repository"])) {
            $DIC["ente.repository"] = function ($c) {
                return new CachedRepository(
                    new Repository($c["ente.provider_db"])
                );
            };
        }
        return $DIC["ente.repository"];
    }

    /**
     * Get components for the entity.
     *
     * @return IComponent[]
     */
    protected function getComponents()
    {
        $repository = $this->getRepository();
        return $repository->componentsForEntity($this->getEntity());
    }

    /**
     * @return IComponent[]
     */
    protected function getComponentsOfType(string $component_type): array
    {
        $repository = $this->getRepository();
        return $repository->componentsForEntity($this->getEntity(), $component_type);
    }

    /**
     * Get the entity this object handles components for.
     */
    protected function getEntity(): IEntity
    {
        return new Entity(
            \ilObjectFactory::getInstanceByRefId(
                $this->getEntityRefId()
            )
        );
    }

    /**
     * Get the ref_id of the object this object handles components for.
     */
    abstract protected function getEntityRefId(): int;
}
