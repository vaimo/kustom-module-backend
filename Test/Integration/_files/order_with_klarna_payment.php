<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

/**
 * Creates a test order with Klarna payment method for integration testing
 *
 * Creates the following test data:
 * - Simple product (SKU: simple-test-product-klarna, Price: $10)
 * - Guest order with increment ID: 100000001
 * - Klarna order entry with test reservation ID and session ID
 *
 * @see invoice_with_klarna_payment.php for invoice creation
 * @see order_with_klarna_payment_rollback.php for cleanup
 */

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Address as OrderAddress;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\Order\Item as OrderItem;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Klarna\Base\Api\OrderRepositoryInterface as KlarnaOrderRepositoryInterface;
use Klarna\Base\Model\OrderFactory as KlarnaOrderFactory;

$objectManager = Bootstrap::getObjectManager();

try {
    // Create simple product
    /** @var ProductRepositoryInterface $productRepository */
    $productRepository = $objectManager->get(ProductRepositoryInterface::class);

    $product = $objectManager->create(Product::class);
    $product->setTypeId(Type::TYPE_SIMPLE)
        ->setAttributeSetId(4)
        ->setWebsiteIds([1])
        ->setName('Simple Test Product')
        ->setSku('simple-test-product-klarna')
        ->setPrice(10)
        ->setWeight(1)
        ->setVisibility(Visibility::VISIBILITY_BOTH)
        ->setStatus(Status::STATUS_ENABLED)
        ->setStockData([
            'use_config_manage_stock' => 1,
            'qty' => 100,
            'is_qty_decimal' => 0,
            'is_in_stock' => 1,
        ]);
    $productRepository->save($product);

    // Create order directly (bypass quote to avoid payment method issues)
    /** @var Order $order */
    $order = $objectManager->create(Order::class);

    $storeManager = $objectManager->get(StoreManagerInterface::class);
    $store = $storeManager->getStore();

    // Set order data
    $order->setIncrementId('100000001')
        ->setState(Order::STATE_PROCESSING)
        ->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_PROCESSING))
        ->setCustomerIsGuest(true)
        ->setCustomerEmail('john.doe.klarna@example.com')
        ->setCustomerFirstname('John')
        ->setCustomerLastname('Doe')
        ->setStoreId($store->getId())
        ->setEmailSent(0)
        ->setBaseCurrencyCode('USD')
        ->setStoreCurrencyCode('USD')
        ->setOrderCurrencyCode('USD');

    // Set billing address
    $billingAddress = $objectManager->create(OrderAddress::class);
    $billingAddress->setFirstname('John')
        ->setLastname('Doe')
        ->setStreet(['123 Test Street'])
        ->setCity('Test City')
        ->setPostcode('12345')
        ->setCountryId('US')
        ->setRegionId(1)
        ->setTelephone('555-1234')
        ->setAddressType('billing');
    $order->setBillingAddress($billingAddress);

    // Set shipping address
    $shippingAddress = $objectManager->create(OrderAddress::class);
    $shippingAddress->setFirstname('John')
        ->setLastname('Doe')
        ->setStreet(['123 Test Street'])
        ->setCity('Test City')
        ->setPostcode('12345')
        ->setCountryId('US')
        ->setRegionId(1)
        ->setTelephone('555-1234')
        ->setAddressType('shipping');
    $order->setShippingAddress($shippingAddress);

    // Set payment - use checkmo (always available) to simulate Klarna
    $payment = $objectManager->create(Payment::class);
    $payment->setMethod('checkmo');
    $order->setPayment($payment);

    // Add order item
    $orderItem = $objectManager->create(OrderItem::class);
    $orderItem->setProductId($product->getId())
        ->setQtyOrdered(1)
        ->setBasePrice($product->getPrice())
        ->setPrice($product->getPrice())
        ->setRowTotal($product->getPrice())
        ->setBaseRowTotal($product->getPrice())
        ->setProductType(Type::TYPE_SIMPLE)
        ->setName($product->getName())
        ->setSku($product->getSku());
    $order->addItem($orderItem);

    // Set order totals
    $shippingAmount = 5.00;
    $order->setSubtotal($product->getPrice())
        ->setBaseSubtotal($product->getPrice())
        ->setGrandTotal($product->getPrice() + $shippingAmount)
        ->setBaseGrandTotal($product->getPrice() + $shippingAmount)
        ->setShippingAmount($shippingAmount)
        ->setBaseShippingAmount($shippingAmount)
        ->setShippingDescription('Flat Rate - Fixed');
    $order->save();

    // Create Klarna order entry
    $klarnaOrderFactory = $objectManager->get(KlarnaOrderFactory::class);
    $klarnaOrderRepository = $objectManager->get(KlarnaOrderRepositoryInterface::class);
    $klarnaOrder = $klarnaOrderFactory->create();
    $klarnaOrder->setOrderId((int)$order->getEntityId())
        ->setReservationId('test-reservation-id-12345')
        ->setSessionId('test-session-id-67890')
        ->setKlarnaOrderId('test-klarna-order-id-abcde');
    $klarnaOrderRepository->save($klarnaOrder);
} catch (\Exception $e) {
    throw new \RuntimeException(
        'Failed to create order fixture: ' . $e->getMessage() . "\n" . $e->getTraceAsString()
    );
}
