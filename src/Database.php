<?php

namespace Reflexive\Core;

use PDO, PDOStatement;

// Lazy PDO wrapper (and optional singleton register thingy for those who want to use one…)

class Database extends PDO
{
    private static array $PDOInstances = [];
    // private static $databases = [];
	private static array $defaultOptions = [
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
		PDO::ATTR_EMULATE_PREPARES => false,
		PDO::ATTR_PERSISTENT => false,
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
	];

	private bool $connected = false;

    public function __construct(
		private string $dsn,
		private ?string $username = null,
		private ?string $password = null,
		private array $options = [],
	)
	{}

	private function connect(): void
	{
		if (!$this->connected) {
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
		string $username = null,
		string $password = null,
		array $options = [],
	): ?static
	{
        if (!isset(self::$PDOInstances[$dsn])) {
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

	public function beginTransaction(): bool
	{
		$this->connect();
		return parent::beginTransaction();
	}
	public function commit(): bool
	{
		$this->connect();
		return parent::commit();
	}
	public function errorCode(): ?string
	{
		$this->connect();
		return parent::errorCode();
	}

	/** @psalm-suppress LessSpecificImplementedReturnType */
	public function errorInfo(): array
	{
		$this->connect();
		return parent::errorInfo();
	}
	public function exec(string $statement): int
	{
		$this->connect();
		return parent::exec($statement);
	}
	public function getAttribute(int $attribute): mixed
	{
		$this->connect();
		return parent::getAttribute($attribute);
	}
	// public static function getAvailableDrivers(): array
	// {
	// 	return self::getAvailableDrivers();
	// }
	public function inTransaction(): bool
	{
		$this->connect();
		return parent::inTransaction();
	}
	public function lastInsertId(string $name = null): string
	{
		$this->connect();
		return parent::lastInsertId($name);
	}
	public function prepare(string $statement, array $driver_options = []): PDOStatement|false
	{
		$this->connect();
		return parent::prepare($statement, $driver_options);
	}

	/** @psalm-suppress PossiblyNullArgument */
	public function query(string $query, ?int $fetchMode = null, mixed ...$fetchModeArgs): PDOStatement|false
	{
		$this->connect();
		return parent::query($query, $fetchMode, ...$fetchModeArgs);
	}
	public function quote(string $string, int $parameter_type = PDO::PARAM_STR): string
	{
		$this->connect();
		return parent::quote($string, $parameter_type);
	}
	public function rollBack(): bool
	{
		$this->connect();
		return parent::rollBack();
	}
	public function setAttribute(int $attribute, mixed $value): bool
	{
		$this->connect();
		return parent::setAttribute($attribute, $value);
	}
}
