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

use \CaT\Ente\Entity AS IEntity;

/**
 * An entity over an ILIAS object.
 */
class Entity implements IEntity
{
	/**
	 * @var \ilObject
	 */
	private $object;

	public function __construct(\ilObject $object)
	{
		$this->object = $object;
	}

	/**
	 * @inheritdocs
	 */
	public function id() : int
	{
		return (int)$this->object->getId();
	}

	public function object() : \ilObject
	{
		return $this->object;
	}
}
