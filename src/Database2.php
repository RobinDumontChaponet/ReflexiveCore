<?php

namespace Reflexive\Core;

use PDO;
use Exception;
use PDOException;

// Lazy PDO wrapper (and optional singleton register thingy for those who want to use one…)

class Database extends PDO
{
    private static $PDOInstances = array();
    private static $databases = array();
	private static $defaultOptions = [
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
		PDO::ATTR_EMULATE_PREPARES => false,
		PDO::ATTR_PERSISTENT => false,
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
	];

	private $connected = false;

    public function __construct(
		private string $dsn,
		private ?string $user = null,
		private ?string $password = null,
		private array $options = [],
	)
	{
		$class = new \ReflectionClass(self::class);
		$methods = $class->getMethods(\ReflectionMethod::IS_PUBLIC);
		$properties = $class->getProperties(\ReflectionProperty::IS_PUBLIC);


		foreach($methods as $method) {
			if(in_array($method->name, ['__construct']))
				continue;

			$method->setAccessible(false);
			var_dump($method);
			echo '<br />';
		}

		echo '<br />';

		foreach($properties as $property) {
			// if(in_array($property->name, ['__construct']))
			// 	continue;

			// $property->setAccessible(false);
			var_dump($property);
			echo '<br />';
		}

		echo '<br />';

		$class = new \ReflectionClass(self::class);
		$methods = $class->getMethods(\ReflectionMethod::IS_PUBLIC);

		foreach($methods as $method) {
			if(in_array($method->name, ['__construct']))
				continue;

			var_dump($method);
			echo '<br />';
		}
	}

	public function __call ($function, $args)
	{
		var_dump('A');
		$this->connect();

		// invoke the original method
		return call_user_func_array(array($this, $function), $args);
	}

	public function __get ($property)
	{
		$this->connect();

		return $this->$property;
	}

	private function connect()
	{
		if (!$this->connected) {
			var_dump('B');
			parent::__construct(
				$this->dsn,
				$this->user,
				$this->password,
				array_replace(slef::$defaultOptions, $this->options)
			);
			$this->connected = true;
		}
	}

	public static function once(
		string $dsn,
		string $user = null,
		string $password = null,
		array $options = [],
	): ?static
	{
        if (!isset(self::$PDOInstances[$dsn])) {
			self::$PDOInstances[$dsn] = new self(
				$dsn,
				$user,
				$password,
				$options,
			);
		}

		return self::$PDOInstances[$dsn];
	}
}
