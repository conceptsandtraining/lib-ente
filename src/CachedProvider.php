<?php
/******************************************************************************
 * An entity component framework for PHP.
 *
 * Copyright (c) 2018 Richard Klees <richard.klees@concepts-and-training.de>
 *
 * This software is licensed under GPLv3. You should have received a copy of
 * the license along with the code.
 */

declare(strict_types=1);

namespace CaT\Ente;

/**
 * A chaching wrapper around a provider that caches components per type
 * and passes through the other methods.
 */
class CachedProvider implements Provider
{
	/**
	* @var Provider
	*/
	protected $provider;

	/**
	* @var array<string,Component[]>
	*/
	protected $cache;

	public function __construct(Provider $provider)
	{
		$this->provider = $provider;
		$this->cache = [];
	}

	/**
	* @inheritdocs
	*/
	public function componentsOfType(string $component_type) : array
	{
		if (!isset($this->cache[$component_type])) {
			$this->cache[$component_type] = $this->provider->componentsOfType($component_type);
		}
		return $this->cache[$component_type];
	}

	/**
	* @inheritdocs
	*/
	public function componentTypes() : string
	{
		return $this->provider->componentTypes();
	}

	/**
	* @inheritdocs
	*/
	public function entity() : Entity
	{
		return $this->provider->entity();
	}
}
