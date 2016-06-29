<?php

	/**
	 * NfinDB "NfinityDB" a schemaless datastore that uses sqlite as a storage,
	 * this datastore has been created to serve some technical concepts at nfinity.space .
	 *
	 * @license		MIT License
	 * @author 		Nfinity {Backend Team}
	 * @version 	v1.0
	 */
	class NfinDB {
		/**
		 * @var PDO
		 * @ignore
		 */
		private $db;

		/**
		 * @var string
		 * @ignore
		 */
		private $datatb;

		/**
		 * Constructor
		 *
		 * @param 	string 	$filename 	the database filename to use 	
		 * @param 	string 	$name 		the name of the table to use/build
		 */
		public function __construct($filename, $name) {
			$this->datatb = $name;
			try {
				$this->db = new PDO("sqlite:{$filename}");
			} catch ( Exception $e ) {
				throw new Exception($e->getMessage());
			}
			$this->db->exec("CREATE TABLE IF NOT EXISTS {$this->datatb} (
				_namespace	CHAR(255),
				_type 		CHAR(255),
				_key		CHAR(255),
				_value		TEXT,
				_created	INTEGER
			)");
			$this->db->exec("CREATE INDEX IF NOT EXISTS ns ON {$this->datatb} _namespace");
			$this->db->exec("CREATE INDEX IF NOT EXISTS ns_type_created ON {$this->datatb} (_namespace, _type, _created)");
			$this->db->exec("CREATE UNIQUE INDEX IF NOT EXISTS ns_type_key ON {$this->datatb}(_namespace, _type, _key)");
		}

		/**
		 * Get an array of registered namespaces
		 *
		 * @return 	array
		 */
		public function getNamespaces() {
			$stmnt = $this->db->query("SELECT DISTINCT _namespace FROM {$this->datatb}");
			return ! $stmnt ? [] : $stmnt->fetchAll(PDO::FETCH_COLUMN);
		}

		/**
		 * Drop the specified namespace
		 *
		 * @param 	string 	$ns   the namespace to drop
		 *
		 * @return 	array
		 */
		public function dropNamespace($ns) {
			$stmnt = $this->db->prepare("DELETE FROM {$this->datatb} WHERE _namespace = ?");
			$ok = $stmnt && $stmnt->execute([$ns]);
			return $ok ? $stmnt->rowCount() > 0 : false;
		}

		/**
		 * Get an array of registered types of the specified namespace
		 *
		 * @param   string 	$ns 	the namespace to fetch its types
		 *
		 * @return 	array
		 */
		public function getTypes($ns) {
			$stmnt = $this->db->prepare("SELECT DISTINCT _type FROM {$this->datatb} WHERE _namespace = ?");
			$ok = $stmnt && $stmnt->execute([$ns]);
			return ! $ok ? [] : $stmnt->fetchAll(PDO::FETCH_COLUMN);
		}

		/**
		 * Drop the specified namespace from the specified namespace
		 *
		 * @param 	string 	$ns   the namespace of the type
		 * @param 	string 	$ns   the type to drop
		 *
		 * @return 	bool
		 */
		public function dropType($ns, $type) {
			$stmnt = $this->db->prepare("DELETE FROM {$this->datatb} WHERE _namespace = ? AND _type = ?");
			$ok = $stmnt && $stmnt->execute([$ns, $type]);
			return $ok ? $stmnt->rowCount() > 0 : false;
		}

		/**
		 * Get an array of registered items of the specified namespace & type
		 *
		 * @param   string 	$ns 	the namespace
		 * @param   string 	$type 	the type
		 * @param   integer $from 	the offset
		 * @param   integer $to 	the length
		 * @param   bool 	$asc 	whether to return the result in ASC order or not "DESC"
		 *
		 * @return 	array
		 */
		public function getItems($ns, $type, $from = 0, $limit = 10, $asc = true) {
			$order = $asc ? "ASC" : "DESC";
			$stmnt = $this->db->prepare("SELECT _key, _value FROM {$this->datatb} WHERE _namespace = ? AND _type = ? ORDER BY _created {$order} LIMIT {$from},{$limit}");
			$ok = $stmnt && $stmnt->execute([$ns, $type]);
			return ! $ok ? [] : call_user_func(function() use($stmnt) {
				$array = $stmnt->fetchAll(PDO::FETCH_KEY_PAIR);
				foreach ( $array as &$v ) {
					$v = json_decode($v, true);
				}
				return $array;
			});
		}

		/**
		 * Register an item into the specified namespace->type
		 *
		 * @param   string 	$ns 	the namespace
		 * @param   string 	$type 	the type
		 * @param   bool 	$data 	the data to store
		 *
		 * @return 	bool
		 */
		public function putItem($ns, $type, $key, array $data) {
			$stmnt = $this->db->prepare("REPLACE INTO {$this->datatb} (_namespace, _type, _key, _value, _created) VALUES(?, ?, ?, ?, ?)");
			$ok = $stmnt && $stmnt->execute([$ns, $type, $key, json_encode($data), time()]);
			return $ok;
		}

		/**
		 * Get an item from the datastore using its namespace->type->key
		 *
		 * @param   string 	$ns 	the namespace
		 * @param   string 	$type 	the type
		 * @param   string 	$key 	the key
		 * @param   bool 	$array 	whether to return the item as array "default" or object
		 *
		 * @return 	array
		 */
		public function getItem($ns, $type, $key, $array = true) {
			$stmnt = $this->db->prepare("SELECT _value FROM {$this->datatb} WHERE _namespace = ? AND _type = ? AND _key = ? LIMIT 1");
			$ok = $stmnt && $stmnt->execute([$ns, $type, $key]);
			return $ok ? json_decode($stmnt->fetch(PDO::FETCH_COLUMN), $array) : [];
		}

		/**
		 * Drop an item from the datastore using its namespace->type->key
		 *
		 * @param   string 	$ns 	the namespace
		 * @param   string 	$type 	the type
		 * @param   string 	$key 	the key
		 *
		 * @return 	bool
		 */
		public function dropItem($ns, $type, $key) {
			$stmnt = $this->db->prepare("DELETE FROM {$this->datatb} WHERE _namespace = ? AND _type = ? AND _key = ?");
			$ok = $stmnt && $stmnt->execute([$ns, $type, $key]);
			return $ok ? $stmnt->rowCount() > 0 : false;
		}
	}
