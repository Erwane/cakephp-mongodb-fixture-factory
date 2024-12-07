<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\Event\EventListenerInterface;
use CakephpTestSuiteLight\Fixture\TriggerStrategy;
use CakephpTestSuiteLight\Sniffer\SnifferRegistry;

class EventFixtureStrategy extends TriggerStrategy
{
    private string $testName;

    public function setTestName(string $name)
    {
        $this->testName = $name;
    }

    /**
     * Scan all test connections and truncate the dirty tables
     *
     * @return void
     */
    public function truncateDirtyTables(): void
    {
        foreach ($this->getActiveConnections() as $connection) {
            $sniffer = SnifferRegistry::get($connection);
            if ($sniffer instanceof EventListenerInterface) {
                $sniffer->setTestName($this->testName);
            }

            $sniffer->truncateDirtyTables();
        }
    }
}
