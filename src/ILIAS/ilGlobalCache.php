<?php
/******************************************************************************
 * An entity component framework for PHP.
 *
 * Copyright (c) 2018 Richard Klees <richard.klees@concepts-and-training.de>
 *
 * This software is licensed under GPLv3. You should have received a copy of
 * the license along with the code.
 */

namespace CaT\Ente\ILIAS;

/**
 * Contract for a cache.
 */
class ilGlobalCache implements Cache {
    /**
     * @var \ilGlobalCache
     */
    protected $il_global_cache;

    /**
     * @var int|null
     */
    protected $ttl_in_seconds;

    const PREFIX = "tms_ente_";

    public function __construct(\ilGlobalCache $cache, int $ttl_in_seconds = null) {
        $this->il_global_cache = $cache;
        $this->ttl_in_seconds = $ttl_in_seconds;
    }

    /**
     * @return void
     */
    public function set(string $key, array $value) {
        return $this->il_global_cache->set(self::PREFIX.$key, $value);
    }

    /**
     * @return array|null
     */
    public function get(string $key) {
        return $this->il_global_cache->get(self::PREFIX.$key);
    }

    /**
     * @return void
     */
    public function delete(string $key) {
        $this->il_global_cache->delete(self::PREFIX.$key);
    }
}
