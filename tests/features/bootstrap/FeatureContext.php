<?php

use Behat\Behat\Context\Context;
use PHPUnit\Framework\Constraint\IsIdentical;

/**
 * Defines application features from the specific context.
 */
class FeatureContext implements Context
{
    /**
     * @var int $a first argument to add
     */
    protected $a;

    /**
     * @var int $b second argument to add
     */
    protected $b;

    /**
     * @var int $sum sum of arguments
     */
    protected $sum;

    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     */
    public function __construct()
    {
    }

    /**
     * @Given there are :arg1 and :arg2
     */
    public function thereAre(int $arg1, int $arg2)
    {
        $this->a = $arg1;
        $this->b = $arg2;
    }

    /**
     * @When I sum them
     */
    public function iSumThem()
    {
        $this->sum = $this->a + $this->b;
    }

    /**
     * @Then the sum should be :arg1
     */
    public function theSumShouldBe(int $arg1)
    {
        (new IsIdentical($this->sum))->evaluate($arg1);
    }
}
