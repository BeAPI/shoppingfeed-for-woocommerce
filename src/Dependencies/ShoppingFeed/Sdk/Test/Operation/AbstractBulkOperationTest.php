<?php
namespace ShoppingFeed\Sdk\Test\Operation;

use PHPUnit\Framework\TestCase;
use ShoppingFeed\Sdk\Operation\AbstractBulkOperation;
use ShoppingFeed\Sdk\Operation\AbstractOperation;

class AbstractBulkOperationTest extends TestCase
{
    public function testGetterSetters()
    {
        /** @var AbstractBulkOperation $instance */
        $instance = $this->getMockForAbstractClass(AbstractBulkOperation::class);

        $instance
            ->setBatchSize(10)
            ->setPoolSize(20);

        $this->assertEquals(10, $instance->getBatchSize());
        $this->assertEquals(20, $instance->getPoolSize());
    }

    public function testEachBatchCallback()
    {
        $operations = [];
        for ($i = 0; $i < 50; $i++) {
            $operations[] = 'operation' . $i;
        };
        $instance = new BulkOperationMock($operations);
        $instance->setBatchSize(2);

        $count    = 0;
        $opeCount = count($operations);
        $tester   = $this;
        $instance->eachBatch(
            function ($chunck) use ($tester, $opeCount, &$count) {
                if ($opeCount % 2 && $opeCount - $count === 1) {
                    $tester->assertCount(1, $chunck);
                } else {
                    $tester->assertCount(2, $chunck);
                    $count += 2;
                }
            }
        );
    }

    /**
     * Test that the batch creation group only handle the requested group
     */
    public function testEachBatchGroupedBy()
    {
        // Create 2 groups of operations : pair > group0 and odd > group1
        $operations = [];
        for ($i = 0; $i < 100; $i++) {
            $operations['group' . ($i % 2 ? '1' : '0')][] = 'operation' . $i;
        };

        $instance = new BulkOperationMock($operations);
        $instance->setBatchSize(10);

        $count = 0;
        $instance->eachBatch(
            function ($chunk) use (&$count) {
                $count += count($chunk);
            },
            'group1'
        );

        // Assert that the sum of all chuncks = number of operation in group1
        $this->assertEquals(count($operations['group1']), $count);
    }

    public function testCountOperation()
    {
        $countOperation = 50;
        $operations     = [];
        for ($i = 0; $i < $countOperation; $i++) {
            $operations[] = $this->createMock(AbstractOperation::class);
        };
        $instance = new BulkOperationMock($operations);

        $this->assertEquals($countOperation, $instance->count());
    }

    public function testCountOperationWithFilter()
    {
        $countOperation = 50;
        $operations     = [];
        for ($i = 0; $i < $countOperation; $i++) {
            $operations[$i % 2 ? 'group1' : 'group2'][] = $this->createMock(AbstractOperation::class);
        };
        $instance = new BulkOperationMock($operations);

        $this->assertEquals(count($operations['group1']), $instance->count('group1'));
    }
}
