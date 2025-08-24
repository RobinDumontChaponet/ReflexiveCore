<?php

declare(strict_types=1);

namespace Reflexive\Core;

abstract class Strings {
	public static function quote(string $string): string
	{
		return preg_replace('/\b((?<!`)[^\s()`\.]+(?![\(`]))\b/i', '`$1`', $string);
	}
}
