<?php
/******************************************************************************
 * An entity component framework for PHP.
 *
 * Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de>
 *
 * This software is licensed under GPLv3. You should have received a copy of
 * the license along with the code.
 */

namespace CaT\Ente\ILIAS;

use CaT\Ente;

/**
 * Helper for repository objects that want to provide components.
 */
trait ilProviderObjectHelper {
	use ilObjectHelper;

	/**
	 * Delete all unbound providers of this object.
	 *
	 * @return null
	 */
	protected function deleteUnboundProviders() {
		if (!($this instanceof \ilObject)) {
			throw new \LogicException("ilProviderObjectHelper can only be used with ilObjects.");
		} 

		$provider_db = $this->getProviderDB();
		$unbound_providers = $provider_db->unboundProvidersOf($this);
		foreach ($unbound_providers as $unbound_provider) {
			$provider_db->delete($unbound_provider);
		}
	}
}
