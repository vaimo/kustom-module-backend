<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Backend\Model\Api\Rest\Service;

use Klarna\Base\Api\ServiceInterface;
use Klarna\Base\Helper\VersionInfo;
use Magento\Framework\DataObject;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @internal
 */
class Ordermanagement
{
    public const API_VERSION = 'v1';

    public const ACTIONS = [
        'acknowledge_order'               => 'Acknowledge order',
        'get_order'                       => 'Get order',
        'extend_authorization'            => 'Extend authorization',
        'update_merchant_references'      => 'Update merchant references',
        'add_shipping_info'               => 'Add shipping info',
        'cancel_order'                    => 'Cancel order',
        'capture_order'                   => 'Capture order',
        'get_all_captures'                => 'Get all captures',
        'get_capture'                     => 'Get capture',
        'refund'                          => 'Refund',
        'release_authorization'           => 'Release authorization',
    ];

    /**
     * @var ServiceInterface
     */
    private $service;

    /**
     * Initialize class
     *
     * @param ServiceInterface $service
     * @param VersionInfo      $versionInfo
     * @codeCoverageIgnore
     */
    public function __construct(
        ServiceInterface $service,
        VersionInfo $versionInfo
    ) {
        $this->service = $service;

        $version = sprintf(
            '%s;%s;Core/%s',
            $versionInfo->getFullM2KlarnaVersion(),
            $versionInfo->getVersion('Klarna_Backend'),
            $versionInfo->getVersion('Klarna_Base')
        );

        $mageInfo = $versionInfo->getMageInfo();
        $this->service->setUserAgent('Magento2_OM', $version, $mageInfo);
        $this->service->setHeader('Accept', '*/*');
    }

    /**
     * Setup connection based on store config
     *
     * @param string $user
     * @param string $password
     * @param string $url
     */
    public function resetForStore(string $user, string $password, string $url)
    {
        $this->service->connect($user, $password, $url);
    }

    /**
     * Used by merchants to acknowledge the order.
     *
     * Merchants will receive the order confirmation push until the order has been acknowledged.
     *
     * @param string $orderId
     * @return array
     */
    public function acknowledgeOrder(string $orderId)
    {
        $action = self::ACTIONS['acknowledge_order'];
        $url    = "/ordermanagement/" . self::API_VERSION . "/orders/{$orderId}/acknowledge";
        return $this->service->makeRequest(
            $url,
            ServiceInterface::SERVICE_OM,
            [],
            ServiceInterface::POST,
            $orderId,
            $action
        );
    }

    /**
     * Get the current state of an order
     *
     * @param string $orderId
     * @return array
     */
    public function getOrder(string $orderId)
    {
        $action = self::ACTIONS['get_order'];
        $url    = "/ordermanagement/" . self::API_VERSION . "/orders/{$orderId}";
        return $this->service->makeRequest(
            $url,
            ServiceInterface::SERVICE_OM,
            [],
            ServiceInterface::GET,
            $orderId,
            $action
        );
    }

    /**
     * Extend the order's authorization by default period according to merchant contract.
     *
     * @param string $orderId
     * @return array
     */
    public function extendAuthorization(string $orderId)
    {
        $action = self::ACTIONS['extend_authorization'];
        $url    = "/ordermanagement/" . self::API_VERSION . "/orders/{$orderId}/extend-authorization-time";
        return $this->service->makeRequest(
            $url,
            ServiceInterface::SERVICE_OM,
            [],
            ServiceInterface::POST,
            $orderId,
            $action
        );
    }

    /**
     * Update one or both merchant references. To clear a reference, set its value to "" (empty string).
     *
     * @param string $orderId
     * @param string $merchantReference1
     * @param string|null $merchantReference2
     * @return array
     */
    public function updateMerchantReferences(
        string $orderId,
        string $merchantReference1,
        ?string $merchantReference2 = null
    ) {
        $action = self::ACTIONS['update_merchant_references'];
        $url    = "/ordermanagement/" . self::API_VERSION . "/orders/{$orderId}/merchant-references";

        $data = [
            'merchant_reference1' => $merchantReference1
        ];

        if ($merchantReference2 !== null) {
            $data['merchant_reference2'] = $merchantReference2;
        }
        return $this->service->makeRequest(
            $url,
            ServiceInterface::SERVICE_OM,
            $data,
            ServiceInterface::PATCH,
            $orderId,
            $action
        );
    }

    /**
     * Add shipping info to capture
     *
     * @param string $orderId
     * @param string $captureId
     * @param array  $data
     * @return array
     */
    public function addShippingInfo(string $orderId, string $captureId, array $data)
    {
        $action = self::ACTIONS['add_shipping_info'];
        $url    = "/ordermanagement/" . self::API_VERSION . "/orders/{$orderId}/captures/{$captureId}/shipping-info";
        return $this->service->makeRequest(
            $url,
            ServiceInterface::SERVICE_OM,
            $data,
            ServiceInterface::POST,
            $orderId,
            $action
        );
    }

    /**
     * Cancel an authorized order. For a cancellation to be successful, there must be no captures on the order.
     *
     * The authorized amount will be released and no further updates to the order will be allowed.
     *
     * @param string $orderId
     * @return array
     */
    public function cancelOrder(string $orderId)
    {
        $action = self::ACTIONS['cancel_order'];
        $url    = "/ordermanagement/" . self::API_VERSION . "/orders/{$orderId}/cancel";
        return $this->service->makeRequest(
            $url,
            ServiceInterface::SERVICE_OM,
            [],
            ServiceInterface::POST,
            $orderId,
            $action
        );
    }

    /**
     * Capture the supplied amount. Use this call when fulfillment is completed, e.g. physical goods are being shipped
     * to the customer.
     * 'captured_amount' must be equal to or less than the order's 'remaining_authorized_amount'.
     * Shipping address is inherited from the order. Use PATCH method below to update the shipping address of an
     * individual capture. The capture amount can optionally be accompanied by a descriptive text and order lines for
     * the captured items.
     *
     * @param string $orderId
     * @param array $data
     * @return array
     * @throws \Klarna\Base\Model\Api\Exception
     */
    public function captureOrder(string $orderId, array $data)
    {
        $action = self::ACTIONS['capture_order'];
        $url    = "/ordermanagement/" . self::API_VERSION . "/orders/{$orderId}/captures";
        return $this->service->makeRequest(
            $url,
            ServiceInterface::SERVICE_OM,
            $data,
            ServiceInterface::POST,
            $orderId,
            $action
        );
    }

    /**
     * Retrieve all captures
     *
     * @param string $orderId
     * @return array
     */
    public function getCaptures(string $orderId): array
    {
        $action = self::ACTIONS['get_all_captures'];
        $url    = "/ordermanagement/" . self::API_VERSION . "/orders/{$orderId}/captures";
        return $this->service->makeRequest(
            $url,
            ServiceInterface::SERVICE_OM,
            [],
            ServiceInterface::GET,
            $orderId,
            $action
        );
    }

    /**
     * Retrieve a capture
     *
     * @param string $orderId
     * @param string $captureId
     * @return array
     */
    public function getCapture(string $orderId, string $captureId)
    {
        $action = self::ACTIONS['get_capture'];
        $url    = "/ordermanagement/" . self::API_VERSION . "/orders/{$orderId}/captures/{$captureId}";
        return $this->service->makeRequest(
            $url,
            ServiceInterface::SERVICE_OM,
            [],
            ServiceInterface::GET,
            $orderId,
            $action
        );
    }

    /**
     * Refund an amount of a captured order. The refunded amount will be credited to the customer.
     * The refunded amount must not be higher than 'captured_amount'.
     * The refunded amount can optionally be accompanied by a descriptive text and order lines.
     *
     * @param string $orderId
     * @param array $data
     * @return array
     */
    public function refund(string $orderId, array $data)
    {
        $action = self::ACTIONS['refund'];
        $url    = "/ordermanagement/" . self::API_VERSION . "/orders/{$orderId}/refunds";
        return $this->service->makeRequest(
            $url,
            ServiceInterface::SERVICE_OM,
            $data,
            ServiceInterface::POST,
            $orderId,
            $action
        );
    }

    /**
     * Signal that there is no intention to perform further captures.
     *
     * @param string $orderId
     * @return array
     */
    public function releaseAuthorization(string $orderId)
    {
        $action = self::ACTIONS['release_authorization'];
        $url    = "/ordermanagement/" . self::API_VERSION . "/orders/{$orderId}/release-remaining-authorization";
        return $this->service->makeRequest(
            $url,
            ServiceInterface::SERVICE_OM,
            [],
            ServiceInterface::POST,
            $orderId,
            $action
        );
    }

    /**
     * Get resource id from Location URL
     *
     * This assumes the ID is the last url path
     *
     * @param string|array|DataObject $location
     * @return string
     */
    public function getLocationResourceId($location)
    {
        if ($location instanceof DataObject) {
            $responseObject = $location->getResponseObject();
            $location = $responseObject['headers']['Location'];
        }
        if (is_array($location)) {
            $location = array_shift($location);
        }

        $location = rtrim($location, '/');
        $locationArr = explode('/', $location);
        return array_pop($locationArr);
    }
}
