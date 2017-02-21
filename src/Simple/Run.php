<?php
/******************************************************************************
 * An entity component framework for PHP.
 *
 * Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de>
 *
 * This software is licensed under GPLv3. You should have received a copy of
 * the license along with the code.
 */

namespace CaT\Ente\Simple;

/**
 * Runs something on the closure and returns the result.
 *
 * Intended to be used for testing.
 */
interface Run extends \CaT\Ente\Component {
    /**
     * Call the closure with the entity and get the result.
     *
     * @return mixed
     */
    public function run();
}
