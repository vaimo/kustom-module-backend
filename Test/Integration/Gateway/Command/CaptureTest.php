<?php /** @noinspection PhpInternalEntityUsedInspection */
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Backend\Test\Integration\Gateway\Command;

use Klarna\Backend\Gateway\Command\Capture;
use Klarna\Backend\Model\Api\Factory;
use Klarna\Backend\Model\Api\OrderManagement;
use Klarna\Backend\Model\Validator;
use Klarna\Backend\Test\Integration\Stub\StubRequest;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\DataObject;
use Magento\Payment\Gateway\Data\PaymentDataObjectFactory;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CaptureTest extends TestCase
{
    private const TEST_ORDER_INCREMENT_ID = '100000001';
    private const TEST_RESERVATION_ID = 'test-reservation-id-12345';
    private const TEST_CAPTURE_ID = 'test-capture-123';

    private ?Capture $captureCommand = null;
    private OrderManagement|MockObject|null $mockOrderManagement = null;
    private ?StubRequest $stubRequest = null;
    private ?PaymentDataObjectFactory $paymentDataObjectFactory = null;
    private ?OrderRepositoryInterface $orderRepository = null;
    private ?SearchCriteriaBuilder $searchCriteriaBuilder = null;
    private ?InvoiceRepositoryInterface $invoiceRepository = null;

    /** @noinspection ObjectManagerInspection */
    protected function setUp(): void
    {
        parent::setUp();

        $objectManager = Bootstrap::getObjectManager();
        $this->paymentDataObjectFactory = $objectManager->get(PaymentDataObjectFactory::class);
        $this->orderRepository = $objectManager->get(OrderRepositoryInterface::class);
        $this->searchCriteriaBuilder = $objectManager->get(SearchCriteriaBuilder::class);
        $this->invoiceRepository = $objectManager->get(InvoiceRepositoryInterface::class);

        $mockApiFactory = $this->createMock(Factory::class);
        $mockValidator = $this->createMock(Validator::class);
        $this->stubRequest = $objectManager->create(StubRequest::class);
        $this->mockOrderManagement = $this->createMock(OrderManagement::class);

        $mockApiFactory->method('createOmApi')->willReturn($this->mockOrderManagement);

        $this->captureCommand = $objectManager->create(
            Capture::class,
            [
                'omFactory' => $mockApiFactory,
                'validator' => $mockValidator,
                'request' => $this->stubRequest
            ]
        );
    }

    /**
     * Test capture with tracking info present but shipment not processed.
     * Scenario: Admin adds tracking to invoice after separate shipment or manual tracking entry.
     * Verifies tracking presence alone doesn't trigger shipping API without do_shipment flag.
     *
     * @magentoDataFixture Klarna_Backend::Test/Integration/_files/invoice_with_klarna_payment.php
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testExecuteCaptureWithValidTrackingInfoButNoShipment(): void
    {
        $invoice = $this->getInvoiceByOrderIncrementId(self::TEST_ORDER_INCREMENT_ID);
        $order = $invoice->getOrder();
        $payment = $order->getPayment();
        $payment->setData('invoice', $invoice);

        $this->stubRequest->setPostData([
            'tracking' => [
                [
                    'carrier_code' => 'ups',
                    'title' => 'UPS Ground',
                    'number' => '1Z999AA10123456784',
                ]
            ]
        ]);

        $this->mockOrderManagement->method('isFullyCaptured')->willReturn(false);

        $captureResponse = new DataObject(['capture_id' => self::TEST_CAPTURE_ID]);
        $this->mockOrderManagement->expects($this->once())
            ->method('capture')
            ->willReturn($captureResponse);

        $this->mockOrderManagement->expects($this->never())
            ->method('addShippingInfo');

        $paymentDataObject = $this->paymentDataObjectFactory->create($payment);

        $this->captureCommand->execute([
            'payment' => $paymentDataObject,
            'amount' => 100.00,
        ]);
    }

    /**
     * Test capture with valid tracking info and do_shipment flag set.
     * Scenario: Admin creates invoice with shipment and provides full carrier tracking details.
     * Verifies capture proceeds and shipping info is sent to Klarna with a success comment on the invoice.
     *
     * @magentoDataFixture Klarna_Backend::Test/Integration/_files/invoice_with_klarna_payment.php
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testExecuteCaptureWithShipmentProcessing(): void
    {
        $invoice = $this->getInvoiceByOrderIncrementId(self::TEST_ORDER_INCREMENT_ID);
        $order = $invoice->getOrder();
        $payment = $order->getPayment();
        $payment->setData('invoice', $invoice);

        $this->stubRequest->setPostData([
            'invoice' => ['do_shipment' => '1'],
            'tracking' => [
                [
                    'carrier_code' => 'ups',
                    'title' => 'UPS Ground',
                    'number' => '1Z999AA10123456784',
                ]
            ]
        ]);

        $this->mockOrderManagement->method('isFullyCaptured')->willReturn(false);

        $captureResponse = new DataObject(['capture_id' => self::TEST_CAPTURE_ID]);
        $this->mockOrderManagement->expects($this->once())
            ->method('capture')
            ->willReturn($captureResponse);

        $shippingResponse = new DataObject([
            'is_successful' => true,
        ]);

        $this->mockOrderManagement->expects($this->once())
            ->method('addShippingInfo')
            ->with(
                self::TEST_RESERVATION_ID,
                self::TEST_CAPTURE_ID,
                $this->isType('array')
            )
            ->willReturn($shippingResponse);

        $paymentDataObject = $this->paymentDataObjectFactory->create($payment);

        $this->captureCommand->execute([
            'payment' => $paymentDataObject,
            'amount' => 100.00
        ]);

        $this->invoiceRepository->save($invoice);
        $reloadedInvoice = $this->invoiceRepository->get($invoice->getEntityId());
        $this->assertInvoiceHasComment($reloadedInvoice, 'Shipping info sent to Klarna API');
    }

    /**
     * Test capture returns early when tracking info is present but missing required carrier_code.
     * Scenario: Incomplete tracking data submitted — tracking array exists but carrier_code is absent.
     * Verifies isTrackingInfoValid() rejects the data and capture is never called.
     *
     * @magentoDataFixture Klarna_Backend::Test/Integration/_files/invoice_with_klarna_payment.php
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testExecuteWithInvalidTrackingInfoReturnsEarly(): void
    {
        $invoice = $this->getInvoiceByOrderIncrementId(self::TEST_ORDER_INCREMENT_ID);
        $order = $invoice->getOrder();
        $payment = $order->getPayment();

        $this->stubRequest->setPostData([
            'tracking' => [
                [
                    'title' => 'UPS',
                    'number' => '123',
                ]
            ]
        ]);

        $this->mockOrderManagement->expects($this->never())
            ->method('capture');

        $paymentDataObject = $this->paymentDataObjectFactory->create($payment);

        $this->captureCommand->execute([
            'payment' => $paymentDataObject,
            'amount' => 100.00,
        ]);
    }

    /**
     * Test capture with do_shipment flag but empty tracking array skips shipping info call.
     * Scenario: do_shipment is set but no tracking entries were provided (e.g., merchant forgot to add them).
     * Verifies the $hasTracking guard prevents addShippingInfo from being called when tracking is empty.
     *
     * @magentoDataFixture Klarna_Backend::Test/Integration/_files/invoice_with_klarna_payment.php
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testExecuteWithShipmentButEmptyTrackingSkipsShippingInfo(): void
    {
        $invoice = $this->getInvoiceByOrderIncrementId(self::TEST_ORDER_INCREMENT_ID);
        $order = $invoice->getOrder();
        $payment = $order->getPayment();
        $payment->setData('invoice', $invoice);

        $this->stubRequest->setPostData([
            'invoice' => ['do_shipment' => '1'],
            'tracking' => [],
        ]);

        $this->mockOrderManagement->method('isFullyCaptured')->willReturn(false);

        $captureResponse = new DataObject(['capture_id' => self::TEST_CAPTURE_ID]);
        $this->mockOrderManagement->expects($this->once())
            ->method('capture')
            ->willReturn($captureResponse);

        $this->mockOrderManagement->expects($this->never())
            ->method('addShippingInfo');

        $paymentDataObject = $this->paymentDataObjectFactory->create($payment);

        $this->captureCommand->execute([
            'payment' => $paymentDataObject,
            'amount' => 100.00,
        ]);

        $this->invoiceRepository->save($invoice);
        $reloadedInvoice = $this->invoiceRepository->get($invoice->getEntityId());
        $comments = $reloadedInvoice->getCommentsCollection(reload: true);
        $this->assertCount(0, $comments, 'No comments should be added when tracking is empty');
    }

    /**
     * Test capture with shipment processing when the Klarna shipping API returns a failure response.
     * Scenario: Valid tracking info and do_shipment flag set, but Klarna API rejects the shipping info.
     * Verifies each error message from the API response is recorded as a comment on the invoice.
     *
     * @magentoDataFixture Klarna_Backend::Test/Integration/_files/invoice_with_klarna_payment.php
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testExecuteWithShipmentProcessingApiFailure(): void
    {
        $invoice = $this->getInvoiceByOrderIncrementId(self::TEST_ORDER_INCREMENT_ID);
        $order = $invoice->getOrder();
        $payment = $order->getPayment();
        $payment->setData('invoice', $invoice);

        $this->stubRequest->setPostData([
            'invoice' => ['do_shipment' => '1'],
            'tracking' => [
                [
                    'carrier_code' => 'ups',
                    'title' => 'UPS Ground',
                    'number' => '1Z999AA10123456784',
                ]
            ]
        ]);

        $this->mockOrderManagement->method('isFullyCaptured')->willReturn(false);

        $captureResponse = new DataObject(['capture_id' => self::TEST_CAPTURE_ID]);
        $this->mockOrderManagement->expects($this->once())
            ->method('capture')
            ->willReturn($captureResponse);

        $shippingResponse = new DataObject([
            'is_successful' => false,
            'error_messages' => ['Invalid tracking number', 'Carrier not supported'],
        ]);

        $this->mockOrderManagement->expects($this->once())
            ->method('addShippingInfo')
            ->willReturn($shippingResponse);

        $paymentDataObject = $this->paymentDataObjectFactory->create($payment);

        $this->captureCommand->execute([
            'payment' => $paymentDataObject,
            'amount' => 100.00,
        ]);

        $this->invoiceRepository->save($invoice);
        $reloadedInvoice = $this->invoiceRepository->get($invoice->getEntityId());
        $this->assertInvoiceHasComment($reloadedInvoice, 'Invalid tracking number');
        $this->assertInvoiceHasComment($reloadedInvoice, 'Carrier not supported');
    }

    /**
     * Get invoice by order increment ID
     *
     * @param string $incrementId
     *
     * @return \Magento\Sales\Api\Data\InvoiceInterface
     */
    private function getInvoiceByOrderIncrementId(string $incrementId): InvoiceInterface
    {
        /** @var \Magento\Sales\Model\Order $order */
        $searchCriteria = $this->searchCriteriaBuilder->addFilter('increment_id', $incrementId)->create();
        $order = $this->orderRepository->getList($searchCriteria)->getFirstItem();

        if (!$order->getId()) {
            throw new \RuntimeException("Order with increment ID {$incrementId} not found");
        }

        // Force reload of invoice collection
        $order = $this->orderRepository->get($order->getId());
        $invoices = $order->getInvoiceCollection();

        $this->assertGreaterThan(
            0,
            $invoices->getSize(),
            sprintf(
                'No invoice found for order %s. Order state: %s, can invoice: %s',
                $incrementId,
                $order->getState(),
                $order->canInvoice() ? 'yes' : 'no'
            )
        );

        return $invoices->getFirstItem();
    }

    /**
     * Assert that invoice has specific comment
     *
     * @param \Magento\Sales\Api\Data\InvoiceInterface $invoice
     * @param string $commentText
     */
    private function assertInvoiceHasComment(InvoiceInterface $invoice, string $commentText): void
    {
        // Use getCommentsCollection to load from database
        $comments = $invoice->getCommentsCollection(true); // true = reload
        $found = false;
        $existingComments = [];
        foreach ($comments as $comment) {
            $existingComments[] = $comment->getComment();
            if (\str_contains($comment->getComment(), $commentText)) {
                $found = true;
                break;
            }
        }
        $this->assertTrue(
            $found,
            sprintf(
                "Invoice comment not found: '%s'. Existing comments: %s",
                $commentText,
                empty($existingComments) ? 'none' : implode('; ', $existingComments)
            )
        );
    }
}
