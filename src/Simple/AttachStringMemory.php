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

namespace CaT\Ente\Simple;

use CaT\Ente\Entity as IEntity;

/**
 * In memory implementation of AttachString.
 */
class AttachStringMemory implements AttachString
{
	/**
	* @var IEntity
	*/
	private $entity;

	/**
	* @var string
	*/
	private $attached_string;

	public function __construct(IEntity $entity, string $attached_string)
	{
		$this->entity = $entity;
		$this->attached_string = $attached_string;
	}

	/**
	* @inheritdocs
	*/
	public function entity() : IEntity
	{
		return $this->entity;
	}

	/**
	* @inheritdocs
	*/
	public function attachedString() : string
	{
		return $this->attached_string;
	}
}
