<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

namespace Klarna\Backend\Test\Observer;

use Klarna\Backend\Observer\PrepareCaptureObserver;
use Klarna\Base\Test\Unit\Mock\TestCase;
use Magento\Framework\Event\Observer;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\Order\Invoice;

/**
 * @coversDefaultClass \Klarna\Backend\Observer\PrepareCaptureObserver
 */
class PrepareCaptureObserverTest extends TestCase
{
    /**
     * @var PrepareCaptureObserver
     */
    private PrepareCaptureObserver $prepareCapture;
    /**
     * @var Observer
     */
    private Observer $observer;
    /**
     * @var Payment
     */
    private Payment $payment;

    public function testExecuteInvoiceWontBeSetSinceThePaymentMethodCodeDoesNotStartWithKlarna(): void
    {
        $this->dependencyMocks['enablementChecker']->method('ispPaymentMethodInstanceCodeStartsWithKlarna')
            ->willReturn(false);

        $this->payment->expects(static::never())
            ->method('setInvoice');
        $this->prepareCapture->execute($this->observer);
    }

    public function testExecuteInvoiceWillBeSetSinceThePaymentMethodCodeStartsWithKlarna(): void
    {
        $this->dependencyMocks['enablementChecker']->method('ispPaymentMethodInstanceCodeStartsWithKlarna')
            ->willReturn(true);

        $this->payment->expects(static::once())
            ->method('setInvoice');
        $this->prepareCapture->execute($this->observer);
    }

    protected function setUp(): void
    {
        $this->prepareCapture = parent::setUpMocks(PrepareCaptureObserver::class);

        $this->observer = $this->mockFactory->create(Observer::class, [], ['getPayment', 'getInvoice']);
        $this->payment = $this->mockFactory->create(Payment::class, [], ['setInvoice']);
        $this->observer->method('getPayment')
            ->willReturn($this->payment);
        $invoice = $this->mockFactory->create(Invoice::class);
        $this->observer->method('getInvoice')
            ->willReturn($invoice);
    }
}