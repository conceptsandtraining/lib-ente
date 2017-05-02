<?php

use \CaT\Plugins\ComponentProviderExample\Settings\ComponentProviderExample;

class SettingsTest extends PHPUnit_Framework_TestCase {
    public function test_providedStrings() {
        $some_strings = ["a", "b", "c"];
        $cpe = new ComponentProviderExample($some_strings);
        $this->assertEquals($some_strings, $cpe->providedStrings());
    }

    public function test_withProvidedStrings() {
        $cpe = new ComponentProviderExample([]);
        $this->assertEquals([], $cpe->providedStrings());
        $some_strings = ["d", "e", "f"];
        $cpe = $cpe->withProvidedStrings($some_strings);
        $this->assertEquals($some_strings, $cpe->providedStrings());
    }
}
