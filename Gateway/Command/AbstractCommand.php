<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Backend\Gateway\Command;

use Klarna\Backend\Api\ApiInterface;
use Klarna\Backend\Model\Api\Factory;
use Klarna\Backend\Model\Api\OrderManagement;
use Klarna\Backend\Model\Validator;
use Klarna\Base\Api\OrderInterface as KlarnaOrderInterface;
use Klarna\Base\Exception;
use Klarna\Base\Helper\KlarnaConfig;
use Klarna\Base\Model\OrderRepository as KlarnaOrderRepository;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Payment\Gateway\Command\ResultInterface;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Quote\Model\QuoteRepository as MageQuoteRepository;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\OrderRepository as MageOrderRepository;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @internal
 */
abstract class AbstractCommand extends DataObject implements CommandInterface
{
    /**
     * @var KlarnaOrderRepository
     */
    public $klarnaOrderRepository;
    /**
     * @var OrderManagement
     */
    private $om;
    /**
     * @var array
     */
    public $omCache = [];
    /**
     * @var MageQuoteRepository
     */
    public $mageQuoteRepository;
    /**
     * @var MageOrderRepository
     */
    public $mageOrderRepository;
    /**
     * @var KlarnaConfig
     */
    public $helper;
    /**
     * @var Factory
     */
    public $omFactory;
    /**
     * @var int
     */
    private $omConnectionStoreId;
    /**
     * @var Validator
     */
    private Validator $validator;

    /**
     * AbstractCommand constructor.
     *
     * @param KlarnaOrderRepository $klarnaOrderRepository
     * @param MageQuoteRepository   $mageQuoteRepository
     * @param MageOrderRepository   $mageOrderRepository
     * @param KlarnaConfig          $helper
     * @param Factory               $omFactory
     * @param Validator             $validator
     * @param array                 $data
     * @codeCoverageIgnore
     */
    public function __construct(
        KlarnaOrderRepository $klarnaOrderRepository,
        MageQuoteRepository $mageQuoteRepository,
        MageOrderRepository $mageOrderRepository,
        KlarnaConfig $helper,
        Factory $omFactory,
        Validator $validator,
        array $data = []
    ) {
        parent::__construct($data);
        $this->klarnaOrderRepository = $klarnaOrderRepository;
        $this->mageQuoteRepository = $mageQuoteRepository;
        $this->mageOrderRepository = $mageOrderRepository;
        $this->helper = $helper;
        $this->omFactory = $omFactory;
        $this->validator = $validator;
    }

    /**
     * AbstractCommand command
     *
     * @param array $commandSubject
     *
     * @return null|ResultInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    abstract public function execute(array $commandSubject);

    /**
     * Get a Klarna order
     *
     * @param OrderInterface $order
     *
     * @return KlarnaOrderInterface
     * @throws NoSuchEntityException
     */
    public function getKlarnaOrder(OrderInterface $order): KlarnaOrderInterface
    {
        return $this->klarnaOrderRepository->getByOrder($order);
    }

    /**
     * Get api class
     *
     * @param OrderInterface $order
     * @return ApiInterface
     * @throws Exception
     * @throws LocalizedException
     */
    public function getOmApi(OrderInterface $order): ApiInterface
    {
        $store = $order->getStore();
        if ($this->omConnectionStoreId !== $store->getId()) {
            $this->omConnectionStoreId = $store->getId();
            $this->om = $this->omFactory->createOmApi(
                $order->getPayment()->getMethod(),
                $order->getOrderCurrencyCode(),
                $store
            );
        }

        return $this->om;
    }

    /**
     * Get validator
     *
     * @return Validator
     */
    public function getValidator(): Validator
    {
        return $this->validator;
    }
}
