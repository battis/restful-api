<?php

namespace Tests\Battis\CRUD;

use Battis\CRUD\Connection;
use PDO;
use PHPUnit\Framework\TestCase;
use TypeError;

/**
 * @backupStaticAttributes enabled
 */
class ConnectionTest extends TestCase
{
    private $pdo;

    private function getSqliteMemoryPDO(): PDO
    {
        if ($this->pdo == null) {
            $this->pdo = new PDO("sqlite::memory:");
        }
        return $this->pdo;
    }

    public function testNullPDOArgument()
    {
        $this->expectException(TypeError::class);
        Connection::getInstance();
    }

    public function testValidPDOArgument()
    {
        $this->assertInstanceOf(
            Connection::class,
            Connection::getInstance($this->getSqliteMemoryPDO())
        );
        $this->assertInstanceOf(Connection::class, Connection::getInstance());
    }

    public function testSingleton()
    {
        $a = Connection::getInstance($this->getSqliteMemoryPDO());
        $b = Connection::getInstance();
        $this->assertSame($a, $b);
        $this->assertSame($b, Connection::getInstance());
    }

    public function testCreateQuery()
    {
        Connection::getInstance($this->getSqliteMemoryPDO());
        $this->assertSame(
            $this->getSqliteMemoryPDO(),
            Connection::createQuery()->getPdo()
        );
    }

    public function testNullPDOCreateQuery()
    {
        $this->expectException(TypeError::class);
        Connection::createQuery();
    }
}
