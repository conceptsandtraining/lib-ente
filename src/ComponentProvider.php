<?php
/******************************************************************************
 * An entity component framework for PHP.
 *
 * Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de>
 *
 * This software is licensed under GPLv3. You should have received a copy of
 * the license along with the code.
 */

namespace CaT\Ente;

/**
 * A component provider can be queried for components for an entity.
 *
 * ARCH:
 *  - There could be some other thingy, providing component providers for a
 *    specific entity instead one provider that can provide components for
 *    all entities. This would mean we would need `ProviderProvider` which
 *    seems odd. Having a provider providing components for all entities
 *    potentially also makes it possible to have some more high level queries,
 *    e.g. getting to know for which entities the provider actually provides
 *    components.
 */
interface ComponentProvider {
    /**
     * Get the components of a given type for the given entity.
     *
     * `$component_type` must be a class or interface name. The returned
     * components must implement that class or interface.
     *
     * For every `$component_type` not included in `providedComponentTypes`
     * this must return an empty array.
     *
     * For every `$entity` not included in `providesForEntities` this must
     * return an empty array.
     *
     * @param   Entity      $entity
     * @param   string      $component_type
     * @return  Component[]
     */
    public function componentsOf(Entity $entity, $component_type);

    /**
     * Get the component types this provider provides.
     *
     * @return  string[]
     */
    public function providedComponentTypes();

    /**
     * Get the entities this provider can provide components for.
     *
     * @return  Entity[]
     */
    public function providesForEntities();
}
