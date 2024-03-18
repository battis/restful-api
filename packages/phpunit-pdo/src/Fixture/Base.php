<?php

namespace Battis\PHPUnit\PDO\Fixture;

use ArrayAccess;
use Battis\DataUtilities\Traits\ImmutableIterableArray;
use Battis\PHPUnit\PDO\Exceptions\BaseException;
use Countable;
use Iterator;
use Symfony\Component\Yaml\Yaml;

/**
 * @template TypeKey
 * @template TypeStored
 * @template TypeAccessed
 * @implements ArrayAccess<TypeKey, TypeAccessed>
 * @implements Iterator<TypeKey, TypeAccessed>
 */
abstract class Base implements ArrayAccess, Iterator, Countable
{
    /** @use ImmutableIterableArray<TypeKey, TypeStored, TypeAccessed> */
    use ImmutableIterableArray;

    /**
     * @param array<string, mixed> $config
     */
    abstract protected function __construct(array $config);

    /**
     * @param array<string, mixed> $array
     * @return Base<TypeKey, TypeStored, TypeAccessed>
     */
    abstract public static function fromArray(array $array): Base;

    /**
     * @param string $pathToFile
     * @return Base<TypeKey, TypeStored, TypeAccessed>
     * @throws BaseException if YAML does not parse to an array
     */
    public static function fromYamlFile(string $pathToFile): Base
    {
        /** @var mixed */
        $array = Yaml::parseFile($pathToFile);
        if (is_array($array)) {
            return static::fromArray($array);
        } else {
            throw new BaseException('YAML does not parse to an array');
        }
    }

    /**
     * @param Base<TypeKey, TypeStored, TypeAccessed> $other
     * @return bool
     */
    abstract public function equals(Base $other): bool;
}
