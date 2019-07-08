<?php
/******************************************************************************
 * An entity component framework for PHP.
 *
 * Copyright (c) 2018 Richard Klees <richard.klees@concepts-and-training.de>
 *
 * This software is licensed under GPLv3. You should have received a copy of
 * the license along with the code.
 */

declare(strict_types=1);

namespace CaT\Ente\ILIAS;

/**
 * Contract for a cache.
 */
interface Cache
{
    public function set(string $key, array $value);

    /**
     * @return array|null
     */
    public function get(string $key);

    public function delete(string $key);
}
