<?php

declare(strict_types=1);

namespace Reflexive\Core;

class Condition
{
	public function __construct(
		public string $columnName,
		public Comparator $comparator,
		public string|int|float|array|null $value,
		// public ?Operator $operator = null,
	) {}

	public static function EQUAL(string $columnName, string|int|float|array|null $value): self
	{
		return new self($columnName, Comparator::EQUAL, $value);
	}
	public static function NOTEQUAL(string $columnName, string|int|float|array|null $value)
	{
		return new self($columnName, Comparator::NOTEQUAL, $value);
	}
	public static function GREATER(string $columnName, string|int|float|array|null $value)
	{
		return new self($columnName, Comparator::GREATER, $value);
	}
	public static function LESS(string $columnName, string|int|float|array|null $value)
	{
		return new self($columnName, Comparator::LESS, $value);
	}
	public static function GREATEROREQUAL(string $columnName, string|int|float|array|null $value)
	{
		return new self($columnName, Comparator::GREATEROREQUAL, $value);
	}
	public static function LESSOREQUAL(string $columnName, string|int|float|array|null $value)
	{
		return new self($columnName, Comparator::LESSOREQUAL, $value);
	}
	public static function IN(string $columnName, string|int|float|array|null $value)
	{
		return new self($columnName, Comparator::IN, $value);
	}
	public static function BETWEEN(string $columnName, string|int|float|array|null $value)
	{
		return new self($columnName, Comparator::BETWEEN, $value);
	}
	public static function LIKE(string $columnName, string|int|float|array|null $value)
	{
		return new self($columnName, Comparator::LIKE, $value);
	}
	public static function NULL(string $columnName, string|int|float|array|null $value)
	{
		return new self($columnName, Comparator::NULL, $value);
	}
	public static function NOTNULL(string $columnName, string|int|float|array|null $value)
	{
		return new self($columnName, Comparator::NOTNULL, $value);
	}

	public function bake(int &$index, bool $quoteNames = true, ?Operator $operator = null, int &$prettify = 0): array
	{
		// $queryString = ($prettify > 0 ? str_repeat("\t", $prettify) : '');
		$queryString = $operator !== null ? $operator->value.($prettify > 0 ? PHP_EOL.str_repeat("\t", $prettify) : '') : '';
		$queryString.= $quoteNames ? Strings::quote($this->columnName) : $this->columnName;
		$queryString.= ' '.$this->comparator?->value;
		$parameters = [];

		$key = lcfirst(str_replace('.', '', $this->columnName));

		if(is_array($this->value)) { // we have an array of value, probably for an IN condition or something
			$subIndex = 0;
			$subString = '';

			foreach($this->value as $value) {
				$parameters[$key.'_'.$index.'_'.$subIndex] = $value;
				$subString.= ':'.$key.'_'.$index.'_'.$subIndex++.', ';
			}
			$queryString.= ' ('.($prettify ? PHP_EOL.str_repeat("\t", $prettify+1) : ''). rtrim($subString, ', ') .($prettify > 0 ? PHP_EOL.str_repeat("\t", $prettify) : '').') ';
		} elseif($this->comparator != Comparator::NULL && $this->comparator != Comparator::NOTNULL) { // we have a simple value
			$parameters[$key.'_'.$index] = $this->value;
			$queryString.= ' :'.$key.'_'.$index;
		}

		return [
			'queryString' => $queryString .($prettify > 0 ? PHP_EOL : ''),
			'parameters' => $parameters,
		];
	}
}
