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

use \CaT\Ente\Component;

/**
 * Attaches a string to an entity.
 *
 * Intended to be used for testing.
 */
interface AttachString extends Component
{
	/**
	 * Get the attached string.
	 */
	public function attachedString() : string;
}
