<?php
/******************************************************************************
 * An entity component framework for PHP.
 *
 * Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de>
 *
 * This software is licensed under GPLv3. You should have received a copy of
 * the license along with the code.
 */

declare(strict_types=1);

namespace CaT\Ente\ILIAS;

/**
 * Helper for repository objects that want to provide components.
 */
class ilProviderObjectHelper
{
    /**
     * @var ProviderDB
     */
    protected $provider_db;

    /**
     * @var \ilObject
     */
    protected $object;

    public function __construct(\ilObject $object, ProviderDB $provider_db)
    {
        $this->provider_db = $provider_db;
        $this->object = $object;
    }

    /**
     * Delete all unbound providers of this object.
     *
     * @return void
     */
    protected function deleteUnboundProviders()
    {
        $unbound_providers = $this->provider_db->unboundProvidersOf($this->object);
        foreach ($unbound_providers as $unbound_provider) {
            $this->provider_db->delete($unbound_provider, $this->object);
        }
    }

    /**
     * Create an unbound provider for this object.
     *
     * @param string $object_type for which the object provides
     * @param string $class_name of the unbound provider
     * @param string $path of the include file for the unbound provider class
     * @return    void
     */
    protected function createUnboundProvider($object_type, $class_name, $path)
    {
        if (is_subclass_of($class_name, SeparatedUnboundProvider::class)) {
            $this->provider_db->createSeparatedUnboundProvider($this->object, $object_type, $class_name, $path);
        } else if (is_subclass_of($class_name, SharedUnboundProvider::class)) {
            $this->provider_db->createSharedUnboundProvider($this->object, $object_type, $class_name, $path);
        } else {
            throw new \LogicException(
                "createUnboundProvider can only create providers " .
                "derived from Shared- or SeperatedUnboundProvider.");
        }
    }
}
