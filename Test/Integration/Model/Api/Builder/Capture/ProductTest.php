<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Backend\Test\Integration\Model\Api\Builder\Capture\Product;

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
        $invoice = $this->getInvoiceByOrderIncrementId('100000001');
        $request = $this->getCaptureRequest(15.75, $invoice);

        $this->postPurchaseValidator->performAllGeneralUsChecks($request, 1575, $invoice);
    }

    /**
     * @magentoDataFixture Klarna_Base::Test/Integration/_files/fixtures/invoice_setup1_several_simple_product.php
     *
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testSeveralSimpleProductForShopSetup1()
    {
        $invoice = $this->getInvoiceByOrderIncrementId('100000001');
        $request = $this->getCaptureRequest(71.60, $invoice);

        $this->postPurchaseValidator->performAllGeneralUsChecks($request, 7160, $invoice);
    }

    /**
     * @magentoDataFixture Klarna_Base::Test/Integration/_files/fixtures/invoice_setup1_single_virtual_product.php
     *
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testSingleVirtualProductForShopSetup1()
    {
        $invoice = $this->getInvoiceByOrderIncrementId('100000001');
        $request = $this->getCaptureRequest(10.75, $invoice);

        $this->postPurchaseValidator->performAllGeneralUsChecks($request, 1075, $invoice);
    }

    /**
     * @magentoDataFixture Klarna_Base::Test/Integration/_files/fixtures/invoice_setup1_several_virtual_product.php
     *
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testSeveralVirtualProductForShopSetup1()
    {
        $invoice = $this->getInvoiceByOrderIncrementId('100000001');
        $request = $this->getCaptureRequest(21.50, $invoice);

        $this->postPurchaseValidator->performAllGeneralUsChecks($request, 2150, $invoice);
    }

    /**
     * @magentoDataFixture Klarna_Base::Test/Integration/_files/fixtures/invoice_setup1_single_downloadable_product.php
     *
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testSingleDownloadableProductForShopSetup1()
    {
        $invoice = $this->getInvoiceByOrderIncrementId('100000001');
        $request = $this->getCaptureRequest(10.75, $invoice);

        $this->postPurchaseValidator->performAllGeneralUsChecks($request, 1075, $invoice);
    }

    /**
     * @magentoDataFixture Klarna_Base::Test/Integration/_files/fixtures/invoice_setup1_several_downloadable_product.php
     *
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testSeveralDownloadableProductForShopSetup1()
    {
        $invoice = $this->getInvoiceByOrderIncrementId('100000001');
        $request = $this->getCaptureRequest(21.50, $invoice);

        $this->postPurchaseValidator->performAllGeneralUsChecks($request, 2150, $invoice);
    }

    /**
     * @magentoDataFixture Klarna_Base::Test/Integration/_files/fixtures/invoice_setup1_single_grouped_product.php
     *
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testSingleGroupedProductForShopSetup1()
    {
        $invoice = $this->getInvoiceByOrderIncrementId('100000001');
        $request = $this->getCaptureRequest(17.90, $invoice);

        $this->postPurchaseValidator->performAllGeneralUsChecks($request, 1790, $invoice);
    }

    /**
     * @magentoDataFixture Klarna_Base::Test/Integration/_files/fixtures/invoice_setup1_several_grouped_product.php
     *
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testSeveralGroupedProductForShopSetup1()
    {
        $invoice = $this->getInvoiceByOrderIncrementId('100000001');
        $request = $this->getCaptureRequest(35.80, $invoice);

        $this->postPurchaseValidator->performAllGeneralUsChecks($request, 3580, $invoice);
    }

    /**
     * @magentoDataFixture Klarna_Base::Test/Integration/_files/fixtures/invoice_setup1_single_configurable_product.php
     *
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testSingleConfigurableProductForShopSetup1()
    {
        $invoice = $this->getInvoiceByOrderIncrementId('100000001');
        $request = $this->getCaptureRequest(20.05, $invoice);

        $this->postPurchaseValidator->performAllGeneralUsChecks($request, 2005, $invoice);
    }

    /**
     * @magentoDataFixture Klarna_Base::Test/Integration/_files/fixtures/invoice_setup1_several_configurable_product.php
     *
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testSeveralConfigurableProductForShopSetup1()
    {
        $invoice = $this->getInvoiceByOrderIncrementId('100000001');
        $request = $this->getCaptureRequest(40.10, $invoice);

        $this->postPurchaseValidator->performAllGeneralUsChecks($request, 4010, $invoice);
    }

    /**
     * @magentoDataFixture Klarna_Base::Test/Integration/_files/fixtures/invoice_setup1_single_bundled_fixed_product.php
     *
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testSingleBundledFixedProductForShopSetup1()
    {
        $invoice = $this->getInvoiceByOrderIncrementId('100000001');
        $request = $this->getCaptureRequest(18.71, $invoice);

        $this->postPurchaseValidator->performAllGeneralUsChecks($request, 1871, $invoice);
    }

    /**
     * @magentoDataFixture Klarna_Base::Test/Integration/_files/fixtures/invoice_setup1_several_bundled_fixed_product.php
     *
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testSeveralBundledFixedProductForShopSetup1()
    {
        $invoice = $this->getInvoiceByOrderIncrementId('100000001');
        $request = $this->getCaptureRequest(37.41, $invoice);

        $this->postPurchaseValidator->performAllGeneralUsChecks($request, 3741, $invoice);
    }

    /**
     * @magentoDataFixture Klarna_Base::Test/Integration/_files/fixtures/invoice_setup1_single_bundled_dynamic_product.php
     *
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testSingleBundledDynamicProductForShopSetup1()
    {
        $invoice = $this->getInvoiceByOrderIncrementId('100000001');
        $request = $this->getCaptureRequest(25.43, $invoice);

        $this->postPurchaseValidator->performAllGeneralUsChecks($request, 2543, $invoice);
    }

    /**
     * @magentoDataFixture Klarna_Base::Test/Integration/_files/fixtures/invoice_setup1_several_bundled_dynamic_product.php
     *
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testSeveralBundledDynamicProductForShopSetup1()
    {
        $invoice = $this->getInvoiceByOrderIncrementId('100000001');
        $request = $this->getCaptureRequest(27.20, $invoice);

        $this->postPurchaseValidator->performAllGeneralUsChecks($request, 2720, $invoice);
    }
}