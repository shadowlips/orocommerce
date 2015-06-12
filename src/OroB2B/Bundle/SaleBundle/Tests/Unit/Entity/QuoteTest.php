<?php

namespace OroB2B\Bundle\SaleBundle\Tests\Unit\Entity;

use Oro\Bundle\UserBundle\Entity\User;

use OroB2B\Bundle\SaleBundle\Entity\Quote;

class QuoteTest extends AbstractTest
{
    public function testProperties()
    {
        $now = new \DateTime('now');
        $properties = [
            ['id', '123'],
            ['qid', 'QID-123456'],
            ['owner', new User()],
            ['validUntil', $now, false],
            ['createdAt', $now, false],
            ['updatedAt', $now, false],
        ];

        $this->assertPropertyAccessors(new Quote(), $properties);
    }

    public function testPrePersist()
    {
        $quote = new Quote();

        $this->assertNull($quote->getCreatedAt());
        $this->assertNull($quote->getUpdatedAt());

        $quote->prePersist();

        $this->assertInstanceOf('\DateTime', $quote->getCreatedAt());
        $this->assertInstanceOf('\DateTime', $quote->getUpdatedAt());
    }

    public function testPreUpdate()
    {
        $quote = new Quote();

        $this->assertNull($quote->getUpdatedAt());
        
        $quote->preUpdate();

        $this->assertInstanceOf('\DateTime', $quote->getUpdatedAt());
    }

}
