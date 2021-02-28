<?php

namespace Reflexive\Core;

class Query
{
	public $pdo;

	private $tableName = '';
	private $where = '';
	private $parameters = array();
	private $limit = '';
	private $offset = '';
	private $orderBy = '';

	private $index = 0;

	public static $debug = false;
	public static $dump = [];

	protected function __construct()
	{
	}

	public static function search(string $columns = null): static
	{
		return new SelectQuery($columns);
	}

	public function createIn(string $tableName): static
	{
		$this->tableName = $tableName;

		return $this;
	}

	public function readIn(string $tableName): static
	{
		$this->tableName = $tableName;

		return $this;
	}

	public function updateIn(string $tableName): static
	{
		$this->tableName = $tableName;

		return $this;
	}

	public function deleteIn(string $tableName): static
	{
		$this->tableName = $tableName;

		return $this;
	}

	public function where(string $where): static
	{
		$this->where = $where;

		return $this;
	}


	private function setTableName(string $tableName): void
	{
		$this->tableName = trim($tableName);
	}
}
