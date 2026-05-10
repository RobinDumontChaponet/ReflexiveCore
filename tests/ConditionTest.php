<?php

declare(strict_types=1);

namespace Reflexive\Core\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Reflexive\Core\Comparator;
use Reflexive\Core\Condition;

final class ConditionTest extends TestCase
{
	/**
	 * @return iterable<string, array{TestCondition, string, Comparator, mixed}>
	 */
	public static function factoryProvider(): iterable
	{
		yield 'equal' => [TestCondition::EQUAL('age', 40), 'age', Comparator::EQUAL, 40];
		yield 'not equal' => [TestCondition::NOTEQUAL('status', 'deleted'), 'status', Comparator::NOTEQUAL, 'deleted'];
		yield 'greater' => [TestCondition::GREATER('score', 10), 'score', Comparator::GREATER, 10];
		yield 'less' => [TestCondition::LESS('score', 20), 'score', Comparator::LESS, 20];
		yield 'greater or equal' => [TestCondition::GREATEROREQUAL('score', 10), 'score', Comparator::GREATEROREQUAL, 10];
		yield 'less or equal' => [TestCondition::LESSOREQUAL('score', 20), 'score', Comparator::LESSOREQUAL, 20];
		yield 'in' => [TestCondition::IN('id', [1, 2, 3]), 'id', Comparator::IN, [1, 2, 3]];
		yield 'between' => [TestCondition::BETWEEN('created_at', ['2026-01-01', '2026-01-31']), 'created_at', Comparator::BETWEEN, ['2026-01-01', '2026-01-31']];
		yield 'like' => [TestCondition::LIKE('email', '%@example.com'), 'email', Comparator::LIKE, '%@example.com'];
		yield 'null' => [TestCondition::NULL('deleted_at'), 'deleted_at', Comparator::NULL, null];
		yield 'not null' => [TestCondition::NOTNULL('published_at'), 'published_at', Comparator::NOTNULL, null];
	}

	// Verifies each factory creates a condition with the expected public state.
	#[DataProvider('factoryProvider')]
	public function testFactoriesPreserveNameComparatorAndValue(
		TestCondition $condition,
		string $name,
		Comparator $comparator,
		mixed $value
	): void {
		self::assertSame($name, $condition->name);
		self::assertSame($comparator, $condition->comparator);
		self::assertSame($value, $condition->value);
	}
}

final class TestCondition extends Condition
{
	public function __construct(string $name, Comparator $comparator, mixed $value)
	{
		parent::__construct($name, $comparator, $value);
	}
}
