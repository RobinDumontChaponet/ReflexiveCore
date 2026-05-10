<?php

declare(strict_types=1);

namespace Reflexive\Core\Tests;

use PDO;
use PHPUnit\Framework\TestCase;
use Reflexive\Core\Database;

final class DatabaseTest extends TestCase
{
	/** @var list<string> */
	private array $databaseFiles = [];

	protected function tearDown(): void
	{
		foreach($this->databaseFiles as $databaseFile) {
			if(is_file($databaseFile))
				unlink($databaseFile);
		}
	}

	// Verifies the DSN prefix is parsed without opening a connection.
	public function testExposesDsnPrefixWithoutConnecting(): void
	{
		$database = new Database('sqlite::memory:');

		self::assertSame('sqlite', $database->getDSNPrefix());
		self::assertNull((new Database('memory'))->getDSNPrefix());
	}

	// Verifies the PDO connection opens only when first used.
	public function testConnectsLazilyOnFirstPdoOperation(): void
	{
		$databaseFile = $this->temporaryDatabaseFile();
		$database = new Database('sqlite:'.$databaseFile);

		self::assertSame(
			[
				'dsn' => 'sqlite:'.$databaseFile,
				'user' => null,
				'options' => [],
			],
			$database->__debugInfo()
		);

		$database->exec('CREATE TABLE users (id INTEGER PRIMARY KEY, name TEXT)');
		$database->exec("INSERT INTO users (name) VALUES ('Ada')");

		$row = $database->query('SELECT id, name FROM users')->fetch();

		self::assertSame(1, $row->id);
		self::assertSame('Ada', $row->name);
		self::assertSame(PDO::FETCH_OBJ, $database->getAttribute(PDO::ATTR_DEFAULT_FETCH_MODE));
	}

	// Verifies Database::once returns one instance per DSN.
	public function testOnceReusesOneInstancePerDsn(): void
	{
		$firstDatabaseFile = $this->temporaryDatabaseFile();
		$secondDatabaseFile = $this->temporaryDatabaseFile();

		$first = Database::once('sqlite:'.$firstDatabaseFile);
		$second = Database::once('sqlite:'.$firstDatabaseFile);
		$third = Database::once('sqlite:'.$secondDatabaseFile);

		self::assertSame($first, $second);
		self::assertNotSame($first, $third);
	}

	private function temporaryDatabaseFile(): string
	{
		$databaseFile = tempnam(sys_get_temp_dir(), 'reflexive-core-');
		self::assertIsString($databaseFile);

		$this->databaseFiles[] = $databaseFile;

		return $databaseFile;
	}
}
