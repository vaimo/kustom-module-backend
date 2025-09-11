<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

namespace Klarna\Backend\Test\Model;

use Klarna\Backend\Model\Validator;
use Klarna\Base\Model\Order as KlarnaOrder;
use Klarna\Base\Exception as KlarnaException;
use Klarna\Logger\Model\Logger;
use Klarna\Base\Test\Unit\Mock\TestCase;
use Magento\Sales\Model\Order as MagentoOrder;
use Magento\Framework\DataObject;

/**
 * @coversDefaultClass \Klarna\Backend\Model\Validator
 */
class ValidatorTest extends TestCase
{
    /**
     * @var Validator
     */
    private Validator $validator;
    /**
     * @var KlarnaOrder
     */
    private KlarnaOrder $klarnaOrder;
    /**
     * @var MagentoOrder
     */
    private MagentoOrder $magentoOrder;
    /**
     * @var DataObject
     */
    private DataObject $dataObject;

    public function testCheckRequestSendableIdOfDatabaseEntryIsNull(): void
    {
        $this->klarnaOrder->method('getReservationId')
            ->willReturn('a');
        $this->klarnaOrder->method('getId')
            ->willReturn(null);
        $this->expectException(KlarnaException::class);
        $this->validator->checkRequestSendable($this->klarnaOrder, $this->magentoOrder, '');
    }

    public function testCheckRequestSendableNoReservationIdGiven(): void
    {
        $this->klarnaOrder->method('getReservationId')
            ->willReturn(null);
        $this->klarnaOrder->method('getId')
            ->willReturn('1');
        $this->expectException(KlarnaException::class);
        $this->validator->checkRequestSendable($this->klarnaOrder, $this->magentoOrder, '');
    }

    public function testCheckRequestSendableReturnsTrue(): void
    {
        $this->klarnaOrder->method('getReservationId')
            ->willReturn('a');
        $this->klarnaOrder->method('getId')
            ->willReturn('1');

        $result = $this->validator->checkRequestSendable($this->klarnaOrder, $this->magentoOrder, '');
        static::assertTrue($result);
    }

    public function testCheckApiResponseRequestWasNotSuccessful(): void
    {
        $this->dataObject->method('getIsSuccessful')
            ->willReturn(false);
        $this->dataObject->method('getErrorMessages')
            ->willReturn(['a', 'b']);
        $this->expectException(KlarnaException::class);
        $this->validator->checkApiResponse($this->klarnaOrder, $this->dataObject, $this->magentoOrder, '');
    }

    public function testCheckApiResponseRequestWasSuccessful(): void
    {
        $this->dataObject->method('getIsSuccessful')
            ->willReturn(true);

        $result = $this->validator->checkApiResponse(
            $this->klarnaOrder,
            $this->dataObject,
            $this->magentoOrder,
            ''
        );
        static::assertTrue($result);
    }

    protected function setUp(): void
    {
        $logger = $this->createSingleMock(Logger::class);
        $this->validator = parent::setUpMocks(Validator::class, [], ['logger' => $logger]);

        $this->klarnaOrder = $this->mockFactory->create(KlarnaOrder::class);
        $this->magentoOrder = $this->mockFactory->create(MagentoOrder::class);
        $store = $this->mockFactory->create(\Magento\Store\Model\Store::class);
        $this->magentoOrder->method('getStore')
            ->willReturn($store);

        $this->dataObject = $this->mockFactory->create(DataObject::class, [], ['getIsSuccessful', 'getErrorMessages']);
    }
}
