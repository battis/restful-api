<?php

use Battis\CRUD\Connection;
use Envms\FluentPDO\Query;
use PHPUnit\Framework\TestCase;

/**
 * @backupStaticAttributes enabled
 */
class ConnectionTest extends TestCase
{
    private function getSqliteMemoryPDO(): PDO
    {
        return new PDO("sqlite::memory:");
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
        $this->assertEquals($a, $b);
        $this->assertEquals($b, Connection::getInstance());
    }

    public function testCreateQuery()
    {
        Connection::getInstance($this->getSqliteMemoryPDO());
        $this->assertInstanceOf(Query::class, Connection::createQuery());
    }

    public function testNullPDOCreateQuery()
    {
        $this->expectException(TypeError::class);
        Connection::createQuery();
    }
}
