<?php

/* Copyright (c) 2018 Richard Klees <richard.klees@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\ComponentProviderExample\Settings;

/**
 * Settings for an ComponentProviderExample.
 */
class ComponentProviderExample
{
    /**
     * @var int
     */
    protected $obj_id;

    /**
     * @var string[]
     */
    protected $provided_strings;

    public function __construct(int $obj_id, array $provided_strings)
    {
        assert('array_sum(array_map("is_string", $provided_strings)) == count($provided_strings)');
        $this->obj_id = $obj_id;
        $this->provided_strings = $provided_strings;
    }

    public function getObjId() : int
    {
        return $this->obj_id;
    }

    public function getProvidedStrings() : array
    {
        return $this->provided_strings;
    }

    public function withProvidedStrings(array $provided_strings) : ComponentProviderExample
    {
        assert('array_sum(array_map("is_string", $provided_strings)) == count($provided_strings)');
        $clone = clone $this;
        $clone->provided_strings = $provided_strings;
        return $clone;
    }
}
