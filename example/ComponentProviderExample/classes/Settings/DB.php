<?php

/* Copyright (c) 2018 Richard Klees <richard.klees@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\ComponentProviderExample\Settings;

/**
 * Interface for DB handle of additional settings.
 */
interface DB
{
    public function update(ComponentProviderExample $settings);
    public function getFor(int $obj_id) : ComponentProviderExample;
    public function deleteFor(int $obj_id);
}
