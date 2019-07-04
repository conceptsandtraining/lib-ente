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

use CaT\Ente\Component;
use CaT\Ente\Entity AS IEntity;

/**
 * An shared unbound provider is an unbound provider that has a multiple owners
 * and may provide components based on a combination of owners properties.
 */
abstract class SharedUnboundProvider implements UnboundProvider
{
    /**
     * @var \ilObject[]
     */
    private $owners;

    /**
     * @var array<int, int>
     */
    private $ids;

    /**
     * @var string
     */
    private $object_type;

    final public function __construct(array $owners, string $object_type)
    {
        $this->owners = [];
        $this->ids = [];
        foreach ($owners as $id => $owner) {
            assert('is_int($id)');
            assert('$owner instanceof \ilObject');
            $this->owners[] = $owner;
            $this->ids[$owner->getId()] = $id;
        }
        $this->object_type = $object_type;
    }

    /**
     * @inheritdocs
     */
    abstract public function componentTypes();

    /**
     * Build the component(s) of the given type for the given object.
     * @return Component[]
     */
    abstract public function buildComponentsOf(string $component_type, IEntity $entity): array;

    /**
     * @inheritdocs
     */
    final public function idFor(\ilObject $owner): int
    {
        $id = $owner->getId();
        if (!isset($this->ids[$id])) {
            throw new \InvalidArgumentException(
                "Object with id " . $owner->getId() . " is not an owner");
        }
        return (int)$this->ids[$id];
    }

    /**
     * @inheritdocs
     */
    final public function owners(): array
    {
        return $this->owners;
    }

    /**
     * @inheritdocs
     */
    final public function objectType(): string
    {
        return $this->object_type;
    }
}
