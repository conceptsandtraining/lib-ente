<?php
include_once("Services/Repository/classes/class.ilObjectPlugin.php");

/**
 * Object of the plugin
 */
class ilObjComponentHandlerExample extends ilObjectPlugin {
	/**
	 * Init the type of the plugin. Same value as choosen in plugin.php
	 */
	public function initType() {
		$this->setType("xleh");
	}

    /**
     * Returns an array with title => string[] entries containing the strings
     * provided for the object this plugin object is contained in.
     *
     * @return  array<string,string[]>
     */
    public function getProvidedStrings() {
        $repository = $this->plugin->getRepository();
        $entity = $this->getMyEntity();
		$components = $repository->componentsForEntity($entity, \CaT\Ente\Simple\AttachString::class);

        $provided_strings = [];
		foreach ($components as $component) {
            $provided_strings[] = $component->attachedString();
        }

        return $provided_strings;
    }

    /**
     * Get the entity this plugin object belongs to.
     *
     * @return  \CaT\Ente\Entity
     */
    protected function getMyEntity() {
        global $DIC;
        return new \CaT\Ente\ILIAS\Entity
            ( \ilObjectFactory::getInstanceByRefId
                ( $DIC->repositoryTree()->getParentId($this->getRefId())
                )
            );
    }
}
