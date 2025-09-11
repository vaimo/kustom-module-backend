<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Backend\Observer;

use Klarna\Base\Model\Payment\EnablementChecker;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * @internal
 */
class PrepareCaptureObserver implements ObserverInterface
{
    /**
     * @var EnablementChecker
     */
    private EnablementChecker $enablementChecker;

    /**
     * @param EnablementChecker $enablementChecker
     * @codeCoverageIgnore
     */
    public function __construct(EnablementChecker $enablementChecker)
    {
        $this->enablementChecker = $enablementChecker;
    }

    /**
     * Preparing the capture
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $payment = $observer->getPayment();

        if (!$this->enablementChecker->ispPaymentMethodInstanceCodeStartsWithKlarna($payment)) {
            return;
        }

        $payment->setInvoice($observer->getInvoice());
    }
}
