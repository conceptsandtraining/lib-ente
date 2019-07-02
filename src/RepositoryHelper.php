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

namespace CaT\Ente;

/**
 * Helper for all repositories. Repos only need to implement a minimal amount
 * of methods then.
 */
trait RepositoryHelper
{
	/**
	 * Get providers for an entity, possibly filtered by a component type.
	 * @return Provider[]
	 */
	abstract public function providersForEntity(
		Entity $entity,
		string $component_type = null
	) : array;

	/**
	 * Get components for the entity, possibly filtered by component type.
	 * @return Component[]
	 */
	public function componentsForEntity(Entity $entity, string $component_type = null) : array
	{
		$providers = $this->providersForEntity($entity, $component_type);
		$components = [];
		foreach ($providers as $provider) {
			if ($component_type !== null) {
				$components[] = $provider->componentsOfType($component_type);
			}
			else {
				foreach ($provider->componentTypes() as $type) {
					$components[] = $provider->componentsOfType($type);
				}
			}
		}
		if(count($components) > 0) {
			return call_user_func_array("array_merge", $components);
		} else {
			return $components;
		}
	}
}
