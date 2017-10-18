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

use CaT\Ente;

/**
 * Helper for repository objects that want to handle components.
 */
trait ilHandlerObjectHelper {
	use ilObjectHelper;

    /**
     * @var \CaT\Ente\ILIAS\Repository|null
     */
    protected $repository = null;

    /**
     * Get a repository for providers and components.
     *
     * @return \CaT\Ente\Repository
     */
    protected function getRepository() {
        if ($this->repository === null) {
            $this->repository = new \CaT\Ente\ILIAS\Repository($this->getProviderDB());
        }
        return $this->repository;
    }

	/**
	 * Get components for the entity.
	 *
	 * TODO: this may as well be protected
	 *
	 * @return	Component[]
	 */
	public function getComponents() {
		$repository = $this->getRepository();
		return $repository->componentsForEntity($this->getEntity());
	}

	/**
	 * Get components for the entity.
	 *
	 * TODO: this may as well be protected
	 *
	 * @param	string		$component_type
	 * @return	Component[]
	 */
	public function getComponentsOfType($component_type) {
		assert('is_string($component_type)');
		$repository = $this->getRepository();
		return $repository->componentsForEntity($this->getEntity(), $component_type);
	}

	/**
	 * Get the entity this object handles components for.
	 *
	 * @return Ente\Entity
	 */
	protected function getEntity() {
        return new \CaT\Ente\ILIAS\Entity
            ( \ilObjectFactory::getInstanceByRefId
                ( $this->getEntityRefId()
                )
            );
	}

	/**
	 * Get the ref_id of the object this object handles components for.
	 *
	 * @return int
	 */
	abstract protected function getEntityRefId();
}
