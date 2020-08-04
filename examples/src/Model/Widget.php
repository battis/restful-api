<?php


namespace Example\Model;


use Battis\PersistentObject\PersistentObjectException;
use Example\ExampleObject;

class Widget extends ExampleObject
{
    const FOO = 'foo';
    const BAR = 'bar';

    /** @var string */
    protected $foo;

    /** @var int|null */
    protected $bar;

    /**
     * @return string
     * @throws PersistentObjectException
     */
    public function getFoo(): string {
        return $this->getField(self::FOO);
    }

    /**
     * @param string $foo
     * @throws PersistentObjectException
     */
    protected function setFoo(string $foo) {
        $this->setField(self::FOO, $foo);
    }

    /**
     * @return int|null
     * @throws PersistentObjectException
     */
    public function getBar() {
        return $this->getField(self::BAR);
    }

    /**
     * @param int $bar
     * @throws PersistentObjectException
     */
    public function setBar(int $bar) {
        $this->setField(self::BAR, $bar);
    }
}