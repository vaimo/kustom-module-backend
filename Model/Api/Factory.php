<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Backend\Model\Api;

use Magento\Store\Api\Data\StoreInterface;
use Klarna\Backend\Api\ApiInterface;
use Magento\Framework\Exception\LocalizedException;
use Klarna\Backend\Model\Api\OrderManagement;

/**
 * Creating om api objects and returning them
 *
 * @api
 */
class Factory
{
    /**
     * @var OrderManagement
     */
    private OrderManagement $ordermanagement;

    /**
     * @param OrderManagement        $ordermanagement
     * @codeCoverageIgnore
     */
    public function __construct(
        OrderManagement $ordermanagement
    ) {
        $this->ordermanagement = $ordermanagement;
    }

    /**
     * Creating and returning the ordermanagement api instance
     *
     * @param string              $methodCode
     * @param string              $currency
     * @param StoreInterface|null $store
     * @return ApiInterface
     * @throws LocalizedException
     */
    public function createOmApi(string $methodCode, string $currency, ?StoreInterface $store = null): ApiInterface
    {
        return $this->ordermanagement->resetForStore($store, $methodCode, $currency);
    }
}
