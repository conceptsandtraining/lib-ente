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

use CaT\Ente;

/**
 * Basic helper for ILIAS object using this framework. Do not
 * use this directly, use ilHandlerObjectHelper or
 * ilProviderObjectHelper.
 */
trait ilObjectHelper
{
	/**
	 * @return Ente\ILIAS\ProviderDB
	 */
	protected function getProviderDB() : Ente\ILIAS\ProviderDB
	{
		$DIC = $this->getDIC();
		if (!isset($DIC["ente.provider_db"])) {
			$DIC["ente.provider_db"] = new Ente\ILIAS\ilProviderDB(
				$DIC["ilDB"],
				$DIC["tree"],
				$DIC["ilObjDataCache"]
			);
		}
		return $DIC["ente.provider_db"];
	}

	/**
	 * Get the ILIAS DIC.
	 *
	 * @return \ArrayAccess
	 */
	abstract protected function getDIC();
}
