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
	{}

	public function getDSNPrefix(): ?string
	{
		return preg_match('/^([a-z][a-z0-9_]*):/i', $this->dsn, $matches)? strtolower($matches[1]) : null;
	}

	private function _connect(): void
	{
		if(!$this->connected) {
			$options = array_replace(self::$defaultOptions, $this->options);

			if(
				$this->getDSNPrefix() === 'mysql'
				&& defined('\Pdo\Mysql::ATTR_USE_BUFFERED_QUERY')
				&& !array_key_exists(\Pdo\Mysql::ATTR_USE_BUFFERED_QUERY, $options)
			)
				$options[\Pdo\Mysql::ATTR_USE_BUFFERED_QUERY] = true;

			parent::__construct(
				$this->dsn,
				$this->username,
				$this->password,
				$options
			);
			$this->connected = true;
		}
	}

	public static function once(
		string $dsn,
		?string $username = null,
		?string $password = null,
		array $options = [],
	): static
	{
		$key = static::class."\0".$dsn; // forgot about subclassing…

		if(!isset(self::$PDOInstances[$key])) {
			self::$PDOInstances[$key] = new static(
				$dsn,
				$username,
				$password,
				$options,
			);
		}

		return self::$PDOInstances[$key];
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
	public function exec(string $statement): int|false
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
	public function quote(string $string, int $type = PDO::PARAM_STR): string|false
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
