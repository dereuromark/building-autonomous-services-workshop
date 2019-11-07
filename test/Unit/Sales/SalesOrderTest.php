<?php
declare(strict_types=1);

namespace Sales;

use Common\Persistence\IdentifiableObject;
use LogicException;
use Test\Integration\EntityTest;

final class SalesOrderTest extends EntityTest
{
    /**
     * @test
     */
    public function it_can_be_created_with_an_id_and_lines(): void
    {
        $salesOrderId = SalesOrderId::create();
        $productId = '8513f8f0-9ed6-4096-b84c-3274dc0394d1';
        $quantity = 10;
        $salesOrder = new SalesOrder($salesOrderId, $productId, $quantity);

        self::assertEquals($salesOrderId, $salesOrder->id());
        self::assertEquals($productId, $salesOrder->productId());
        self::assertEquals($quantity, $salesOrder->quantity());
    }

    /**
     * @test
     */
    public function initially_it_has_not_been_delivered_yet_nor_can_it_be_delivered(): void
    {
        $salesOrder = $this->someSalesOrder();

        self::assertFalse($salesOrder->wasDelivered());
        self::assertFalse($salesOrder->isDeliverable());
    }

    /**
     * @test
     */
    public function it_can_be_marked_as_deliverable(): void
    {
        $salesOrder = $this->someSalesOrder();

        $salesOrder->markAsDeliverable();

        self::assertTrue($salesOrder->isDeliverable());
    }

    /**
     * @test
     */
    public function it_has_to_be_marked_as_deliverable_before_it_can_be_delivered(): void
    {
        $salesOrder = $this->someSalesOrder();

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('should first be marked as deliverable');

        $salesOrder->deliver();
    }

    /**
     * @test
     */
    public function it_will_remember_if_it_was_delivered(): void
    {
        $salesOrder = $this->someSalesOrder();
        $salesOrder->markAsDeliverable();

        $salesOrder->deliver();

        self::assertTrue($salesOrder->wasDelivered());
    }

    /**
     * @return SalesOrder
     */
    private function someSalesOrder(): SalesOrder
    {
        return new SalesOrder(SalesOrderId::create(), '8513f8f0-9ed6-4096-b84c-3274dc0394d1', 10);
    }

    protected function getObject(): IdentifiableObject
    {
        return $this->someSalesOrder();
    }
}
