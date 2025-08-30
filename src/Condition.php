<?php

declare(strict_types=1);

namespace Reflexive\Core;

abstract class Condition
{
	protected(set) string $name;
	protected(set) Comparator $comparator;
	protected(set) mixed $value;

	protected function __construct(
		string $name,
		Comparator $comparator,
	) {
		$this->name = $name;
		$this->comparator = $comparator;
	}

	public static function EQUAL(string $name, mixed $value): static
	{
		return new static($name, Comparator::EQUAL, $value);
	}
	public static function NOTEQUAL(string $name, mixed $value): static
	{
		return new static($name, Comparator::NOTEQUAL, $value);
	}
	public static function GREATER(string $name, mixed $value): static
	{
		return new static($name, Comparator::GREATER, $value);
	}
	public static function LESS(string $name, mixed $value): static
	{
		return new static($name, Comparator::LESS, $value);
	}
	public static function GREATEROREQUAL(string $name, mixed $value): static
	{
		return new static($name, Comparator::GREATEROREQUAL, $value);
	}
	public static function LESSOREQUAL(string $name, mixed $value): static
	{
		return new static($name, Comparator::LESSOREQUAL, $value);
	}
	public static function IN(string $name, mixed $value): static
	{
		return new static($name, Comparator::IN, $value);
	}
	public static function BETWEEN(string $name, mixed $value): static
	{
		return new static($name, Comparator::BETWEEN, $value);
	}
	public static function LIKE(string $name, mixed $value): static
	{
		return new static($name, Comparator::LIKE, $value);
	}
	public static function NULL(string $name): static
	{
		return new static($name, Comparator::NULL, null);
	}
	public static function NOTNULL(string $name): static
	{
		return new static($name, Comparator::NOTNULL, null);
	}
}
