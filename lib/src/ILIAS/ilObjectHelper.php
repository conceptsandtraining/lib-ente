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
 * Basic helper for ILIAS object using this framework. Do not
 * use this directly, use ilHandlerObjectHelper or
 * ilProviderObjectHelper.
 */
trait ilObjectHelper {
	/**
	 * @var Ente\ILIAS\ProviderDB
	 */
	private $provider_db = null;

    /**
     * @return \CaT\Ente\ILIAS\ProviderDB
     */
    protected function getProviderDB() {
		$DIC = $this->getDIC();
        if ($this->provider_db === null) {
            $this->provider_db = new \CaT\Ente\ILIAS\ilProviderDB
                ( $DIC["ilDB"]
                , $DIC["tree"]
                , $DIC["ilObjDataCache"]
				, $DIC
                );
        }
        return $this->provider_db;
    }

	/**
	 * Get the ILIAS DIC.
	 *
	 * @return \ArrayAccess
	 */
	abstract protected function getDIC();
}
