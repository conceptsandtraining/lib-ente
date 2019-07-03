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
 * A database that stores ILIAS providers.
 */
class ilProviderDB implements ProviderDB
{
	const PROVIDER_TABLE = "ente_prvs";
	const COMPONENT_TABLE = "ente_prv_cmps";

	const CLASS_NAME_LENGTH = 128;
	const PATH_LENGTH = 1024;

	/**
	 * @var \ilDBInterface
	 */
	private $ilDB;

	/**
	 * @var \ilTree
	 */
	private $ilTree;

	/**
	 * @var \ilObjectDataCache
	 */
	private $ilObjectDataCache;

	public function __construct(\ilDBInterface $ilDB, \ilTree $tree, \ilObjectDataCache $cache)
	{
		$this->ilDB = $ilDB;
		$this->ilTree = $tree;
		$this->ilObjectDataCache = $cache;
	}

	/**
	 * @inheritdocs
	 */
	public function createSeparatedUnboundProvider(
		\ilObject $owner,
		string $object_type,
		string $class_name,
		string $include_path
	) : SeparatedUnboundProvider {
		$this->validateCreationParams($object_type, $class_name, $include_path);
		$shared = false;
		list($unbound_provider, $id) = $this->createUnboundProvider(
			$owner,
			$object_type,
			$class_name,
			$include_path,
			$shared
		);
		$this->createComponentsForUnboundProvider($unbound_provider, $id);
		return $unbound_provider;
	}

	/**
	 * @inheritdocs
	 */
	public function createSharedUnboundProvider(
		\ilObject $owner,
		string $object_type,
		string $class_name,
		string $include_path
	) : SharedUnboundProvider {
		$this->validateCreationParams($object_type, $class_name, $include_path);
		$shared = true;
		list($unbound_provider, $id) = $this->createUnboundProvider(
			$owner,
			$object_type,
			$class_name,
			$include_path,
			$shared
		);
		$this->createComponentsForUnboundProvider($unbound_provider, $id);
		return $unbound_provider;
	}

	/**
	 * @return  array(UnboundProvider, int)
	 */
	private function createUnboundProvider(
		\ilObject $owner,
		string $object_type,
		string $class_name,
		string $include_path,
		bool $shared
	) : array {
		// TODO: check if class exist first
		$id = (int)$this->ilDB->nextId(ilProviderDB::PROVIDER_TABLE);
		$this->ilDB->insert(
			ilProviderDB::PROVIDER_TABLE,
			[
				"id" => ["integer", $id],
				"owner" => ["integer", $owner->getId()],
				"object_type" => ["string", $object_type],
				"class_name" => ["string", $class_name],
				"include_path" => ["string", $include_path],
				"shared" => ["integer", $shared]
			]
		);

		if($shared===true) {
			$unbound_provider = $this->buildSharedUnboundProvider(
				array($id=>$owner),
				$class_name,
				$class_name,
				$include_path
			);
		} else {
			$unbound_provider = $this->buildSeparatedUnboundProvider(
				$id,
				$owner,
				$class_name,
				$class_name,
				$include_path
			);
		}
		return array($unbound_provider, $id);
	}


	/**
	 * @throws  \LogicException     if any parameter is out of bounds
	 */
	private function validateCreationParams(
		string $object_type,
		string $class_name,
		string $include_path
	) {
		if (strlen($object_type) > 4) {
			throw new \LogicException(
				"Expected object type '$object_type' to have four or less chars."
			);
		}
		if (strlen($class_name) > ilProviderDB::CLASS_NAME_LENGTH) {
			throw new \LogicException(
						"Expected class name '$class_name' to have at most "
						.ilProviderDB::CLASS_NAME_LENGTH." chars.");
		}
		if (strlen($include_path) > ilProviderDB::PATH_LENGTH) {
			throw new \LogicException(
						"Expected include path '$include_path' to have at most "
						.ilProviderDB::PATH_LENGTH." chars.");
		}
	}

	/**
	 * @throws  \LogicException     if class_name of component is out of bounds
	 */
	private function createComponentsForUnboundProvider(UnboundProvider $unbound_provider, int $id)
	{
		foreach ($unbound_provider->componentTypes() as $component_type) {
			if (strlen($component_type) > ilProviderDB::CLASS_NAME_LENGTH) {
				throw new \LogicException(
							"Expected component type '$component_type' to have at most "
							.ilProviderDB::CLASS_NAME_LENGTH." chars."
				);
			}
			$this->ilDB->insert(
				ilProviderDB::COMPONENT_TABLE,
				[
					"id" => ["integer", $id],
					"component_type" => ["string", $component_type]
				]
			);
		}
	}

	/**
	 * @inheritdocs
	 */
	public function load(int $id) : UnboundProvider
	{
		assert('is_int($id)');

		$query =
			"SELECT owner, object_type, class_name, include_path ".
			"FROM ".ilProviderDB::PROVIDER_TABLE." ".
			"WHERE id = ".$this->ilDB->quote($id, "integer");
		$res = $this->ilDB->query($query);

		if($row = $this->ilDB->fetchAssoc($res)) {
			$owner = $this->buildObjectByObjId($row["owner"]);
			return $this->buildSeparatedUnboundProvider(
				$id, $owner, $row["object_type"],
				$row["class_name"],
				$row["include_path"]
			);
		}
		else {
			throw new \InvalidArgumentException(
				"Unbound provider with id '$id' does not exist."
			);
		}
	}

	/**
	 * @inheritdocs
	 */
	public function delete(UnboundProvider $provider, \ilObject $owner)
	{
		$id = $provider->idFor($owner);
		$this->ilDB->manipulate(
			"DELETE FROM ".ilProviderDB::PROVIDER_TABLE
			." WHERE id = ".$this->ilDB->quote($id, "integer")
		);
		$this->ilDB->manipulate(
			"DELETE FROM ".ilProviderDB::COMPONENT_TABLE
			." WHERE id = ".$this->ilDB->quote($id, "integer")
		);
	}

	/**
	 * @inheritdocs
	 */
	public function update(UnboundProvider $provider)
	{
		$component_types = $provider->componentTypes();
		foreach ($provider->owners() as $owner) {
			$id = $provider->idFor($owner);
			$this->ilDB->manipulate(
				"DELETE FROM ".ilProviderDB::COMPONENT_TABLE
				." WHERE id = ".$this->ilDB->quote($id, "integer")
			);

			foreach ($component_types as $component_type) {
				if (strlen($component_type) > ilProviderDB::CLASS_NAME_LENGTH) {
					throw new \LogicException(
						"Expected component type '"
						.$component_type."'' to have at most "
						.ilProviderDB::CLASS_NAME_LENGTH." chars."
					);
				}
				$this->ilDB->insert(
					ilProviderDB::COMPONENT_TABLE,
					[
						"id" => ["integer", $id],
						"component_type" => ["string", $component_type]
					]
				);
			}
		}
	}

	/**
	 * @inheritdocs
	 */
	public function unboundProvidersOf(\ilObject $owner) : array
	{
		$ret = [];

		$query =
			"SELECT id, object_type, class_name, include_path ".
			"FROM ".ilProviderDB::PROVIDER_TABLE." ".
			"WHERE owner = ".$this->ilDB->quote($owner->getId(), "integer");
		$res = $this->ilDB->query($query);

		while($row = $this->ilDB->fetchAssoc($res)) {
			if(
				is_subclass_of(
					$row["class_name"],
					'CaT\Ente\ILIAS\SeparatedUnboundProvider'
				)
			) {
				$ret[] = $this->buildSeparatedUnboundProvider(
					(int)$row["id"], $owner,
					$row["object_type"],
					$row["class_name"],
					$row["include_path"]
				);
			}
			if(
				is_subclass_of(
					$row["class_name"],
					'CaT\Ente\ILIAS\SharedUnboundProvider'
				)
			) {
				$ret[] = $this->buildSharedUnboundProvider(
					array($row["id"]=>$owner),
					$row["object_type"],
					$row["class_name"],
					$row["include_path"]
				);
			}
		}

		return $ret;
	}

	/**
	 * @inheritdocs
	 */
	public function providersFor(
		\ilObject $object,
		string $component_type = null
	) : array {
		list($nodes_ids, $nodes_id_mapping) = $this->getSubtreeObjectIdsAndRefIdMapping(
			(int)$object->getRefId()
		);
		$object_type = $object->getType();

		$ret = [];

		$provider_data = $this->getSeperatedUnboundProviderDataOf(
			$nodes_ids,
			$object_type,
			$component_type
		);
		foreach ($provider_data as $data) {
			$obj_id = $data["owner"];
			$ref_id = $nodes_id_mapping[$obj_id];
			try {
				$owner = $this->buildObjectByRefId($ref_id);
			}
			catch (\InvalidArgumentException $e) {
				continue;
			}
			$ret[] = new Provider(
				$object,
				$this->buildSeparatedUnboundProvider(
					$data["id"],
					$owner,
					$object_type,
					$data["class_name"],
					$data["include_path"]
				)
			);
		}

		$provider_data = $this->getSharedUnboundProviderDataOf(
			$nodes_ids,
			$object_type,
			$component_type
		);
		foreach ($provider_data as $data) {
			$owners = [];
			foreach ($data["owners"] as $obj_id) {
				$ref_id = $nodes_id_mapping[$obj_id];
				$prv_id = array_shift($data["ids"]);
				try {
					$owners[$prv_id] = $this->buildObjectByRefId($ref_id);
				}
				catch (\InvalidArgumentException $e) {
					continue;
				}
			}
			if (count($owners) > 0) {
				$ret[] = new Provider(
					$object,
					$this->buildSharedUnboundProvider(
						$owners,
						$object_type,
						$data["class_name"],
						$data["include_path"]
					)
				);
			}
		}

		return $ret;
	}

	/**
	 * Get the object ids of the subtree starting at and including $ref_id with
	 * a mapping from $obj_id to $ref_id.
	 *
	 * @return  array   [int[], array<int,int>]
	 */
	protected function getSubtreeObjectIdsAndRefIdMapping(int $ref_id) : array
	{
		$sub_nodes_refs = $this->ilTree->getSubTreeIds($ref_id);
		$all_nodes_refs = array_merge([$ref_id], $sub_nodes_refs);
		$this->ilObjectDataCache->preloadReferenceCache($all_nodes_refs);

		$nodes_id_mapping = [];
		$nodes_ids = [];
		foreach ($all_nodes_refs as $ref_id) {
			$id = $this->ilObjectDataCache->lookupObjId($ref_id);
			$nodes_id_mapping[$id] = $ref_id;
			$nodes_ids[] = $id;
		}
		return [$nodes_ids, $nodes_id_mapping];
	}

	protected function getSeperatedUnboundProviderDataOf(
		array $node_ids,
		string $object_type,
		string $component_type = null
	) : iterable {
		$query = $this->buildSeparatedUnboundProviderQueryForObjects(
			$node_ids,
			$object_type,
			$component_type
		);
		$res = $this->ilDB->query($query);
		while ($row = $this->ilDB->fetchAssoc($res)) {
			yield [
				"id" => (int)$row["id"],
				"owner" => (int)$row["owner"],
				"class_name" => $row["class_name"],
				"include_path" => $row["include_path"]
			];
		}
	}

	protected function getSharedUnboundProviderDataOf(
		array $node_ids,
		string $object_type,
		string $component_type = null
	) : iterable {
		$to_int_list = function ($r) {
			return array_map(function($v) { return (int)$v; }, explode(",", $r));
		};
		$query = $this->buildSharedUnboundProviderQueryForObjects(
			$node_ids,
			$object_type,
			$component_type
		);
		$res = $this->ilDB->query($query);
		while ($row = $this->ilDB->fetchAssoc($res)) {
			yield [
				"owners" => $to_int_list($row["owners"]),
				"ids" => $to_int_list($row["ids"]),
				"class_name" => $row["class_name"],
				"include_path" => $row["include_path"]
			];
		}
	}

	protected function buildSeparatedUnboundProviderQueryForObjects(
		array $node_ids,
		string $object_type,
		string $component_type = null
	) : string {
		if ($component_type === null) {
			return
				"SELECT id, owner, class_name, include_path".PHP_EOL
				."FROM ".ilProviderDB::PROVIDER_TABLE.PHP_EOL
				."WHERE shared = 0".PHP_EOL
				."    AND ".$this->ilDB->in("owner", $node_ids, false, "integer").PHP_EOL
				."    AND object_type = ".$this->ilDB->quote($object_type,"string");
		}
		else {
			return
				"SELECT prv.id, prv.owner, prv.class_name, prv.include_path".PHP_EOL
				."FROM ".ilProviderDB::PROVIDER_TABLE." prv".PHP_EOL
				."JOIN ".ilProviderDB::COMPONENT_TABLE." cmp".PHP_EOL
				."    ON prv.id = cmp.id".PHP_EOL
				."WHERE shared = 0".PHP_EOL
				."    AND ".$this->ilDB->in("owner", $node_ids, false, "integer").PHP_EOL
				."    AND object_type = ".$this->ilDB->quote($object_type, "string").PHP_EOL
				."    AND component_type = ".$this->ilDB->quote($component_type, "string");
		}
	}

	protected function buildSharedUnboundProviderQueryForObjects(
		array $node_ids,
		string $object_type,
		string $component_type = null
	) : string {
		if ($component_type === null) {
			return
				"SELECT GROUP_CONCAT(id SEPARATOR \",\") ids,".PHP_EOL
				."GROUP_CONCAT(owner SEPARATOR \",\") owners, class_name, include_path".PHP_EOL
				."FROM ".ilProviderDB::PROVIDER_TABLE.PHP_EOL
				."WHERE shared = 1".PHP_EOL
				."    AND ".$this->ilDB->in("owner", $node_ids,false,"integer").PHP_EOL
				."    AND object_type = ".$this->ilDB->quote($object_type, "string").PHP_EOL
				."GROUP BY class_name, include_path";
		}
		else {
			return
				"SELECT GROUP_CONCAT(prv.id SEPARATOR \",\") ids,".PHP_EOL
				."GROUP_CONCAT(prv.owner SEPARATOR \",\") owners,".PHP_EOL
				."prv.class_name, prv.include_path".PHP_EOL
				."FROM ".ilProviderDB::PROVIDER_TABLE." prv".PHP_EOL
				."JOIN ".ilProviderDB::COMPONENT_TABLE." cmp".PHP_EOL
				."    ON prv.id = cmp.id".PHP_EOL
				."WHERE shared = 1".PHP_EOL
				."    AND ".$this->ilDB->in("owner", $node_ids,false,"integer").PHP_EOL
				."    AND object_type = ".$this->ilDB->quote($object_type, "string").PHP_EOL
				."    AND component_type = ".$this->ilDB->quote($component_type, "string").PHP_EOL
				."GROUP BY prv.class_name, prv.include_path";
		}
	}

	public function createTables() {
		if (!$this->ilDB->tableExists(ilProviderDB::PROVIDER_TABLE)) {
			$this->ilDB->createTable(
				ilProviderDB::PROVIDER_TABLE,
				[
					"id" => [
						"type" => "integer",
						"length" => 4,
						"notnull" => true
					],
					"owner" => [
						"type" => "integer",
						"length" => 4,
						"notnull" => true
					],
					"object_type" => [
						"type" => "text",
						"length" => 4,
						"notnull" => true
					],
					"class_name" => [
						"type" => "text",
						"length" => ilProviderDB::CLASS_NAME_LENGTH,
						"notnull" => true
					],
					"include_path" => [
						"type" => "text",
						"length" => ilProviderDB::PATH_LENGTH,
						"notnull" => true
					]
				]
			);
			$this->ilDB->addPrimaryKey(ilProviderDB::PROVIDER_TABLE, ["id"]);
			$this->ilDB->createSequence(ilProviderDB::PROVIDER_TABLE);
		}
		if (!$this->ilDB->tableExists(ilProviderDB::COMPONENT_TABLE)) {
			$this->ilDB->createTable(
				ilProviderDB::COMPONENT_TABLE,
				[
					"id" => [
						"type" => "integer",
						"length" => 4,
						"notnull" => true
					],
					"component_type" => [
						"type" => "text",
						"length" => ilProviderDB::CLASS_NAME_LENGTH,
						"notnull" => true
					]
				]
			);
			$this->ilDB->addPrimaryKey(
				ilProviderDB::COMPONENT_TABLE,
				["id", "component_type"]
			);
		}
		if (!$this->ilDB->tableColumnExists(
			ilProviderDB::PROVIDER_TABLE,
			"shared")
		) {
			$this->ilDB->addTableColumn(
				ilProviderDB::PROVIDER_TABLE,
				"shared",
				[
					"type" => "integer",
					"length" => 1,
					"notnull" => true,
					"default" => 0
				]
			);
			$this->ilDB->addIndex(
				ilProviderDB::PROVIDER_TABLE,
				["shared"],
				"ids"
			);
		}
	}

	public function addIndizes() {
		if (!$this->ilDB->tableExists(ilProviderDB::PROVIDER_TABLE)) {
			return;
		}
		$this->ilDB->addIndex(
			ilProviderDB::PROVIDER_TABLE,
			["owner"],
			"ido",
			false
		);
	}

	protected function buildSeparatedUnboundProvider(
		int $id,
		\ilObject $owner,
		string $object_type,
		string $class_name,
		string $include_path
	) : UnboundProvider {
		require_once($include_path);

		assert('class_exists($class_name)');
		if (!is_subclass_of($class_name, SeparatedUnboundProvider::class)) {
			throw new \UnexpectedValueException(
						"Class '$class_name' does not extend UnboundProvider."
			);
		}

		return new $class_name($id, $owner, $object_type);
	}

	protected function buildSharedUnboundProvider(
		array $owners,
		string $object_type,
		string $class_name,
		string $include_path
	) : UnboundProvider {
		assert('count($owners) > 0');

		require_once($include_path);

		assert('class_exists($class_name)');
		if (!is_subclass_of($class_name, SharedUnboundProvider::class)) {
			throw new \UnexpectedValueException(
						"Class '$class_name' does not extend UnboundProvider."
			);
		}

		return new $class_name($owners, $object_type);
	}

	/**
	 * Build an object by its reference id.
	 *
	 * @throws  \InvalidArgumentException if object could not be build
	 */
	protected function buildObjectByRefId(int $ref_id) : \ilObject
	{
		$obj = \ilObjectFactory::getInstanceByRefId($ref_id, false);
		if ($obj === false) {
			throw new \InvalidArgumentException("Cannot build object with obj_id='$ref_id'");
		}
		assert($obj instanceof \ilObject);
		return $obj;
	}

	/**
	 * Build an object by its object id.
	 *
	 * @throws  \InvalidArgumentException if object could not be build
	 */
	protected function buildObjectByObjId(int $obj_id) : \ilObject
	{
		$obj = \ilObjectFactory::getInstanceByObjId($obj_id, false);
		if ($obj === false) {
			throw new \InvalidArgumentException("Cannot build object with obj_id='$obj_id'");
		}
		assert($obj instanceof \ilObject);
		return $obj;
	}

	protected function getAllReferenceIdsFor(int $obj_id) : array
	{
		return \ilObject::_getAllReferences($obj_id);
	}
}
