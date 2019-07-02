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

use CaT\Ente\Component;
use CaT\Ente\Entity AS IEntity;

/**
 * An unbound provider is a provider that currently is not bound to an
 * entity and can thus not produce components.
 */
interface UnboundProvider
{
	/**
	 * @inheritdocs
	 */
	public function componentTypes();

	/**
	 * Build the component(s) of the given type for the given object.
	 * @return Component[]
	 */
	public function buildComponentsOf(string $component_type, IEntity $entity) : array;

	/**
	 * Get the id of this provider for the given owner.
	 *
	 * @throws \InvalidArgumentException if $owner is not an owner of this provider
	 */
	public function idFor(\ilObject $owner) : int;

	/**
	 * Get the owner object of the component.
	 *
	 * @return \ilObject[]
	 */
	public function owners() : array;

	/**
	 * Get the object type this binds to.
	 *
	 * @return string
	 */
	public function objectType() : string;
}
