<?php

namespace Reflexive\Core;

class SelectQuery extends Query
{
	private $select = '*';

	protected function __construct(string $columns = '*')
	{
		parrent::__construct();

		$this->select = trim($columns);
	}

	public function in(string $tableName): static
	{
		$this->setTableName($tableName);

		return $this;
	}

	public function where(string $condition): static
	{
		$this->where = $condition;

		return $this;
	}


	public function __toString(): ?string
	{
		return 'SELECT '. $this->select .' IN '. $this->tableName .' WHERE '. $this->where .'; ';
	}
}
