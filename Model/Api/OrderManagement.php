<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Backend\Model\Api;

use Klarna\Base\Helper\DataConverter;
use Klarna\Backend\Api\ApiInterface;
use Klarna\Backend\Model\Api\Rest\Service\Ordermanagement as OrdermanagementApi;
use Klarna\AdminSettings\Model\Configurations\Api;
use Klarna\Orderlines\Model\Container\Parameter;
use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\CreditmemoInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @internal
 */
class OrderManagement implements ApiInterface
{
    /**
     * Order fraud statuses
     */
    public const ORDER_FRAUD_STATUS_ACCEPTED = 'ACCEPTED';
    public const ORDER_FRAUD_STATUS_REJECTED = 'REJECTED';
    public const ORDER_FRAUD_STATUS_PENDING  = 'PENDING';

    public const RET_ORDER_FRAUD_STATUS_ACCEPTED = 1;
    public const RET_ORDER_FRAUD_STATUS_REJECTED = -1;
    public const RET_ORDER_FRAUD_STATUS_PENDING  = 0;

    /**
     * Order notification statuses
     */
    public const ORDER_NOTIFICATION_FRAUD_REJECTED = 'FRAUD_RISK_REJECTED';
    public const ORDER_NOTIFICATION_FRAUD_ACCEPTED = 'FRAUD_RISK_ACCEPTED';
    public const ORDER_NOTIFICATION_FRAUD_STOPPED  = 'FRAUD_RISK_STOPPED';

    /**
     * API allowed shipping method code
     */
    public const KLARNA_API_SHIPPING_METHOD_HOME = "Home";

    /**
     * @var DataObject
     */
    private $klarnaOrder;
    /**
     * @var OrdermanagementApi
     */
    private $orderManagement;
    /**
     * @var DataConverter
     */
    private $dataConverter;
    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;
    /**
     * @var Parameter
     */
    private $parameter;
    /**
     * @var Api
     */
    private Api $apiConfiguration;
    /**
     * @var Builder
     */
    private Builder $builder;

    /**
     * OrdermanagementApi constructor.
     *
     * @param OrdermanagementApi $orderManagement
     * @param DataConverter      $dataConverter
     * @param DataObjectFactory  $dataObjectFactory
     * @param Parameter          $parameter
     * @param Api                $apiConfiguration
     * @param Builder            $builder
     * @codeCoverageIgnore
     */
    public function __construct(
        OrdermanagementApi $orderManagement,
        DataConverter $dataConverter,
        DataObjectFactory $dataObjectFactory,
        Parameter $parameter,
        Api $apiConfiguration,
        Builder $builder
    ) {
        $this->orderManagement   = $orderManagement;
        $this->dataConverter     = $dataConverter;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->parameter         = $parameter;
        $this->apiConfiguration  = $apiConfiguration;
        $this->builder           = $builder;
    }

    /**
     * Get the fraud status of an order to determine if it should be accepted or denied within Magento
     *
     * Return value of 1 means accept
     * Return value of 0 means still pending
     * Return value of -1 means deny
     *
     * @param string $orderId
     *
     * @return int
     */
    public function getFraudStatus($orderId)
    {
        $klarnaOrder = $this->orderManagement->getOrder($orderId);
        $klarnaOrder = $this->dataObjectFactory->create(['data' => $klarnaOrder]);
        switch ($klarnaOrder->getFraudStatus()) {
            case self::ORDER_FRAUD_STATUS_ACCEPTED:
                return self::RET_ORDER_FRAUD_STATUS_ACCEPTED;
            case self::ORDER_FRAUD_STATUS_REJECTED:
                return self::RET_ORDER_FRAUD_STATUS_REJECTED;
            case self::ORDER_FRAUD_STATUS_PENDING:
            default:
                return self::RET_ORDER_FRAUD_STATUS_PENDING;
        }
    }

    /**
     * Acknowledge an order in order management
     *
     * @param string $orderId
     *
     * @return DataObject
     */
    public function acknowledgeOrder($orderId)
    {
        $response = $this->orderManagement->acknowledgeOrder($orderId);
        $response = $this->dataObjectFactory->create(['data' => $response]);
        return $response;
    }

    /**
     * Update merchant references for a Klarna order
     *
     * @param string $orderId
     * @param string $reference1
     * @param string|null $reference2
     *
     * @return DataObject
     */
    public function updateMerchantReferences($orderId, $reference1, $reference2 = null)
    {
        \file_put_contents('/tmp/gabor.log', "\n".__METHOD__, FILE_APPEND);
        \file_put_contents('/tmp/gabor.log', "\n"."  \$reference1 = ".var_export($reference1, true), FILE_APPEND);


        $response = $this->orderManagement->updateMerchantReferences($orderId, $reference1, $reference2);
        $response = $this->dataObjectFactory->create(['data' => $response]);
        return $response;
    }

    /**
     * Capture an amount on an order
     *
     * @param string           $orderId
     * @param float            $amount
     * @param InvoiceInterface $invoice
     *
     * @return DataObject
     * @throws LocalizedException
     * @throws \Klarna\Base\Exception
     * @throws \Klarna\Base\Model\Api\Exception
     */
    public function capture(string $orderId, float $amount, InvoiceInterface $invoice)
    {
        $data = $this->builder->getCaptureRequest($amount, $invoice);

        $invoiceId = $this->getInvoiceId($invoice);
        if ($invoiceId !== null) {
            $data['reference'] = $invoiceId;
        }

        $data = $this->setShippingDelay($data);

        $response = $this->orderManagement->captureOrder($orderId, $data);
        $response = $this->dataObjectFactory->create(['data' => $response]);

        /**
         * If a capture fails, attempt to extend the auth and attempt capture again.
         * This work in certain cases that cannot be detected via api calls
         */
        if (!$response->getIsSuccessful()) {
            $extendResponse = $this->orderManagement->extendAuthorization($orderId);
            $extendResponse = $this->dataObjectFactory->create(['data' => $extendResponse]);

            if ($extendResponse->getIsSuccessful()) {
                $response = $this->orderManagement->captureOrder($orderId, $data);
                $response = $this->dataObjectFactory->create(['data' => $response]);
            }
        }

        if ($response->getIsSuccessful()) {
            $responseObject = $response->getResponseObject();
            $captureId = $this->orderManagement
                ->getLocationResourceId($responseObject['headers']['Location']);

            if ($captureId) {
                $captureDetails = $this->orderManagement->getCapture($orderId, $captureId);
                $captureDetails = $this->dataObjectFactory->create(['data' => $captureDetails]);

                if ($captureDetails->getKlarnaReference()) {
                    $captureDetails->setTransactionId($captureDetails->getKlarnaReference());
                }
                return $captureDetails;
            }
        }

        return $response;
    }

    /**
     * Get all the captures for an order
     *
     * @param string $orderId
     * @return mixed
     */
    public function getCaptures(string $orderId)
    {
        $response = $this->orderManagement->getCaptures($orderId);
        $response = $this->dataObjectFactory->create(['data' => $response]);
        return $response;
    }

    /**
     * Check to see if the order already fully captured.
     *
     * @param float $orderAmount
     * @param string $klarnaOrderId
     * @return bool
     */
    public function isFullyCaptured($orderAmount, $klarnaOrderId): bool
    {
        $response = $this->getCaptures($klarnaOrderId);
        $orderCaptures = $response->getData()['response_object']['body'];

        $capturedAmount = 0;
        foreach ($orderCaptures as $capture) {
            $capturedAmount += $capture['captured_amount'];
        }

        return $this->dataConverter->toApiFloat($orderAmount) == $capturedAmount;
    }

    /**
     * Getting back the invoice id.
     * It is intended that this method is public so that merchants can hook into it and return an ID based on
     * their system.
     *
     * @param InvoiceInterface $invoice
     * @return string|null
     * @SuppressWarnings(PMD.UnusedFormalParameter)
     * @api
     */
    public function getInvoiceId(InvoiceInterface $invoice): ?string
    {
        return null;
    }

    /**
     * Add shipping info to capture
     *
     * @param string $orderId
     * @param string $captureId
     * @param array $shippingInfo
     * @return array|DataObject
     */
    public function addShippingInfo($orderId, $captureId, $shippingInfo)
    {
        $data = $this->prepareShippingInfo($shippingInfo);
        $response = $this->orderManagement->addShippingInfo($orderId, $captureId, $data);
        $response = $this->dataObjectFactory->create(['data' => $response]);
        return $response;
    }

    /**
     * Prepare shipping info
     *
     * @param array $shippingInfo
     * @return array
     */
    private function prepareShippingInfo(array $shippingInfo)
    {
        $data = [];
        foreach ($shippingInfo as $shipping) {
            $data['shipping_info'][] = [
                'tracking_number' => substr($shipping['number'], 0, 100),
                'shipping_method' => $this->getKlarnaShippingMethod($shipping),
                'shipping_company' => substr($shipping['title'], 0, 100)
            ];
        }

        return $data;
    }

    /**
     * Get Api Accepted shipping method,For merchant who implement this feature
     * Create Plugin to overwrite this default method code
     * Allowed values matches (PickUpStore|Home|BoxReg|BoxUnreg|PickUpPoint|Own)
     *
     * @param array $shipping
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getKlarnaShippingMethod(array $shipping)
    {
        return self::KLARNA_API_SHIPPING_METHOD_HOME;
    }

    /**
     * Set shipping delay for capture
     *
     * Change this setting when items will not be shipped for x amount of days after capture.
     * For instance, you capture on Friday but won't ship until Monday. A 3 day shipping delay would be set.
     *
     * @param array $data
     * @param int   $shippingDelay
     * @return array
     */
    public function setShippingDelay($data, $shippingDelay = 0)
    {
        if ($shippingDelay > 0) {
            $data['shipping_delay'] = $shippingDelay;
        }

        return $data;
    }

    /**
     * Refund for an order
     *
     * @param string              $orderId
     * @param float               $amount
     * @param CreditmemoInterface $creditMemo
     *
     * @return DataObject
     * @throws \Klarna\Base\Exception
     * @throws LocalizedException
     */
    public function refund(string $orderId, float $amount, CreditmemoInterface $creditMemo)
    {
        $data = $this->builder->getRefundRequest($amount, $creditMemo);

        $refundId = $this->getRefundId($creditMemo);
        if ($refundId !== null) {
            $data['reference'] = $refundId;
        }

        $response = $this->orderManagement->refund($orderId, $data);
        $response = $this->dataObjectFactory->create(['data' => $response]);

        if ($response->getIsSuccessful()) {
            $response->setTransactionId($this->orderManagement->getLocationResourceId($response));
        }

        return $response;
    }

    /**
     * Getting back the refund id.
     * It is intended that this method is public so that merchants can hook into it and return an ID based on
     * their system.
     *
     * @param CreditmemoInterface $creditmemo
     * @return string|null
     * @SuppressWarnings(PMD.UnusedFormalParameter)
     * @api
     */
    public function getRefundId(CreditmemoInterface $creditmemo): ?string
    {
        return null;
    }

    /**
     * Cancel an order
     *
     * @param string $orderId
     *
     * @return DataObject
     */
    public function cancel($orderId)
    {
        $response = $this->orderManagement->cancelOrder($orderId);
        $response = $this->dataObjectFactory->create(['data' => $response]);
        return $response;
    }

    /**
     * Release the authorization on an order
     *
     * @param string $orderId
     *
     * @return DataObject
     */
    public function release($orderId)
    {
        $response = $this->orderManagement->releaseAuthorization($orderId);
        $response = $this->dataObjectFactory->create(['data' => $response]);
        return $response;
    }

    /**
     * Get order details for a completed Klarna order
     *
     * @param string $orderId
     *
     * @return DataObject
     */
    public function getPlacedKlarnaOrder($orderId)
    {
        \file_put_contents('/tmp/gabor.log', "\n".__METHOD__, FILE_APPEND);
        \file_put_contents('/tmp/gabor.log', "\n"."  \$orderId = ".var_export($orderId, true), FILE_APPEND);

        $response = $this->orderManagement->getOrder($orderId);
        $response = $this->dataObjectFactory->create(['data' => $response]);
        return $response;
    }

    /**
     * Get Klarna Checkout Reservation Id
     *
     * @return string
     */
    public function getReservationId()
    {
        \file_put_contents('/tmp/gabor.log', "\n".__METHOD__, FILE_APPEND);

        return $this->getKlarnaOrder()->getOrderId();
    }

    /**
     * Get Klarna checkout order details
     *
     * @return DataObject
     */
    public function getKlarnaOrder()
    {
        if ($this->klarnaOrder === null) {
            $this->klarnaOrder = $this->dataObjectFactory->create();
        }

        return $this->klarnaOrder;
    }

    /**
     * @inheritdoc
     */
    public function resetForStore($store, $methodCode, string $currency)
    {
        $user = $this->apiConfiguration->getUserName($store, $currency);
        $password = $this->apiConfiguration->getPassword($store, $currency);
        $url = $this->apiConfiguration->getApiUrl($store, $currency);
        $this->orderManagement->resetForStore($user, $password, $url);
        return $this;
    }
}
