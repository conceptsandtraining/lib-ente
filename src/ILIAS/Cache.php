<?php
/******************************************************************************
 * An entity component framework for PHP.
 *
 * Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de>
 *
 * This software is licensed under GPLv3. You should have received a copy of
 * the license along with the code.
 */

namespace CaT\Ente\ILIAS;

/**
 * Contract for a cache.
 */
interface Cache {
    /**
     * @return void
     */
    public function set(string $key, array $value);

    /**
     * @return array|null
     */
    public function get(string $key);

    /**
     * @return void
     */
    public function delete(string $key);
}
