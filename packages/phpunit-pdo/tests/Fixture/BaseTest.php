<?php

namespace Battis\PHPUnit\PDO\Tests\Fixture;

use Battis\DataUtilities\PHPUnit\FixturePath;
use Battis\PHPUnit\PDO\Exceptions\BaseException;
use Battis\PHPUnit\PDO\Tests\Fixtures\Fixture\BaseTest\BaseObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

class BaseTest extends TestCase
{
    use FixturePath;

    public function testFromYamlFile()
    {
        $path = $this->getPathToFixture('base.yaml');
        $array = Yaml::parseFile($path);
        $base = BaseObject::fromArray($array);

        $this->assertTrue($base->equals(BaseObject::fromYamlFile($path)));

        $this->expectException(BaseException::class);
        BaseObject::fromYamlFile($this->getPathToFixture('not-array.yaml'));
    }
}
