<?php
/******************************************************************************
 * An entity component framework for PHP.
 *
 * Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de>
 *
 * This software is licensed under GPLv3. You should have received a copy of
 * the license along with the code.
 */

use CaT\Ente;

/**
 * This testcases must be passed by a Provider.
 */
abstract class ProviderTest extends PHPUnit_Framework_TestCase {
    /**
     * To make this interesting, the provider should at least provide for one
     * entity.
     *
     * @return  Provider
     */
    abstract protected function provider();

    /**
     * To make this interesting, there should at least be one entity the provider
     * does not provide for.
     *
     * @return  Entity[]
     */
    abstract protected function doesNotProvideForEntities();

    /**
     * Some types of components the provider does not provide for.
     *
     * @return  string[]
     */
    abstract protected function doesNotProvideComponentType();

    // TEST

    /**
     * @dataProvider providesForEntities
     */
    public function test_only_provides_announced_component_types($entity) {
        $provider = $this->provider();
        foreach ($this->doesNotProvideComponentType() as $component_type) {
            $this->assertEmpty($provider->componentsOf($entity, $component_type));
        }
    }

    /**
     * @dataProvider providedComponentTypes
     */
    public function test_only_provides_for_announced_entities($component_type) {
        $provider = $this->provider();
        foreach ($this->doesNotProvideForEntities() as $entity) {
            $this->assertEmpty($provider->componentsOf($entity, $component_type));
        }
    }

    /**
     * @dataProvider providedEntitiesAndComponentType
     */
    public function test_provides_expected_component_types($entity, $component_type) {
        $provider = $this->provider();
        foreach($provider->componentsOf($entity, $component_type) as $component) {
            $this->assertInstanceOf($component_type, $component);
        }
    }

    // DATA PROVIDERS

    public function providesForEntities() {
        $provider = $this->provider();
        foreach ($provider->providesForEntities() as $entity) {
            yield [$entity];
        }
    }

    public function providedComponentTypes() {
        $provider = $this->provider();
        foreach ($provider->providedComponentTypes() as $type) {
            yield [$type];
        }
    }

    public function providedEntitiesAndComponentTypes() {
        foreach ($this->providesForEntities() as $entity) {
            foreach ($this->providedComponentTypes() as $type) {
                yield [$entity[0], $type[0]];
            }
        }
    }
}
