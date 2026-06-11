<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Backend\Test\Model\Api;

use Klarna\AdminSettings\Model\Configurations\Api as KlarnaConfigurationsApi;
use Klarna\Backend\Model\Api\Builder as KlarnaApiBuilder;
use Klarna\Backend\Model\Api\OrderManagement as ApiOrderManagement;
use Klarna\Backend\Model\Api\Rest\Service\Ordermanagement as ServiceOrderManagement;
use Klarna\Base\Helper\DataConverter as KlarnaDataConverter;
use Klarna\Orderlines\Model\Container\Parameter as KlarnaParameter;
use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Klarna\Backend\Model\Api\OrderManagement
 */
class OrderManagementTest extends TestCase
{
    private ApiOrderManagement $model;
    private ServiceOrderManagement|MockObject $mockSrvOrderManagement;
    private DataObjectFactory|MockObject $mockDataObjectFactory;
    private KlarnaParameter|MockObject $mockParameter;
    private KlarnaDataConverter|MockObject $mockDataConverter;
    private KlarnaConfigurationsApi|MockObject $mockApi;
    private KlarnaApiBuilder|MockObject $mockBuilder;

    protected function setUp(): void
    {
        $this->mockSrvOrderManagement = $this->getMockBuilder(ServiceOrderManagement::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->mockDataConverter = $this->getMockBuilder(KlarnaDataConverter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->mockParameter = $this->getMockBuilder(KlarnaParameter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->mockDataObjectFactory = $this->getMockBuilder(DataObjectFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockDataObjectFactory->method('create')->willReturn(new DataObject());

        $this->mockApi = $this->getMockBuilder(KlarnaConfigurationsApi::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->mockBuilder = $this->getMockBuilder(KlarnaApiBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new ApiOrderManagement(
             $this->mockSrvOrderManagement,
             $this->mockDataConverter,
             $this->mockDataObjectFactory,
             $this->mockParameter,
             $this->mockApi,
             $this->mockBuilder,
        );
    }

    /**
     * @dataProvider addShippingInfoFormatProvider
     *
     * Test that addShippingInfo transforms tracking entries into the correct Klarna API payload format.
     * Scenario: Various combinations of tracking input — standard, oversized fields, multiple entries.
     * Verifies the shipping_info wrapper key is present and each entry contains correctly mapped
     * and truncated tracking_number, shipping_method, and shipping_company fields.
     */
    #[DataProvider('addShippingInfoFormatProvider')]
    public function testAddShippingInfoFormatsData(array $input, array $expectedPayload): void
    {
        $this->mockSrvOrderManagement->expects($this->once())
            ->method('addShippingInfo')
            ->with(
                $this->anything(),
                $this->anything(),
                $expectedPayload
            );

        $this->model->addShippingInfo(
            orderId: 'order-id',
            captureId: 'capture-id',
            shippingInfo: $input
        );
    }

    /**
     * Provides input tracking arrays and the corresponding expected Klarna API payloads.
     */
    public static function addShippingInfoFormatProvider(): array
    {
        return [
            'single entry maps all fields correctly' => [
                'input' => [
                    [
                        'carrier_code' => 'ups',
                        'title' => 'UPS Ground',
                        'number' => '1Z999AA10123456784',
                    ],
                ],
                'expectedPayload' => [
                    'shipping_info' => [
                        [
                            'tracking_number' => '1Z999AA10123456784',
                            'shipping_method' => ApiOrderManagement::KLARNA_API_SHIPPING_METHOD_HOME,
                            'shipping_company' => 'UPS Ground',
                        ],
                    ],
                ],
            ],
            'tracking number longer than 100 chars is truncated' => [
                'input' => [
                    [
                        'carrier_code' => 'ups',
                        'title' => 'UPS',
                        'number' => \str_repeat('A', 101)],
                ],
                'expectedPayload' => [
                    'shipping_info' => [
                        [
                            'tracking_number' => \str_repeat('A', 100),
                            'shipping_method' => ApiOrderManagement::KLARNA_API_SHIPPING_METHOD_HOME,
                            'shipping_company' => 'UPS',
                        ],
                    ],
                ],
            ],
            'shipping company longer than 100 chars is truncated' => [
                'input' => [
                    [
                        'carrier_code' => 'ups',
                        'title' => \str_repeat('B', 101),
                        'number' => 'TRACK123',
                    ],
                ],
                'expectedPayload' => [
                    'shipping_info' => [
                        [
                            'tracking_number' => 'TRACK123',
                            'shipping_method' => ApiOrderManagement::KLARNA_API_SHIPPING_METHOD_HOME,
                            'shipping_company' => \str_repeat('B', 100),
                        ],
                    ],
                ],
            ],
            'multiple entries produce one shipping_info item each' => [
                'input' => [
                    [
                        'carrier_code' => 'ups',
                        'title' => 'UPS Ground',
                        'number' => 'TRACK001',
                    ],
                    [
                        'carrier_code' => 'fedex',
                        'title' => 'FedEx',
                        'number' => 'TRACK002',
                    ],
                ],
                'expectedPayload' => [
                    'shipping_info' => [
                        [
                            'tracking_number' => 'TRACK001',
                            'shipping_method' => ApiOrderManagement::KLARNA_API_SHIPPING_METHOD_HOME,
                            'shipping_company' => 'UPS Ground',
                        ],
                        [
                            'tracking_number' => 'TRACK002',
                            'shipping_method' => ApiOrderManagement::KLARNA_API_SHIPPING_METHOD_HOME,
                            'shipping_company' => 'FedEx',
                        ],
                    ],
                ],
            ],
        ];
    }
}
