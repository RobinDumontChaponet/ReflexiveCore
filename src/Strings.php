<?php

declare(strict_types=1);

namespace Reflexive\Core;

abstract class Strings
{
	// do not quote sql keywords
	// I don't like repeating sql there, but have no other idea
	private const SQL_KEYWORDS = [
		'AND' => true,
		'AS' => true,
		'ASC' => true,
		'BETWEEN' => true,
		'BY' => true,
		'DESC' => true,
		'DISTINCT' => true,
		'FROM' => true,
		'GROUP' => true,
		'HAVING' => true,
		'IN' => true,
		'IS' => true,
		'JOIN' => true,
		'LEFT' => true,
		'LIKE' => true,
		'LIMIT' => true,
		'NOT' => true,
		'NULL' => true,
		'OFFSET' => true,
		'ON' => true,
		'OR' => true,
		'ORDER' => true,
		'OUTER' => true,
		'RIGHT' => true,
		'SELECT' => true,
		'WHERE' => true,
	];

	public static function quote(string $string): string
	{
		return preg_replace_callback(
			'/`[^`]*`|\'(?:\'\'|[^\'])*\'|"(?:\"\"|[^"])*"|\\b([a-z_][a-z0-9_]*)\\b(?!\\s*\\()/i',
			static function(array $matches): string {
				if(!isset($matches[1]) || isset(self::SQL_KEYWORDS[strtoupper($matches[1])]))
					return $matches[0];

				return '`'.$matches[1].'`';
			},
			$string
		) ?? $string;
	}
}
