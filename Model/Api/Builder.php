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
use Klarna\Orderlines\Model\Container\Parameter;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\CreditmemoInterface;

/**
 * @internal
 */
class Builder
{

    public const NOTE_LENGTH = 255;
    /**
     * @var DataConverter
     */
    private DataConverter $dataConverter;
    /**
     * @var Parameter
     */
    private Parameter $parameter;

    /**
     * @param DataConverter $dataConverter
     * @param Parameter $parameter
     * @codeCoverageIgnore
     */
    public function __construct(DataConverter $dataConverter, Parameter $parameter)
    {
        $this->dataConverter = $dataConverter;
        $this->parameter = $parameter;
    }

    /**
     * Getting back the capture request
     *
     * @param float $amount
     * @param InvoiceInterface $invoice
     * @return array
     * @throws \Klarna\Base\Exception
     */
    public function getCaptureRequest(float $amount, InvoiceInterface $invoice)
    {
        $data = [
            'captured_amount' => $this->dataConverter->toApiFloat($amount)
        ];

        return $this->prepareOrderLines($data, $invoice);
    }

    /**
     * Getting back the refund request
     *
     * @param float $amount
     * @param CreditmemoInterface $creditMemo
     * @return array
     * @throws \Klarna\Base\Exception
     */
    public function getRefundRequest(float $amount, CreditmemoInterface $creditMemo)
    {
        $data = [
            'refunded_amount' => $this->dataConverter->toApiFloat($amount)
        ];

        if ($creditMemo->getCustomerNote() !== null) {
            $note = $creditMemo->getCustomerNote();
            if (strlen($note) > self::NOTE_LENGTH) {
                $note = substr($note, 0, self::NOTE_LENGTH);
                $note .= '...';
            }
            $data['description'] = $note;
        }

        return $this->prepareOrderLines($data, $creditMemo);
    }

    /**
     * Preparing the orderlines
     *
     * @param array                                     $data
     * @param InvoiceInterface|CreditmemoInterface|null $document
     * @return array
     * @throws LocalizedException
     * @throws \Klarna\Base\Exception
     */
    private function prepareOrderLines(array $data, $document = null)
    {
        $orderItems = null;
        if ($document instanceof InvoiceInterface) {
            $this->parameter->getOrderLineProcessor()
                ->processByInvoice($this->parameter, $document);
            $orderItems = $this->parameter->getOrderLines();
        } elseif ($document instanceof CreditmemoInterface) {
            $this->parameter->getOrderLineProcessor()
                ->processByCreditMemo($this->parameter, $document);
            $orderItems = $this->parameter->getOrderLines();
        }

        /**
         * Without this line the order line items can be messed up when performing a bulk capture since there is a
         * chance that items of the previous request will be used on the current request.
         */
        $this->parameter->resetOrderLines();

        if ($orderItems) {
            $data['order_lines'] = $orderItems;
        }
        return $data;
    }
}
