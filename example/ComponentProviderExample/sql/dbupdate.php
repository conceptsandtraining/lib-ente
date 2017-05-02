<#1>
<?php

require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/ComponentProviderExample/classes/Settings/ilDB.php");
$db = new \CaT\Plugins\ComponentProviderExample\Settings\ilDB($ilDB);
$db->install();
?>
