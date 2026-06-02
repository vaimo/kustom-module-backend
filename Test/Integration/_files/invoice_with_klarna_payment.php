<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

/**
 * Creates an invoice for the test order with Klarna payment
 *
 * Creates:
 * - Invoice for order 100000001
 * - Sets order to "In Process" state
 * - Saves invoice and order in transaction
 *
 * Dependencies:
 * - order_with_klarna_payment.php (must be loaded first)
 *
 * @see order_with_klarna_payment.php for order creation
 */

use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\Sales\Api\InvoiceManagementInterface;
use Magento\Sales\Model\Order;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Klarna_Backend::Test/Integration/_files/order_with_klarna_payment.php');

$objectManager = Bootstrap::getObjectManager();

/** @var Order $order */
$order = $objectManager->get(OrderInterfaceFactory::class)->create()->loadByIncrementId('100000001');

if (!$order->getId()) {
    throw new \RuntimeException('Order with increment ID 100000001 not found. Make sure order_with_klarna_payment.php fixture runs successfully.');
}

// Check if order can be invoiced
if (!$order->canInvoice()) {
    throw new \RuntimeException('Order cannot be invoiced. Order state: ' . $order->getState());
}

$orderService = $objectManager->create(InvoiceManagementInterface::class);
$invoice = $orderService->prepareInvoice($order);

if (!$invoice->getTotalQty()) {
    throw new \RuntimeException('Cannot create invoice with zero quantity.');
}

$invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_OFFLINE);
$invoice->register();

$order->setIsInProcess(true);

$transactionSave = $objectManager->create(\Magento\Framework\DB\Transaction::class);
$transactionSave
    ->addObject($invoice)
    ->addObject($order)
    ->save();
