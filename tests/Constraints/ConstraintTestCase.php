<?php

namespace Battis\PHPUnit\PDO\Tests\Constraints;

use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\TestCase;

/**
 * @template TypeConstraint
 */
abstract class ConstraintTestCase extends TestCase
{
    /** @var TypeConstraint */
    protected ?Constraint $constraint = null;

    /**
     * @return TypeConstraint
     */
    abstract protected function getConstraint(): Constraint;

    abstract public function testMatches(): void;

    public function testToString(): void
    {
        $this->assertNotEmpty($this->getConstraint()->toString());
    }
}
