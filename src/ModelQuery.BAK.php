<?php

declare(strict_types=1);

namespace Reflexive\Core;

class Query
{
	protected $query;

	protected $command;
	protected $columns = [];
	protected $quoteColumns = true;
	protected $tables = [];
	protected $conditions = [];
	protected $nextOperator;
	protected $parameters = [];
	protected $index = 0;
	protected $orders;
	protected $limit;
	protected $offset;

	protected function __construct(string $command)
	{
		$this->command = $command;
	}

	// abstract protected function bake(): void;
	protected function bake(): void
	{
		if(!empty($this->queryString))
			return;

		$this->queryString = $this->command.' ';
		$this->queryString.= $this->getColumnsString();
		$this->queryString.= $this->getFromString();
		$this->queryString.= $this->getWhereString();
		$this->queryString.= $this->getOrderString();
		$this->queryString.= $this->getLimitOffsetString();
	}


	// Builder
	public static function query(array|string|null $columns = null): static
	{
		return new static($columns);
	}

	public static function queryAll(array|string|null $columns = null): static
	{
		$object = new static('CREATE');
		$object->setColumns($columns);

		return $object;
	}

	public static function create(array|string|null $columns = null): static
	{
		$object = new static('UPDATE');
		$object->setColumns($columns);

		return $object;
	}

	public static function update(array|string|null $columns = null): static
		{
			$object = new static('UPDATE');
			$object->setColumns($columns);

			return $object;
		}

	public static function delete(): static
	{
		return new static();
	}

	public static function count(array|string|null $columns = null): static
	{
		return new static($columns);
	}

	// columns
	protected function setColumns(array|string|null $columns = null): void
	{
		if(is_array($columns)) {
			foreach($columns as $key => $column) {
				$column = trim($column);

				if(is_string($key))
					$this->columns[$key] = $column;
				else
					$this->columns[] = $column;
			}
		} elseif(!empty($columns)) {
			$this->columns[] = trim($columns);
		} else {
			$this->columns = [];
		}
	}
	protected function getColumnsString(): string
	{
		if(empty($this->columns))
			return '*';

		$str = '';
		foreach($this->columns as $key => $column) {
			if($this->quoteColumns && !str_starts_with($column, "\u{0060}"))
				$str.= "\u{0060}".$column."\u{0060}";
			else
				$str.= $column;

			if(is_string($key))
				$str.= ' '.$key;

			$str.= ', ';
		}

		return rtrim($str, ', ');
	}

	// from
	public function from(array|string|null $tables = null): static
	{
		$this->queryString = null;
		$this->nextOperator = null;

		if(is_array($tables)) {
			foreach($tables as $key => $table) {
				$table = trim($table);

				if(is_string($key))
					$this->tables[$key] = $table;
				else
					$this->tables[] = $table;
			}
		} elseif(!empty($tables)) {
			$this->tables[] = trim($tables);
		} else {
			$this->tables = [];
		}

		return $this;
	}
	protected function getFromString(): string
	{
		if(empty($this->tables))
			return '';

		$str = ' FROM ';
		foreach($this->tables as $key => $table) {
			if(!str_starts_with($table, "\u{0060}"))
				$str.= "\u{0060}".$table."\u{0060}";
			else
				$str.= $table;

			if(is_string($key))
				$str.= ' '.$key;

			$str.= ', ';
		}

		return rtrim($str, ', ');
	}

	// where
	public function where(string $name, string|Comparator $comparator, string|int|float|array $value = null): static
	{
		if(!empty($this->conditions) && empty($this->nextOperator))
			throw new \TypeError('Condition added to query chain without operator before last condition.');

		$this->queryString = null;

		if(!in_array($comparator, [
			'=',
			'<>',
			'>',
			'<',
			'>=',
			'<=',
			'IN',
			'BETWEEN',
			'LIKE',
			'IS NULL',
			'IS NOT NULL',
		]))
			throw new \InvalidArgumentException('Invalid comparator ('. $comparator. ')');

		$this->conditions[] = [
			'name' => trim($name),
			'comparator' => trim($comparator),
			'value' => $value,
			'operator' => $this->nextOperator,
		];

		return $this;
	}
	protected function getWhereString(): string
	{
		$this->parameters = [];
		$this->index = 0;

		if(empty($this->conditions))
			return '';

		$str = ' WHERE ';
		foreach($this->conditions as $condition) {
			if(is_array($condition['value'])) { // we have an array of value, probably for an IN condition or something
				$arrayString = '';
				foreach($condition['value'] as $value) {
					$this->parameters[$condition['name'].'_'.$this->index] = $value;
					$arrayString.= ':'.$condition['name'].'_'.$this->index++.',';
				}
				$str .= $condition['operator']." \u{0060}".str_replace("\u{0060}", "\u{0060}\u{0060}", $condition['name'])."\u{0060} ".$condition['comparator'].' ('.rtrim($arrayString, ',').') ';
			} else {
				$this->parameters[$condition['name'].'_'.$this->index] = $condition['value'];
				// $this->queryString.= '('.str_replace($condition['name'], $condition['name'].'_'.$this->index++, $sql).')';

				$str .= $condition['operator']." \u{0060}".str_replace("\u{0060}", "\u{0060}\u{0060}", $condition['name'])."\u{0060} ".$condition['comparator'].' :'.$condition['name'].'_'.$this->index++.' ';
			}
		}

		return $str;
	}

	public function and(...$where): static
	{
		$this->nextOperator = self::AND;

		if(!empty($where))
			$this->where(...$where);

		return $this;
	}

	public function or(...$where): static
	{
		$this->nextOperator = self::OR;

		if(!empty($where))
			$this->where(...$where);

		return $this;
	}

	// order by
	public function order(string $column, string $direction = 'asc'): static
	{
		$this->queryString = null;
		$this->nextOperator = null;

		$this->orders[] = [
			'column' => trim(htmlspecialchars(htmlentities(strip_tags(addcslashes($column, '%_')), ENT_NOQUOTES, 'UTF-8'))),
			'direction' => ('desc' == strtolower($direction)) ? ' DESC' : ' ASC',
		];

		return $this;
	}
	protected function getOrderString(): string
	{
		if(empty($this->orders))
			return '';

		$str = 'ORDER BY ';
		foreach($this->orders as $order) {
			$str.= "\u{0060}".$order['column']."\u{0060} ".$order['direction'].', ';
		}

		return rtrim($str, ', '). ' ';
	}

	// limit
	public function limit(int $limit = null): static
	{
		$this->queryString = null;
		$this->nextOperator = null;

		if($limit < 0)
			throw new \TypeError('Invalid limit ('. $limit. '). Cannot be negative.');

		$this->limit = $limit;

		return $this;
	}
	public function offset(int $offset = null): static
	{
		$this->queryString = null;

		if($offset < 0)
			throw new \TypeError('Invalid offset ('. $offset. '). Cannot be negative.');

		$this->offset = $offset;

		return $this;
	}
	protected function getLimitOffsetString(): string
	{
		if(empty($this->limit) && empty($this->offset))
			return '';

		$str = 'LIMIT '. $this->limit .' ';
		if(!empty($this->offset))
			$this->queryString.= 'OFFSET '. $this->offset .' ';

		return $str;
	}

	public function __toString(): string
	{
		$this->bake();

		return $this->queryString;
	}


	public static function format(?\PDOStatement $statement): string
	{
		if(null === $statement || $statement->rowCount() <= 0)
			return 'No result';

		$count = $statement->columnCount();
		$str = '';

		// Get column headers
		$str.= '<table><thead><tr>';
		for ($i = 0; $i < $count; $i++){
			$meta = $statement->getColumnMeta($i)["name"];
			$str.= '<th>' . $meta . '</th>';
		}
		$str.= '</tr></thead><tbody>';

		// Get row data
		while ($row = $statement->fetch(\PDO::FETCH_ASSOC)) {
			$str.= '<tr>';
			for ($i = 0; $i < $count; $i++){
				$meta = $statement->getColumnMeta($i)["name"];
				$str.= '<td>' . $row[$meta] . '</td>';
			}
			$str.= '</tr>';
		}

		$str.= '</tbody></table>';

		return $str;
	}
}
