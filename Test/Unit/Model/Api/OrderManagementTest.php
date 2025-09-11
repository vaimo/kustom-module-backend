<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

namespace Klarna\Backend\Test\Model\Api;

use Klarna\Backend\Model\Api\OrderManagement as ApiOrderManagement;
use Klarna\Backend\Model\Api\Rest\Service\Ordermanagement;
use Klarna\Orderlines\Model\Container\Parameter;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Klarna\Backend\Model\Api\OrderManagement
 */
class OrderManagementTest extends TestCase
{
    /**
     * @var Parameter|\PHPUnit\Framework\MockObject\MockObject
     */
    private $mockParameter;
    /** @var Ordermanagement */
    private $model;

    /** @var Ordermanagement | PHPUnit_Framework_MockObject_MockObject */
    private $mockOrderManagement;

    /** @var \Klarna\Base\Helper\KlarnaConfig | PHPUnit_Framework_MockObject_MockObject */
    private $mockKlarnaConfig;

    /** @var \Klarna\Base\Helper\DataConverter | PHPUnit_Framework_MockObject_MockObject */
    private $mockDataConverter;

    /** @var \Magento\Framework\DataObjectFactory | PHPUnit_Framework_MockObject_MockObject */
    private $mockDataObjectFactory;

    public function testGetKlarnaShippingMethod()
    {
        self::assertEquals(
            ApiOrderManagement::KLARNA_API_SHIPPING_METHOD_HOME,
            $this->model->getKlarnaShippingMethod([])
        );
    }

    protected function setUp(): void
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->mockOrderManagement   = $this->getMockBuilder(Ordermanagement::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockKlarnaConfig      = $this->getMockBuilder(\Klarna\Base\Helper\KlarnaConfig::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockDataConverter     = $this->getMockBuilder(\Klarna\Base\Helper\DataConverter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockParameter         = $this->getMockBuilder(Parameter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockDataObjectFactory = $this->getMockBuilder(\Magento\Framework\DataObjectFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $objectManager->getObject(
            ApiOrderManagement::class,
            [
                'orderManagement'   => $this->mockOrderManagement,
                'klarnaConfig'      => $this->mockKlarnaConfig,
                'dataConverter'     => $this->mockDataConverter,
                'dataObjectFactory' => $this->mockDataObjectFactory,
                'parameter'         => $this->mockParameter,
                'builderType'       => ''
            ]
        );
    }
}
