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
 * Attaches an integer to an entity.
 *
 * Intended to be used for testing.
 */
interface AttachInt extends Component
{
	/**
	 * Get the attached integer.
	 */
	public function attachedInt() : int;
}
