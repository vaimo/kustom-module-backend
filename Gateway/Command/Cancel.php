<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Backend\Gateway\Command;

use Klarna\Base\Api\OrderInterface as KlarnaOrderInterface;
use Klarna\Base\Exception as KlarnaException;
use Magento\Payment\Gateway\Command;
use Magento\Sales\Api\Data\OrderInterface as MageOrderInterface;

/**
 * @internal
 */
class Cancel extends AbstractCommand
{
    /**
     * Cancel command
     *
     * @param array $commandSubject
     *
     * @return null|Command\ResultInterface
     * @throws \Klarna\Base\Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(array $commandSubject)
    {
        /** @var \Magento\Payment\Model\InfoInterface $payment */
        $payment = $commandSubject['payment']->getPayment();
        /** @var \Magento\Sales\Api\Data\OrderInterface $magentoOrder */
        $magentoOrder = $payment->getOrder();
        $klarnaOrder = $this->getKlarnaOrder($magentoOrder);

        $this
            ->getValidator()
            ->checkRequestSendable(
                $klarnaOrder,
                $magentoOrder,
                $this->getValidator()::ACTION_TYPE_CANCEL
            );
        $response = $this->getOmApi($magentoOrder)
            ->cancel($klarnaOrder->getReservationId());
        $this->getValidator()->checkApiResponse(
            $klarnaOrder,
            $response,
            $magentoOrder,
            $this->getValidator()::ACTION_TYPE_CANCEL
        );

        return null;
    }
}
