<?php

namespace Oro\Bundle\SaleBundle\Tests\Unit\Quote\Shipping\LineItem\Converter;

use Oro\Bundle\CurrencyBundle\Entity\Price;
use Oro\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\ProductBundle\Entity\ProductUnit;
use Oro\Bundle\SaleBundle\Entity\Quote;
use Oro\Bundle\SaleBundle\Entity\QuoteProductDemand;
use Oro\Bundle\SaleBundle\Entity\QuoteProductOffer;
use Oro\Bundle\ShippingBundle\Context\LineItem\Builder\Factory\ShippingLineItemBuilderFactoryInterface;
use Oro\Bundle\ShippingBundle\Context\LineItem\Builder\ShippingLineItemBuilderInterface;
use Oro\Bundle\ShippingBundle\Context\LineItem\Collection\Doctrine\DoctrineShippingLineItemCollection;
use Oro\Bundle\ShippingBundle\Context\LineItem\Collection\Factory\ShippingLineItemCollectionFactoryInterface;
use Oro\Bundle\ShippingBundle\Context\ShippingLineItem;

abstract class AbstractOffersQuoteToShippingLineItemConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ShippingLineItemCollectionFactoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $shippingLineItemCollectionFactory;

    /**
     * @var ShippingLineItemBuilderFactoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $shippingLineItemBuilderFactory;

    public function setUp()
    {
        $this->shippingLineItemCollectionFactory = $this
            ->getMockBuilder(ShippingLineItemCollectionFactoryInterface::class)
            ->getMock();

        $this->shippingLineItemBuilderFactory = $this
            ->getMockBuilder(ShippingLineItemBuilderFactoryInterface::class)
            ->getMock();
    }

    /**
     * @return QuoteProductOffer|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getQuoteProductOfferMock()
    {
        return $this
            ->getMockBuilder(QuoteProductOffer::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getQuoteMock()
    {
        return $this
            ->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return ShippingLineItemBuilderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getShippingLineItemBuilderMock()
    {
        return $this
            ->getMockBuilder(ShippingLineItemBuilderInterface::class)
            ->getMock();
    }

    /**
     * @param int $quantity
     * @param QuoteProductDemand|\PHPUnit_Framework_MockObject_MockObject $quoteProductOfferMock
     * @return DoctrineShippingLineItemCollection
     */
    protected function prepareConvertLineItems($quantity, $quoteProductOfferMock)
    {
        $builderMock = $this->getShippingLineItemBuilderMock();

        $product = new Product();
        $productUnit = new ProductUnit();
        $productUnitCode = 'each';

        $price = Price::create(12, 'USD');

        $expectedLineItem = new ShippingLineItem([
            ShippingLineItem::FIELD_PRODUCT => $product,
            ShippingLineItem::FIELD_QUANTITY => $quantity,
            ShippingLineItem::FIELD_PRODUCT_UNIT => $productUnit,
            ShippingLineItem::FIELD_PRODUCT_UNIT_CODE => $productUnitCode,
            ShippingLineItem::FIELD_PRODUCT_HOLDER => $quoteProductOfferMock,
            ShippingLineItem::FIELD_PRICE => $price
        ]);

        $expectedLineItemsArray = [$expectedLineItem];
        $expectedLineItemsCollection = new DoctrineShippingLineItemCollection($expectedLineItemsArray);

        $quoteProductOfferMock
            ->expects($this->exactly(2))
            ->method('getProduct')
            ->willReturn($product);

        $quoteProductOfferMock
            ->expects($this->once())
            ->method('getProductUnit')
            ->willReturn($productUnit);

        $quoteProductOfferMock
            ->expects($this->once())
            ->method('getProductUnitCode')
            ->willReturn($productUnitCode);

        $quoteProductOfferMock
            ->expects($this->atLeastOnce())
            ->method('getPrice')
            ->willReturn($price);

        $builderMock
            ->expects($this->once())
            ->method('setProduct')
            ->with($product);

        $builderMock
            ->expects($this->once())
            ->method('getResult')
            ->willReturn($expectedLineItem);

        $this->shippingLineItemBuilderFactory
            ->expects($this->once())
            ->method('createBuilder')
            ->with($price, $productUnit, $productUnitCode, $quantity, $quoteProductOfferMock)
            ->willReturn($builderMock);

        $this->shippingLineItemCollectionFactory
            ->expects($this->once())
            ->method('createShippingLineItemCollection')
            ->with($expectedLineItemsArray)
            ->willReturn($expectedLineItemsCollection);

        return $expectedLineItemsCollection;
    }
}
