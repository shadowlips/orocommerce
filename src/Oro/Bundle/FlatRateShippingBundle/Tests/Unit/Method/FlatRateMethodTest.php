<?php

namespace Oro\Bundle\FlatRateShippingBundle\Tests\Unit\Method;

use Oro\Bundle\FlatRateShippingBundle\Method\FlatRateMethod;
use Oro\Bundle\FlatRateShippingBundle\Method\FlatRateMethodType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class FlatRateMethodTest extends \PHPUnit_Framework_TestCase
{
    /** @internal */
    const LABEL = 'test';

    /** @internal */
    const IDENTIFIER = 'flat_rate';

    /** @var FlatRateMethod */
    protected $flatRate;

    protected function setUp()
    {
        $this->flatRate = new FlatRateMethod(self::IDENTIFIER, self::LABEL, true);
    }

    public function testGetIdentifier()
    {
        static::assertEquals(self::IDENTIFIER, $this->flatRate->getIdentifier());
    }

    public function testIsGrouped()
    {
        static::assertFalse($this->flatRate->isGrouped());
    }

    public function testIsEnabled()
    {
        static::assertTrue($this->flatRate->isEnabled());
    }

    public function testGetLabel()
    {
        static::assertSame(self::LABEL, $this->flatRate->getLabel());
    }

    public function testGetTypes()
    {
        $types = $this->flatRate->getTypes();
        static::assertCount(1, $types);
        static::assertInstanceOf(FlatRateMethodType::class, $types[0]);
    }

    public function testGetTypeNull()
    {
        static::assertNull($this->flatRate->getType(null));
    }

    public function testGetType()
    {
        $type = $this->flatRate->getType(FlatRateMethodType::IDENTIFIER);
        static::assertInstanceOf(FlatRateMethodType::class, $type);
    }

    public function testGetOptionsConfigurationFormType()
    {
        static::assertEquals(HiddenType::class, $this->flatRate->getOptionsConfigurationFormType());
    }

    public function testGetSortOrder()
    {
        static::assertEquals(10, $this->flatRate->getSortOrder());
    }
}
