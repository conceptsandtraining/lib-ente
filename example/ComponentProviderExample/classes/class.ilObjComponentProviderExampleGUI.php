<?php

include_once("./Services/Repository/classes/class.ilObjectPluginGUI.php");

/**
 * Plugin object GUI class. Baseclass for all GUI action in ILIAS
 *
 * @ilCtrl_isCalledBy ilObjComponentProviderExampleGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
 * @ilCtrl_Calls ilObjComponentProviderExampleGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI, ilCommonActionDispatcherGUI
 */
class ilObjComponentProviderExampleGUI  extends ilObjectPluginGUI {
	/**
	 * Called after parent constructor. It's possible to define some plugin special values
	 */
	protected function afterConstructor() {
	}

	/**
	* Get type.  Same value as choosen in plugin.php
	*/
	final function getType() {
		return "xlep";
	}

	/**
	* Handles all commmands of this class, centralizes permission checks
	*/
	function performCommand($cmd) {
		switch ($cmd) {
			default:
		}
	}

	/**
	* After object has been created -> jump to this command
	*/
	function getAfterCreationCmd() {
		return "";
	}

	/**
	* Get standard command
	*/
	function getStandardCmd() {
		return "";
	}
}
