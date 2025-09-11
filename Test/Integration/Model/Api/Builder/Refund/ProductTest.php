<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Backend\Test\Integration\Model\Api\Builder\Refund\Product;

use Klarna\Base\Test\Integration\Helper\RequestBuilderTestCase;

/**
 * @internal
 */
class ProductTest extends RequestBuilderTestCase
{
    /**
     * @magentoDataFixture Klarna_Base::Test/Integration/_files/fixtures/invoice_setup1_single_simple_product.php
     *
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testSingleSimpleProductForShopSetup1()
    {
        $creditMemo = $this->getCreditMemoByOrderIncrementId('100000001');
        $request = $this->getRefundRequest(15.75, $creditMemo);

        $this->postPurchaseValidator->performAllGeneralUsChecks($request, 1575, $creditMemo);
    }

    /**
     * @magentoDataFixture Klarna_Base::Test/Integration/_files/fixtures/invoice_setup1_several_simple_product.php
     *
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testSeveralSimpleProductForShopSetup1()
    {
        $creditMemo = $this->getCreditMemoByOrderIncrementId('100000001');
        $request = $this->getRefundRequest(71.60, $creditMemo);

        $this->postPurchaseValidator->performAllGeneralUsChecks($request, 7160, $creditMemo);
    }

    /**
     * @magentoDataFixture Klarna_Base::Test/Integration/_files/fixtures/invoice_setup1_single_virtual_product.php
     *
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testSingleVirtualProductForShopSetup1()
    {
        $creditMemo = $this->getCreditMemoByOrderIncrementId('100000001');
        $request = $this->getRefundRequest(10.75, $creditMemo);

        $this->postPurchaseValidator->performAllGeneralUsChecks($request, 1075, $creditMemo);
    }

    /**
     * @magentoDataFixture Klarna_Base::Test/Integration/_files/fixtures/invoice_setup1_several_virtual_product.php
     *
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testSeveralVirtualProductForShopSetup1()
    {
        $creditMemo = $this->getCreditMemoByOrderIncrementId('100000001');
        $request = $this->getRefundRequest(21.50, $creditMemo);

        $this->postPurchaseValidator->performAllGeneralUsChecks($request, 2150, $creditMemo);
    }

    /**
     * @magentoDataFixture Klarna_Base::Test/Integration/_files/fixtures/invoice_setup1_single_downloadable_product.php
     *
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testSingleDownloadableProductForShopSetup1()
    {
        $creditMemo = $this->getCreditMemoByOrderIncrementId('100000001');
        $request = $this->getRefundRequest(10.75, $creditMemo);

        $this->postPurchaseValidator->performAllGeneralUsChecks($request, 1075, $creditMemo);
    }

    /**
     * @magentoDataFixture Klarna_Base::Test/Integration/_files/fixtures/invoice_setup1_several_downloadable_product.php
     *
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testSeveralDownloadableProductForShopSetup1()
    {
        $creditMemo = $this->getCreditMemoByOrderIncrementId('100000001');
        $request = $this->getRefundRequest(21.50, $creditMemo);

        $this->postPurchaseValidator->performAllGeneralUsChecks($request, 2150, $creditMemo);
    }

    /**
     * @magentoDataFixture Klarna_Base::Test/Integration/_files/fixtures/invoice_setup1_single_grouped_product.php
     *
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testSingleGroupedProductForShopSetup1()
    {
        $creditMemo = $this->getCreditMemoByOrderIncrementId('100000001');
        $request = $this->getRefundRequest(17.90, $creditMemo);

        $this->postPurchaseValidator->performAllGeneralUsChecks($request, 1790, $creditMemo);
    }

    /**
     * @magentoDataFixture Klarna_Base::Test/Integration/_files/fixtures/invoice_setup1_several_grouped_product.php
     *
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testSeveralGroupedProductForShopSetup1()
    {
        $creditMemo = $this->getCreditMemoByOrderIncrementId('100000001');
        $request = $this->getRefundRequest(35.80, $creditMemo);

        $this->postPurchaseValidator->performAllGeneralUsChecks($request, 3580, $creditMemo);
    }

    /**
     * @magentoDataFixture Klarna_Base::Test/Integration/_files/fixtures/invoice_setup1_single_configurable_product.php
     *
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testSingleConfigurableProductForShopSetup1()
    {
        $creditMemo = $this->getCreditMemoByOrderIncrementId('100000001');
        $request = $this->getRefundRequest(20.05, $creditMemo);

        $this->postPurchaseValidator->performAllGeneralUsChecks($request, 2005, $creditMemo);
    }

    /**
     * @magentoDataFixture Klarna_Base::Test/Integration/_files/fixtures/invoice_setup1_several_configurable_product.php
     *
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testSeveralConfigurableProductForShopSetup1()
    {
        $creditMemo = $this->getCreditMemoByOrderIncrementId('100000001');
        $request = $this->getRefundRequest(40.10, $creditMemo);

        $this->postPurchaseValidator->performAllGeneralUsChecks($request, 4010, $creditMemo);
    }

    /**
     * @magentoDataFixture Klarna_Base::Test/Integration/_files/fixtures/invoice_setup1_single_bundled_fixed_product.php
     *
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testSingleBundledFixedProductForShopSetup1()
    {
        $creditMemo = $this->getCreditMemoByOrderIncrementId('100000001');
        $request = $this->getRefundRequest(18.71, $creditMemo);

        $this->postPurchaseValidator->performAllGeneralUsChecks($request, 1871, $creditMemo);
    }

    /**
     * @magentoDataFixture Klarna_Base::Test/Integration/_files/fixtures/invoice_setup1_several_bundled_fixed_product.php
     *
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testSeveralBundledFixedProductForShopSetup1()
    {
        $creditMemo = $this->getCreditMemoByOrderIncrementId('100000001');
        $request = $this->getRefundRequest(37.41, $creditMemo);

        $this->postPurchaseValidator->performAllGeneralUsChecks($request, 3741, $creditMemo);
    }

    /**
     * @magentoDataFixture Klarna_Base::Test/Integration/_files/fixtures/invoice_setup1_single_bundled_dynamic_product.php
     *
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testSingleBundledDynamicProductForShopSetup1()
    {
        $creditMemo = $this->getCreditMemoByOrderIncrementId('100000001');
        $request = $this->getRefundRequest(25.43, $creditMemo);

        $this->postPurchaseValidator->performAllGeneralUsChecks($request, 2543, $creditMemo);
    }

    /**
     * @magentoDataFixture Klarna_Base::Test/Integration/_files/fixtures/invoice_setup1_several_bundled_dynamic_product.php
     *
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testSeveralBundledDynamicProductForShopSetup1()
    {
        $creditMemo = $this->getCreditMemoByOrderIncrementId('100000001');
        $request = $this->getRefundRequest(27.20, $creditMemo);

        $this->postPurchaseValidator->performAllGeneralUsChecks($request, 2720, $creditMemo);
    }
}