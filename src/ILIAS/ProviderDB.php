<?php
/******************************************************************************
 * An entity component framework for PHP.
 *
 * Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de>
 *
 * This software is licensed under GPLv3. You should have received a copy of
 * the license along with the code.
 */

use CaT\Ente\ILIAS;

/**
 * A database that stores ILIAS providers.
 */
interface ProviderDB {
    /**
     * Create a new provider for the given owner.
     *
     * The provider will be belong to objects above the $owner in the tree that also
     * have the type $obj_type.
     *
     * @param   \ilObject   $owner
     * @param   string      $obj_type
     * @param   string      $class_name
     * @param   string      $include_path
     * @return  Provider
     */
    public function create(\ilObject $owner, $obj_type, $class_name, $include_path);

    /**
     * Delete a given provider.
     *
     * @param   Provider    $provider
     * @return  null
     */
    public function delete(Provider $provider);

    /**
     * Get all providers for a given owner.
     *
     * @param   \ilObject   $owner
     * @return  Provider[]
     */
    public function providersOf(\ilObject $owner);
}
