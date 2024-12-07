<?php
declare(strict_types=1);

/**
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) 2020 Juan Pablo Ramirez and Nicolas Masson
 * @link          https://webrider.de/
 * @since         1.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

namespace App\Test\Factory;

use Cake\Core\Configure;
use Cake\Datasource\RepositoryInterface;
use RuntimeException;

/**
 * Class EventCollector
 *
 * @internal
 */
class EventCollector
{
    public const MODEL_EVENTS = 'CakephpFixtureFactoriesListeningModelEvents';
    public const MODEL_BEHAVIORS = 'CakephpFixtureFactoriesListeningBehaviors';
    /**
     * @var \App\Test\Factory\BaseFactory
     */
    protected BaseFactory $factory;

    /**
     * @var \Cake\Datasource\RepositoryInterface|null
     */
    private ?RepositoryInterface $table = null;

    /**
     * @var array
     */
    private array $listeningBehaviors = [];

    /**
     * @var array
     */
    private array $listeningModelEvents = [];

    /**
     * @var array
     */
    private array $defaultListeningBehaviors = [];

    /**
     * @var string
     */
    private string $rootTableRegistryName;

    /**
     * EventCollector constructor.
     *
     * @param string $rootTableRegistryName Name of the model of the master factory
     */
    public function __construct(BaseFactory $factory)
    {
        $this->factory = $factory;
        $this->setDefaultListeningBehaviors();
    }

    /**
     * Create a table cloned from the TableRegistry
     * and per default without Model Events.
     */
    public function getTable(): RepositoryInterface
    {
        if (isset($this->table)) {
            return $this->table;
        }

        $options = [
            self::MODEL_EVENTS => $this->getListeningModelEvents(),
            self::MODEL_BEHAVIORS => $this->getListeningBehaviors(),
        ];

        try {
            $locator = $this->factory->getRepositoryLocator();

            $table = $locator->get($this->factory->getRepositoryName(), $options);
        } catch (RuntimeException $exception) {
            debug($exception->getMessage());
            exit;
            // FactoryTableRegistry::getTableLocator()->remove($this->rootTableRegistryName);
            // $table = FactoryTableRegistry::getTableLocator()->get($this->rootTableRegistryName, $options);
        }

        return $this->table = $table;
    }

    /**
     * @return array
     */
    public function getListeningBehaviors(): array
    {
        return $this->listeningBehaviors;
    }

    /**
     * @param array $activeBehaviors Behaviors the factory will listen to
     * @return array
     */
    public function listeningToBehaviors(array $activeBehaviors): array
    {
        unset($this->table);

        return $this->listeningBehaviors = array_merge($this->defaultListeningBehaviors, $activeBehaviors);
    }

    /**
     * @param array $activeModelEvents Events the factory will listen to
     * @return array
     */
    public function listeningToModelEvents(array $activeModelEvents): array
    {
        unset($this->table);

        return $this->listeningModelEvents = $activeModelEvents;
    }

    /**
     * @return array
     */
    public function getListeningModelEvents(): array
    {
        return $this->listeningModelEvents;
    }

    /**
     * @return void
     */
    protected function setDefaultListeningBehaviors(): void
    {
        $defaultBehaviors = (array)Configure::read('FixtureFactories.testFixtureGlobalBehaviors', []);
        $defaultBehaviors[] = 'Timestamp';
        $this->defaultListeningBehaviors = $defaultBehaviors;
        $this->listeningBehaviors = $defaultBehaviors;
    }

    /**
     * @return array
     */
    public function getDefaultListeningBehaviors(): array
    {
        return $this->defaultListeningBehaviors;
    }
}
