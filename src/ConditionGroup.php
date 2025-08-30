<?php

declare(strict_types=1);

namespace Reflexive\Core;

abstract class ConditionGroup
{
	protected array $conditions = [];
	protected ?Operator $nextOperator = null;

	// protected array $parameters = [];
	protected(set) string $name = 'cGroup';

	protected function __construct(
		?Condition $firstCondition = null
	) {
		if($firstCondition !== null)
			$this->where($firstCondition);
	}

	// where
	public function where(Condition|ConditionGroup $condition): static
	{
		if(!empty($this->conditions) && empty($this->nextOperator))
			throw new \TypeError('Condition added to query chain without operator before previous condition.');

		$this->conditions[$condition->name.'_'.count($this->conditions)] = [
			'condition' => $condition,
			'operator' => $this->nextOperator,
		];

		$this->nextOperator = null;

		return $this;
	}

	public function getConditions(): array
	{
		return $this->conditions;
	}

	public function count(): int
	{
		return count($this->conditions);
	}

	public function and(Condition|ConditionGroup|null $condition = null): static
	{
		if(!empty($this->conditions))
			$this->nextOperator = Operator::AND;

		if(!empty($condition))
			$this->where($condition);

		return $this;
	}

	public function or(Condition|ConditionGroup|null $condition = null): static
	{
		if(!empty($this->conditions))
			$this->nextOperator = Operator::OR;

		if(!empty($condition))
			$this->where($condition);

		return $this;
	}

	public function hasConditions(): bool
	{
		return !empty($this->conditions);
	}
}
