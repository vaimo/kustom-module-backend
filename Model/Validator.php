<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Backend\Model;

use Klarna\Base\Api\OrderInterface as KlarnaOrder;
use Klarna\Base\Exception as KlarnaException;
use Klarna\Logger\Model\Logger;
use Magento\Framework\Message\ManagerInterface;
use Magento\Sales\Api\Data\OrderInterface as MagentoOrder;
use Magento\Framework\DataObject;

/**
 * @internal
 */
class Validator
{

    public const ACTION_TYPE_CANCEL = 'cancel';
    public const ACTION_TYPE_CAPTURE = 'capture';
    public const ACTION_TYPE_REFUND = 'refund';

    /**
     * @var Logger
     */
    private Logger $logger;

    /**
     * @param Logger $logger
     * @codeCoverageIgnore
     */
    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Returns true if the Klarna order is valid
     *
     * @param KlarnaOrder $klarnaOrder
     * @param MagentoOrder $magentoOrder
     * @param string $actionType
     * @return bool
     * @throws KlarnaException
     */
    public function checkRequestSendable(KlarnaOrder $klarnaOrder, MagentoOrder $magentoOrder, string $actionType): bool
    {
        $message = '';
        $isValid = true;

        if (!$klarnaOrder->getId()) {
            $isValid = false;
            $message .= 'A invalid database entry was found for the Klarna order.';
        }
        if (!$klarnaOrder->getReservationId()) {
            $isValid = false;
            $message .= 'No Klarna reservation/order ID is given.';
        }

        if (!$isValid) {
            $messageFormat = 'The Klarna API order management %s request will not be send to %s the payment ' .
                'for the Klarna order ID %s and Magento order ID %s because: %s ';
            $fullMessage = sprintf(
                $messageFormat,
                $actionType,
                $actionType,
                $klarnaOrder->getKlarnaOrderId(),
                $magentoOrder->getIncrementId(),
                $message
            );

            $this->logger->setStore($magentoOrder->getStore());
            $this->logAndThrowError($fullMessage);
        }

        return true;
    }

    /**
     * Returns true if the Klarna API request was successful
     *
     * @param KlarnaOrder $klarnaOrder
     * @param DataObject $klarnaResponse
     * @param MagentoOrder $magentoOrder
     * @param string $actionType
     * @return bool
     * @throws KlarnaException
     */
    public function checkApiResponse(
        KlarnaOrder $klarnaOrder,
        DataObject $klarnaResponse,
        MagentoOrder $magentoOrder,
        string $actionType
    ): bool {
        if (!$klarnaResponse->getIsSuccessful()) {
            $messageFormat = 'The Klarna API order management %s request for the Klarna order ID %s ' .
                'and Magento order ID %s failed. Reason: %s';
            $errorMessage = sprintf(
                $messageFormat,
                $actionType,
                $klarnaOrder->getKlarnaOrderId(),
                $magentoOrder->getIncrementId(),
                implode(',', $klarnaResponse->getErrorMessages())
            );

            $this->logger->setStore($magentoOrder->getStore());
            $this->logAndThrowError($errorMessage);
        }

        return true;
    }

    /**
     * Throwing a error
     *
     * @param string $errorMessage
     * @throws KlarnaException
     */
    private function logAndThrowError(string $errorMessage): void
    {
        $this->logger->critical($errorMessage);
        throw new KlarnaException(__($errorMessage));
    }
}
