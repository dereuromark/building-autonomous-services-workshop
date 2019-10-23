<?php
declare(strict_types=1);

use Common\Persistence\Database;
use Common\Persistence\KeyValueStore;
use Common\Stream\Stream;
use Common\Web\HttpApi;
use Ramsey\Uuid\Uuid;
use Sales\OrderStatus;
use Symfony\Component\Debug\Debug;

require __DIR__ . '/../../vendor/autoload.php';

Debug::enable();

$startAtIndexKey = $startAtIndexKey = basename(__DIR__) . '_start_at_index';

$startAtIndex = KeyValueStore::get($startAtIndexKey) ?: 0;
echo 'Start consuming at index: ' . (string)$startAtIndex;

Stream::consume(
    function (string $messageType, $data) use ($startAtIndexKey) {
        if ($messageType === 'sales.sales_order_created') {
            $orderStatus = new OrderStatus($data['salesOrderId']);
            Database::persist($orderStatus);

            echo HttpApi::postFormData(
                'http://stock_web/makeStockReservation',
                [
                    'reservationId' => $data['salesOrderId'],
                    'productId' => $data['productId'],
                    'quantity' => $data['quantity']
                ]
            );
        } elseif ($messageType === 'stock.reservation_accepted') {
            echo HttpApi::postFormData(
                'http://sales_web/deliverSalesOrder',
                [
                    'salesOrderId' => $data['reservationId']
                ]
            );
        } elseif ($messageType === 'stock.reservation_rejected') {
            /** @var OrderStatus $orderStatus */
            $orderStatus = Database::retrieve(OrderStatus::class, $data['reservationId']);
            $purchaseOrderId = Uuid::uuid4()->toString();
            $orderStatus->setPurchaseOrderId($purchaseOrderId);
            Database::persist($orderStatus);

            echo HttpApi::postFormData(
                'http://purchase_web/createPurchaseOrder',
                [
                    'purchaseOrderId' => $purchaseOrderId,
                    'productId' => $data['productId'],
                    'quantity' => $data['quantity']
                ]
            );
        } elseif ($messageType === 'stock.stock_level_increased') {
            $purchaseOrderId = $data['correlationId'];

            $orderStatus = Database::findOne(OrderStatus::class, function (OrderStatus $orderStatus) use ($purchaseOrderId) {
                return $orderStatus->purchaseOrderId() === $purchaseOrderId;
            });
            if ($orderStatus instanceof OrderStatus) {
                echo HttpApi::postFormData(
                    'http://stock_web/makeStockReservation',
                    [
                        'reservationId' => $orderStatus->id(), // the sales order ID
                        'productId' => $data['productId'], // the product that was just received
                        'quantity' => $data['quantity'] // the quantity that was just received
                    ]
                );
            }
        } elseif ($messageType === 'sales.goods_delivered') {
            echo HttpApi::postFormData(
                'http://stock_web/commitStockReservation',
                [
                    'reservationId' => $data['salesOrderId'],
                    'productId' => $data['productId']
                ]
            );
        }

        KeyValueStore::incr($startAtIndexKey);
    },
    $startAtIndex
);
