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
 * In memory implementation of AttachInt.
 */
class AttachIntMemory implements AttachInt
{
	/**
	 * @var IEntity
	 */
	private $entity;

	/**
	 * @var int
	 */
	private $attached_int;

	public function __construct(IEntity $entity, int $attached_int)
	{
		$this->entity = $entity;
		$this->attached_int = $attached_int;
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
	public function attachedInt() : int
	{
		return $this->attached_int;
	}
}
