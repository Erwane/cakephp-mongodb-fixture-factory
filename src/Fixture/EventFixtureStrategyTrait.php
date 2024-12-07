<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\FixtureStrategyInterface;

/**
 * Setup EventFixtureStrategyTrait
 */
trait EventFixtureStrategyTrait
{
    /**
     * @return \Cake\TestSuite\Fixture\FixtureStrategyInterface
     */
    protected function getFixtureStrategy(): FixtureStrategyInterface
    {
        $strategy = new EventFixtureStrategy();

        $strategy->setTestName($this->name());

        return $strategy;
    }
}
