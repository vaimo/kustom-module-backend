<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Backend\Gateway\Command;

use Klarna\Base\Exception as KlarnaException;
use Magento\Payment\Gateway\Command;

/**
 * @internal
 */
class Refund extends AbstractCommand
{
    /**
     * Refund command
     *
     * @param array $commandSubject
     *
     * @return null|Command\ResultInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(array $commandSubject)
    {
        /** @var \Magento\Payment\Model\InfoInterface $payment */
        $payment = $commandSubject['payment']->getPayment();
        $amount = (float) $commandSubject['amount'];
        $magentoOrder = $payment->getOrder();
        $klarnaOrder = $this->getKlarnaOrder($magentoOrder);

        $this
            ->getValidator()
            ->checkRequestSendable(
                $klarnaOrder,
                $magentoOrder,
                $this->getValidator()::ACTION_TYPE_REFUND
            );
        $response = $this->getOmApi($magentoOrder)
            ->refund($klarnaOrder->getReservationId(), $amount, $payment->getCreditmemo());
        $this->getValidator()->checkApiResponse(
            $klarnaOrder,
            $response,
            $magentoOrder,
            $this->getValidator()::ACTION_TYPE_REFUND
        );

        return null;
    }
}
