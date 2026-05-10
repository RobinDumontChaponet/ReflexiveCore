<?php

declare(strict_types=1);

namespace Reflexive\Core\Tests;

use PHPUnit\Framework\TestCase;
use Reflexive\Core\Comparator;
use Reflexive\Core\Condition;
use Reflexive\Core\ConditionGroup;
use Reflexive\Core\Operator;
use TypeError;

final class ConditionGroupTest extends TestCase
{
	// Verifies chained conditions retain their conditions and operators.
	public function testStoresConditionsWithExplicitOperators(): void
	{
		$first = TestGroupCondition::EQUAL('status', 'active');
		$second = TestGroupCondition::GREATER('age', 18);
		$third = TestGroupCondition::NULL('deleted_at');

		$group = (new TestConditionGroup($first))
			->and($second)
			->or()
			->where($third);

		$conditions = array_values($group->getConditions());

		self::assertSame(3, $group->count());
		self::assertTrue($group->hasConditions());
		self::assertSame($first, $conditions[0]['condition']);
		self::assertNull($conditions[0]['operator']);
		self::assertSame($second, $conditions[1]['condition']);
		self::assertSame(Operator::AND, $conditions[1]['operator']);
		self::assertSame($third, $conditions[2]['condition']);
		self::assertSame(Operator::OR, $conditions[2]['operator']);
	}

	// Verifies a condition cannot follow another without and/or.
	public function testRejectsAdjacentConditionsWithoutAnOperator(): void
	{
		$group = new TestConditionGroup(TestGroupCondition::EQUAL('status', 'active'));

		$this->expectException(TypeError::class);
		$this->expectExceptionMessage('Condition added to query chain without operator before previous condition.');

		$group->where(TestGroupCondition::GREATER('age', 18));
	}

	// Verifies condition groups can be nested as conditions.
	public function testCanContainNestedGroups(): void
	{
		$nested = (new TestConditionGroup(TestGroupCondition::EQUAL('role', 'admin')))
			->or(TestGroupCondition::EQUAL('role', 'owner'));

		$group = (new TestConditionGroup(TestGroupCondition::EQUAL('active', true)))
			->and($nested);

		$conditions = array_values($group->getConditions());

		self::assertSame($nested, $conditions[1]['condition']);
		self::assertSame(Operator::AND, $conditions[1]['operator']);
	}
}

final class TestGroupCondition extends Condition
{
	public function __construct(string $name, Comparator $comparator, mixed $value)
	{
		parent::__construct($name, $comparator, $value);
	}
}

final class TestConditionGroup extends ConditionGroup
{
	public function __construct(?Condition $firstCondition = null)
	{
		parent::__construct($firstCondition);
	}
}
