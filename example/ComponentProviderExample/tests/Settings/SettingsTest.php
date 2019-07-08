<?php

/* Copyright (c) 2018 Richard Klees <richard.klees@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\ComponentProviderExample\Settings;

use PHPUnit\Framework\TestCase;

class SettingsTest extends TestCase
{
    public function test_objId()
    {
        $cpe = new ProvidedStringComponents(23, []);
        $this->assertEquals(23, $cpe->getObjId());
    }

    public function test_providedStrings()
    {
        $some_strings = ["a", "b", "c"];
        $cpe = new ProvidedStringComponents(23, $some_strings);
        $this->assertEquals($some_strings, $cpe->getProvidedStrings());
    }

    public function test_withProvidedStrings()
    {
        $cpe = new ProvidedStringComponents(23, []);
        $this->assertEquals([], $cpe->getProvidedStrings());
        $some_strings = ["d", "e", "f"];
        $cpe = $cpe->withProvidedStrings($some_strings);
        $this->assertEquals(23, $cpe->getObjId());
        $this->assertEquals($some_strings, $cpe->getProvidedStrings());
    }
}
