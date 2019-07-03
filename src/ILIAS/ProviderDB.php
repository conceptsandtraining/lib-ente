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

/**
 * A database that stores ILIAS providers.
 */
interface ProviderDB
{
	/**
	 * Create a new separated unbound provider for the given owner.
	 *
	 * The provider will belong to objects above the $owner in the tree that also
	 * have the type $obj_type.
	 */
	public function createSeparatedUnboundProvider(
		\ilObject $owner,
		string $obj_type,
		string $class_name,
		string $include_path
	) : SeparatedUnboundProvider;

	/**
	 * Create a new shared unbound provider for the given owner.
	 *
	 * The provider will be belong to objects above the $owner in the tree that also
	 * have the type $obj_type.
	 */
	public function createSharedUnboundProvider(
		\ilObject $owner,
		string $obj_type,
		string $class_name,
		string $include_path
	) : SharedUnboundProvider;

	/**
	 * Load the unbound provider with the given id.
	 * @throws  \InvalidArgumentException if the provider with the supplied id does not exist.
	 */
	public function load(int $id) : UnboundProvider;

	/**
	 * Delete a given unbound provider.
	 */
	public function delete(UnboundProvider $provider, \ilObject $owner);

	/**
	 * Update the given unbound provider.
	 *
	 * The only thing that may be updated are the components that are provided.
	 */
	public function update(UnboundProvider $provider);

	/**
	 * Get all unbound providers of a given owner.
	 * @return  UnboundProvider[]
	 */
	public function unboundProvidersOf(\ilObject $owner) : array;

	/**
	 * Get all providers for the given object.
	 * @return  Provider[]
	 */
	public function providersFor(
		\ilObject $object,
		string $component_type = null
	) : array;
}
