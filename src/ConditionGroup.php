<?php

declare(strict_types=1);

namespace Reflexive\Core;

class ConditionGroup
{
	protected array $conditions = [];
	protected ?Operator $nextOperator = null;

	protected array $parameters = [];
	private string $columnName = 'cGroup';

	public function __construct(
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

		$this->conditions[$condition->columnName.'_'.count($this->conditions)] = [
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

	public function bake(int &$index, bool $quoteNames = true, ?Operator $operator = null, int &$prettify = -1): array
	{
		$queryString = '';
		$parameters = [];
		if($prettify > -1 && count($this->conditions) > 1) {
			$prettify++;
		}

		foreach($this->conditions as $conditionArray) {
			$baked = $conditionArray['condition']->bake($index, $quoteNames, $conditionArray['operator'], $prettify);

			$queryString.= ($prettify > 0 ? str_repeat("\t", $prettify) : '').$baked['queryString'];
			$parameters += $baked['parameters'];

			$index++;
		}

		if(count($this->conditions) > 1) {
			$queryString = '('.($prettify > 0 ? PHP_EOL : ''). rtrim($queryString, PHP_EOL) .($prettify > 0 ? PHP_EOL.str_repeat("\t", --$prettify) : '').')';
		}

		return [
			'queryString' => $operator?->value.$queryString,
			'parameters' => $parameters,
		];
	}
}
