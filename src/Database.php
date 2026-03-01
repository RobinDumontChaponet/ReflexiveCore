<?php

namespace Reflexive\Core;

use PDO, PDOStatement;

// Lazy PDO wrapper (and optional singleton register thingy for those who want to use one…)

/** @psalm-api */
class Database extends PDO
{
	private static array $PDOInstances = [];
	// private static $databases = [];
	private static array $defaultOptions = [
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
		PDO::ATTR_EMULATE_PREPARES => false,
		PDO::ATTR_PERSISTENT => false,
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
	];

	private bool $connected = false;

	public function __construct(
		public string $dsn,
		private ?string $username = null,
		private ?string $password = null,
		private array $options = [],
	)
	{
		if(defined('\Pdo\Mysql::ATTR_USE_BUFFERED_QUERY'))
			self::$defaultOptions[\Pdo\Mysql::ATTR_USE_BUFFERED_QUERY] = true;
	}

	public function getDSNPrefix(): ?string
	{
		return preg_match('/(\w+):/m', $this->dsn, $matches)? $matches[1] : null;
	}

	private function _connect(): void
	{
		if(!$this->connected) {
			parent::__construct(
				$this->dsn,
				$this->username,
				$this->password,
				array_replace(self::$defaultOptions, $this->options)
			);
			$this->connected = true;
		}
	}

	public static function once(
		string $dsn,
		?string $username = null,
		?string $password = null,
		array $options = [],
	): ?static
	{
		if(!isset(self::$PDOInstances[$dsn])) {
			self::$PDOInstances[$dsn] = new self(
				$dsn,
				$username,
				$password,
				$options,
			);
		}

		return self::$PDOInstances[$dsn];
	}

	public function __sleep(): array
	{
		return [
			'dsn',
			'user',
			'password',
			'options',
		];
	}

	public function __debugInfo() {
		return [
			'dsn' => $this->dsn,
			'user' => $this->username,
			'options' => $this->options,
		];
	}

	#[\Override]
	public function beginTransaction(): bool
	{
		$this->_connect();
		return parent::beginTransaction();
	}

	#[\Override]
	public function commit(): bool
	{
		$this->_connect();
		return parent::commit();
	}

	#[\Override]
	public function errorCode(): ?string
	{
		$this->_connect();
		return parent::errorCode();
	}

	/** @psalm-suppress LessSpecificImplementedReturnType */
	#[\Override]
	public function errorInfo(): array
	{
		$this->_connect();
		return parent::errorInfo();
	}

	#[\Override]
	public function exec(string $statement): int
	{
		$this->_connect();
		return parent::exec($statement);
	}

	#[\Override]
	public function getAttribute(int $attribute): mixed
	{
		$this->_connect();
		return parent::getAttribute($attribute);
	}
	// public static function getAvailableDrivers(): array
	// {
	// 	return self::getAvailableDrivers();
	// }

	#[\Override]
	public function inTransaction(): bool
	{
		$this->_connect();
		return parent::inTransaction();
	}

	#[\Override]
	public function lastInsertId(?string $name = null): string
	{
		$this->_connect();
		return parent::lastInsertId($name);
	}

	#[\Override]
	public function prepare(string $query, array $options = []): PDOStatement|false
	{
		$this->_connect();
		return parent::prepare($query, $options);
	}

	#[\Override]
	public function query(string $query, ?int $fetchMode = null, mixed ...$fetchModeArgs): PDOStatement|false
	{
		$this->_connect();
		return parent::query($query, $fetchMode, ...$fetchModeArgs);
	}

	#[\Override]
	public function quote(string $string, int $type = PDO::PARAM_STR): string
	{
		$this->_connect();
		return parent::quote($string, $type);
	}

	#[\Override]
	public function rollBack(): bool
	{
		$this->_connect();
		return parent::rollBack();
	}

	#[\Override]
	public function setAttribute(int $attribute, mixed $value): bool
	{
		$this->_connect();
		return parent::setAttribute($attribute, $value);
	}
}
