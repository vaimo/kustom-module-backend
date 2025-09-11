<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Backend\Gateway\Command;

use Magento\Payment\Gateway\Command;

/**
 * @internal
 */
class FetchTransactionInfo extends AbstractCommand
{
    public const ACCEPT  = 1;
    public const DENY    = -1;

    /**
     * FetchTransactionInfo command
     *
     * @param array $commandSubject
     *
     * @return null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(array $commandSubject)
    {
        /** @var \Magento\Payment\Model\InfoInterface $payment */
        $payment = $commandSubject['payment']->getPayment();
        $order = $payment->getOrder();

        $klarnaOrder = $this->klarnaOrderRepository->getByOrder($order);
        $transactionId = $klarnaOrder->getReservationId();

        $orderStatus = $this->getOmApi($order)->getFraudStatus($transactionId);

        if ($orderStatus === self::ACCEPT) {
            $payment->setIsTransactionApproved(true);
        } elseif ($orderStatus === self::DENY) {
            $payment->setIsTransactionDenied(true);
            $payment->getAuthorizationTransaction()->closeAuthorization();
        }

        return null;
    }
}
