<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

/**
 * Rollback script for order_with_klarna_payment.php fixture
 *
 * Cleans up test data created by order_with_klarna_payment.php:
 * - Deletes order (increment ID: 100000001)
 * - Deletes associated Klarna order entry
 * - Deletes test product (simple-test-product-klarna)
 *
 * Uses secure area registry to bypass restrictions during deletion.
 * Catches exceptions for entities that may already be deleted.
 *
 * @see order_with_klarna_payment.php for fixture creation
 */

use Magento\Framework\Registry;
use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\TestFramework\Helper\Bootstrap;
use Klarna\Base\Model\OrderFactory as KlarnaOrderFactory;
use Klarna\Base\Model\ResourceModel\Order as KlarnaOrderResource;
use Magento\Catalog\Api\ProductRepositoryInterface;

$objectManager = Bootstrap::getObjectManager();

/** @var Registry $registry */
$registry = $objectManager->get(Registry::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

// Delete order
try {
    /** @var \Magento\Sales\Model\Order $order */
    $order = $objectManager->get(OrderInterfaceFactory::class)->create()->loadByIncrementId('100000001');

    if ($order->getId()) {
        $klarnaOrderFactory = $objectManager->get(KlarnaOrderFactory::class);
        $klarnaOrderResource = $objectManager->get(KlarnaOrderResource::class);
        $klarnaOrder = $klarnaOrderFactory->create();
        $klarnaOrderResource->load($klarnaOrder, $order->getEntityId(), 'order_id');

        if ($klarnaOrder->getId()) {
            $klarnaOrderResource->delete($klarnaOrder);
        }

        $order->delete();
    }
} catch (\Exception $e) {
    // Order already deleted
}

// Delete product
try {
    $productRepository = $objectManager->get(ProductRepositoryInterface::class);
    $product = $productRepository->get('simple-test-product-klarna');
    $productRepository->delete($product);
} catch (\Exception $e) {
    // Product already deleted
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
