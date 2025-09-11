<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Backend\Gateway\Command;

use Klarna\Backend\Model\Validator;
use Klarna\Base\Exception as KlarnaException;
use Magento\Framework\DataObject;
use Magento\Payment\Gateway\Command;
use Klarna\Base\Helper\KlarnaConfig;
use Klarna\Base\Model\OrderRepository as KlarnaOrderRepository;
use Klarna\Backend\Model\Api\Factory;
use Magento\Quote\Model\QuoteRepository as MageQuoteRepository;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\OrderRepository as MageOrderRepository;
use Magento\Framework\App\RequestInterface;
use Laminas\Stdlib\Parameters;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @internal
 */
class Capture extends AbstractCommand
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @param KlarnaOrderRepository $kOrderRepository
     * @param MageQuoteRepository $mageQuoteRepository
     * @param MageOrderRepository $mageOrderRepository
     * @param KlarnaConfig $helper
     * @param Factory $omFactory
     * @param RequestInterface $request
     * @param Validator $validator
     * @param array $data
     * @codeCoverageIgnore
     */
    public function __construct(
        KlarnaOrderRepository $kOrderRepository,
        MageQuoteRepository $mageQuoteRepository,
        MageOrderRepository $mageOrderRepository,
        KlarnaConfig $helper,
        Factory $omFactory,
        RequestInterface $request,
        Validator $validator,
        array $data = []
    ) {
        parent::__construct(
            $kOrderRepository,
            $mageQuoteRepository,
            $mageOrderRepository,
            $helper,
            $omFactory,
            $validator,
            $data
        );

        $this->request = $request;
    }

    /**
     * Capture command
     *
     * @param array $commandSubject
     *
     * @return null|Command\ResultInterface
     * @throws KlarnaException
     * @throws \Klarna\Core\Model\Api\Exception&\Throwable
     * @throws \Magento\Framework\Exception\LocalizedException&\Throwable
     * @throws \Magento\Framework\Exception\NoSuchEntityException&\Throwable
     */
    public function execute(array $commandSubject)
    {
        $requestData =  $this->request->getPost();
        /** @var \Magento\Payment\Model\InfoInterface $payment */
        $payment = $commandSubject['payment']->getPayment();
        $amount = (float) $commandSubject['amount'];
        /** @var \Magento\Sales\Api\Data\OrderInterface $magentoOrder */
        $magentoOrder = $payment->getOrder();
        $klarnaOrder = $this->getKlarnaOrder($payment->getOrder());

        $this
            ->getValidator()
            ->checkRequestSendable(
                $klarnaOrder,
                $magentoOrder,
                $this->getValidator()::ACTION_TYPE_CAPTURE
            );

        // if tracking info is invalid, stop capture
        if (!$this->isTrackingInfoValid($requestData['tracking'] ?? null)) {
            return null;
        }

        // if the order is already fully captured, stop capturing
        if ($this->getOmApi($magentoOrder)
            ->isFullyCaptured($magentoOrder->getBaseGrandTotal(), $klarnaOrder->getReservationId())
        ) {
            return null;
        }

        $response = $this->getOmApi($magentoOrder)
            ->capture($klarnaOrder->getReservationId(), $amount, $payment->getInvoice());
        $this->getValidator()->checkApiResponse(
            $klarnaOrder,
            $response,
            $magentoOrder,
            $this->getValidator()::ACTION_TYPE_CAPTURE
        );

        if ($requestData instanceof Parameters) {
            $requestData = $requestData->toArray();
        }

        if ($this->isProcessingShipment($requestData, $response)) {
            $this->addShippingInfoToCapture(
                $response->getCaptureId(),
                $klarnaOrder->getReservationId(),
                $requestData['tracking'],
                $magentoOrder,
                $payment->getInvoice()
            );
        }
        return null;
    }

    /**
     * Add shipping info to capture
     *
     * @param string $captureId
     * @param string $klarnaOrderId
     * @param array $trackingData
     * @param OrderInterface $order
     * @param InvoiceInterface $invoice
     *
     * @return void
     */
    private function addShippingInfoToCapture($captureId, $klarnaOrderId, $trackingData, $order, $invoice)
    {
        $response = $this->getOmApi($order)
            ->addShippingInfo($klarnaOrderId, $captureId, $trackingData);

        if ($response->getIsSuccessful()) {
            $invoice->addComment("Shipping info sent to Klarna API", false, false);
            return;
        }
        foreach ($response->getErrorMessages() as $message) {
            $invoice->addComment($message, false, false);
        }
    }

    /**
     * Check if we are also processing a shipment with this invoice
     *
     * @param array $requestData
     * @param DataObject $response
     * @return bool
     */
    private function isProcessingShipment(array $requestData, DataObject
    $response): bool
    {
        if (isset($requestData['invoice']['do_shipment'])
            && $requestData['invoice']['do_shipment'] === "1"
            && $response->getCaptureId()
            && $this->isTrackingInfoValid($requestData['tracking'] ?? null)) {
            return true;
        }
        return false;
    }

    /**
     * Validate tracking info
     *
     * @param array|null $trackingInformation
     * @return bool
     */
    private function isTrackingInfoValid(?array $trackingInformation): bool
    {
        if ($trackingInformation === null) {
            return true;
        }

        foreach ((array) $trackingInformation as $info) {
            if (empty($info['carrier_code'])
                || empty($info['title'])
                || empty($info['number'])) {
                return false;
            }
        }
        return true;
    }
}
