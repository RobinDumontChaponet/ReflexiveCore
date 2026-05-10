<?php

declare(strict_types=1);

namespace Reflexive\Core\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Reflexive\Core\Strings;

final class StringsTest extends TestCase
{
	/**
	 * @return iterable<string, array{string, string}>
	 */
	public static function quoteProvider(): iterable
	{
		yield 'qualified identifier' => ['users.email', '`users`.`email`'];
		yield 'sql function' => ['COUNT(*)', 'COUNT(*)'];
		yield 'function argument' => ['COUNT(users.id)', 'COUNT(`users`.`id`)'];
		yield 'alias' => ['users.email AS email', '`users`.`email` AS `email`'];
		yield 'already quoted' => ['`already_quoted`', '`already_quoted`'];
		yield 'comma separated identifiers' => ['first_name, last_name', '`first_name`, `last_name`'];
		yield 'string literal' => ["status = 'active'", "`status` = 'active'"];
	}

	// Verifies identifier quoting while preserving SQL functions.
	#[DataProvider('quoteProvider')]
	public function testQuoteWrapsBareIdentifiersAndLeavesFunctionsAlone(string $input, string $expected): void
	{
		self::assertSame($expected, Strings::quote($input));
	}
}
