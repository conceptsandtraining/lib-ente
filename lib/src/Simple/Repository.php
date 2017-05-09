<?php
/******************************************************************************
 * An entity component framework for PHP.
 *
 * Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de>
 *
 * This software is licensed under GPLv3. You should have received a copy of
 * the license along with the code.
 */

namespace CaT\Ente\Simple;

use CaT\Ente;

/**
 * Simple implementation for a repository, works in memory.
 */
class Repository implements \CaT\Ente\Repository {
    /**
     * @var     array<string,Provider[]>
     */
    protected $providers;

    /**
     * @var     array<string,Entity>
     */
    protected $entities;

    public function __construct() {
        $this->providers = [];
        $this->entities = [];
    } 

    /**
     * @inheritdocs
     */
    public function providersForEntity(Ente\Entity $entity, $component_type = null) {
        $id = serialize($entity->id());
        if (!isset($this->providers[$id])) {
            return [];
        }

        $ret = [];
        foreach ($this->providers[$id] as $provider) {
            if ($component_type === null 
            || in_array($component_type, $provider->componentTypes())) {
                $ret[] = $provider;
            }
        }
        return $ret;
    }

    /**
     * @inheritdocs
     */
    public function providersForComponentType($component_type, $entities = null) {
        if ($entities !== null) {
            $entities = array_map(function($e) { return serialize($e->id()); }, $entities);
        }

        $ret = [];
        foreach ($this->providers as $id => $providers) {
            if ($entities !== null && !in_array($id, $entities)) {
                continue;
            }
            $ret[$id] = ["entity" => $this->entities[$id], "providers" => []];
            foreach ($providers as $provider) {
                if (in_array($component_type, $provider->componentTypes())) {
                    continue;
                }
                $ret[$id]["providers"][] = $provider;
            }
        }
        return array_values($ret);
    }

    /**
     * Add a provider to this repository.
     *
     * @param   Provider    $provider
     * @return  self
     */
    public function addProvider(Provider $provider) {
        $id = serialize($provider->entity()->id());
        if (!isset($this->entities[$id])) {
            $this->entities[$id] = $provider->entity();
        }
        if (!isset($this->providers[$id])) {
            $this->providers[$id] = [];
        }
        $this->providers[$id][] = $provider;
        return $this;
    }
}
