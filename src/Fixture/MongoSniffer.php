<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\Datasource\ConnectionInterface;
use Cake\Datasource\EntityInterface;
use Cake\Event\Event;
use Cake\Event\EventList;
use Cake\Event\EventListenerInterface;
use Cake\Event\EventManager;
use CakephpTestSuiteLight\Sniffer\BaseTableSniffer;
use MongoDB\Collection;
use MongoDB\Database;
use Throwable;

class MongoSniffer extends BaseTableSniffer implements EventListenerInterface
{
    public const DIRTY_TABLE_COLLECTOR = 'test_suite_light_dirty_tables';

    protected Database $db;

    /**
     * @var string
     */
    protected string $testName;

    protected ?Collection $collection = null;

    public function __construct(ConnectionInterface $connection)
    {
        parent::__construct($connection);
        $this->db = $connection->getDriver()->getDatabase();

        EventManager::instance()->setEventList(new EventList());
        EventManager::instance()->on(
            'Model.initialize',
            [$this, 'afterSave']
        );
    }

    /**
     * @inheritDoc
     */
    public function implementedEvents(): array
    {
        return [
            'Model.afterSave' => 'afterSave',
        ];
    }

    /**
     * @noinspection PhpMissingParentCallCommonInspection
     */
    public function init(): void
    {
        $this->collection = $this->db->selectCollection(self::DIRTY_TABLE_COLLECTOR);
    }

    public function setTestName(string $name)
    {
        $this->testName = $name;
    }

    public function afterSave(Event $event, EntityInterface $entity)
    {
        $data = [
            'test' => $this->testName,
            'model' => $event->getSubject()->getTable(),
            'model_id' => $entity->id,
        ];
        $this->collection->insertOne($data);
    }

    public function truncateDirtyTables(): void
    {
        try {
            if (!$this->collection) {
                $this->init();
            }

            $results = $this->collection->find();
            foreach ($results as $result) {
                $collection = $this->db->selectCollection($result->model);
                $dropped = $collection->drop(['_id' => $result->model_id]);
                if ($dropped->ok) {
                    $this->collection->drop(['_id' => $result->_id]);
                }
            }
        } catch (Throwable) {
            $this->init();
            $this->truncateDirtyTables();
        }
    }

    public function getDirtyTables(): array
    {
        // TODO: Implement getDirtyTables() method.
    }

    public function dropTables(array $tables): void
    {
        // TODO: Implement dropTables() method.
    }
}
