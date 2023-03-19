<?php

namespace Battis\PHPUnit\PDO\Tests\Traits;

use Battis\PHPUnit\PDO\Tests\Fixtures\Traits\PDOTest\PDOFixture;
use PDO;
use PHPUnit\Framework\TestCase;

class PDOTest extends TestCase
{
    public function testPDO(): void
    {
        $this->assertNull(PDOFixture::fixtureGetPDO());
        $pdo = PDOFixture::getPDO();
        $this->assertInstanceOf(PDO::class, $pdo);
        PDOFixture::destroyPDO();
        $this->assertNull(PDOFixture::fixtureGetPDO());
        $this->assertInstanceOf(PDO::class, PDOFixture::getPDO());
    }
}
