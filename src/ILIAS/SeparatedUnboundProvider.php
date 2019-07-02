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
 * An separated unbound provider is a unbound provider that has a single owner.
 */
abstract class SeparatedUnboundProvider implements UnboundProvider
{
	/**
	 * @var int
	 */
	private $id;

	/**
	 * @var \ilObject
	 */
	private $owner;

	/**
	 * @var string
	 */
	private $object_type;

	final public function __construct(int $id, \ilObject $owner, string $object_type)
	{
		$this->id = $id;
		$this->owner = $owner;
		$this->object_type = $object_type;
	}

	/**
	 * @inheritdocs
	 */
	abstract public function componentTypes();

	/**
	 * Build the component(s) of the given type for the given object.
	 * @return  Component[]
	 */
	abstract public function buildComponentsOf(string $component_type, IEntity $entity) : array;

	/**
	 * @inheritdocs
	 */
	final public function idFor(\ilObject $owner) : int
	{
		if ($owner->getId() !== $this->owner->getId()) {
			throw new \InvalidArgumentException(
				"Object with id "
				.$owner->getId()
				." is not the owner with id ".$this->owner->getId()
			);
		}
		return $this->id;
	}

	/**
	 * @inheritdocs
	 */
	final public function owners() : array
	{
		return [$this->owner];
	}

	/**
	 * @inheritdocs
	 */
	final public function owner() : \ilObject
	{
		return $this->owner;
	}

	/**
	 * @inheritdocs
	 */
	final public function objectType() : string
	{
		return $this->object_type;
	}
}
