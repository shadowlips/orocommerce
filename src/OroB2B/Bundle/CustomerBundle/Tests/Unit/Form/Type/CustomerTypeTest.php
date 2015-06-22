<?php

namespace OroB2B\Bundle\CustomerBundle\Tests\Unit\Form\Type;

use Oro\Component\Testing\Unit\Form\Type\Stub\EntityType;
use OroB2B\Bundle\CustomerBundle\Tests\Unit\Form\Type\Stub\AddressCollectionTypeStub;
use OroB2B\Bundle\CustomerBundle\Form\Type\CustomerGroupSelectType;
use OroB2B\Bundle\CustomerBundle\Form\Type\ParentCustomerSelectType;
use OroB2B\Bundle\CustomerBundle\Form\Type\CustomerType;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\FormIntegrationTestCase;

class CustomerTypeTest extends FormIntegrationTestCase
{
    /**
     * @var CustomerType
     */
    protected $formType;
    protected $entityManager;

    protected function setUp()
    {
        parent::setUp();

        $this->formType = new CustomerType();
    }

    protected function tearDown()
    {
        unset($this->formType);
    }

    /**
     * @return array
     */
    protected function getExtensions()
    {
        $customerGroupSelectType = new EntityType(
            [
                1 => $this->getEntity('OroB2B\Bundle\CustomerBundle\Entity\CustomerGroup', 1),
                2 => $this->getEntity('OroB2B\Bundle\CustomerBundle\Entity\CustomerGroup', 2)
            ],
            CustomerGroupSelectType::NAME
        );

        $parentCustomerSelectType = new EntityType(
            [
                1 => $this->getEntity('OroB2B\Bundle\CustomerBundle\Entity\Customer', 1),
                2 => $this->getEntity('OroB2B\Bundle\CustomerBundle\Entity\Customer', 2)
            ],
            ParentCustomerSelectType::NAME
        );

        $addressEntityType = new EntityType(
            [
                1 => $this->getEntity('OroB2B\Bundle\CustomerBundle\Entity\CustomerAddress', 1),
                2 => $this->getEntity('OroB2B\Bundle\CustomerBundle\Entity\CustomerAddress', 2)
            ],
            'test_address_entity'
        );

        return [
            new PreloadedExtension(
                [
                    CustomerGroupSelectType::NAME  => $customerGroupSelectType,
                    ParentCustomerSelectType::NAME => $parentCustomerSelectType,
                    'oro_address_collection'  => new AddressCollectionTypeStub(),
                    $addressEntityType->getName()  => $addressEntityType,
                ],
                []
            )
        ];
    }

    /**
     * @param array $options
     * @param mixed $defaultData
     * @param mixed $viewData
     * @param mixed $submittedData
     * @param mixed $expectedData
     * @dataProvider submitDataProvider
     */
    public function testSubmit(array $options, $defaultData, $viewData, $submittedData, $expectedData)
    {
        $form = $this->factory->create($this->formType, $defaultData, $options);

        $formConfig = $form->getConfig();
        $this->assertNull($formConfig->getOption('data_class'));

        $this->assertEquals($defaultData, $form->getData());
        $this->assertEquals($viewData, $form->getViewData());

        $form->submit($submittedData);
        $this->assertTrue($form->isValid());
        $this->assertEquals($expectedData, $form->getData());
    }

    /**
     * @return array
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function submitDataProvider()
    {
        return [
            'default' => [
                'options' => [],
                'defaultData' => [],
                'viewData' => [],
                'submittedData' => [
                    'name' => 'customer_name',
                    'group' => 1,
                    'parent' => 2,
                    'addresses' => 1,
                ],
                'expectedData' => [
                    'name' => 'customer_name',
                    'group' => $this->getEntity('OroB2B\Bundle\CustomerBundle\Entity\CustomerGroup', 1),
                    'parent' => $this->getEntity('OroB2B\Bundle\CustomerBundle\Entity\Customer', 2),
                    'addresses' => $this->getEntity('OroB2B\Bundle\CustomerBundle\Entity\CustomerAddress', 1),
                ]
            ],
            'empty parent' => [
                'options' => [],
                'defaultData' => [],
                'viewData' => [],
                'submittedData' => [
                    'name' => 'customer_name',
                    'group' => 1,
                    'parent' => null,
                    'addresses' => 1
                ],
                'expectedData' => [
                    'name' => 'customer_name',
                    'group' => $this->getEntity('OroB2B\Bundle\CustomerBundle\Entity\CustomerGroup', 1),
                    'parent' => null,
                    'addresses' => $this->getEntity('OroB2B\Bundle\CustomerBundle\Entity\CustomerAddress', 1)
                ]
            ],
            'empty group' => [
                'options' => [],
                'defaultData' => [],
                'viewData' => [],
                'submittedData' => [
                    'name' => 'customer_name',
                    'group' => null,
                    'parent' => 2,
                    'addresses' => 1
                ],
                'expectedData' => [
                    'name' => 'customer_name',
                    'group' => null,
                    'parent' => $this->getEntity('OroB2B\Bundle\CustomerBundle\Entity\Customer', 2),
                    'addresses' => $this->getEntity('OroB2B\Bundle\CustomerBundle\Entity\CustomerAddress', 1),
                ]
            ],
            'empty address' => [
                'options' => [],
                'defaultData' => [],
                'viewData' => [],
                'submittedData' => [
                    'name' => 'customer_name',
                    'group' => 1,
                    'parent' => 2,
                    'addresses' => null
                ],
                'expectedData' => [
                    'name' => 'customer_name',
                    'group' => $this->getEntity('OroB2B\Bundle\CustomerBundle\Entity\CustomerGroup', 1),
                    'parent' => $this->getEntity('OroB2B\Bundle\CustomerBundle\Entity\Customer', 2),
                    'addresses' => null
                ]
            ],
        ];
    }

    public function testGetName()
    {
        $this->assertInternalType('string', $this->formType->getName());
        $this->assertEquals('orob2b_customer_type', $this->formType->getName());
    }

    /**
     * @param string $className
     * @param int $id
     * @return object
     */
    protected function getEntity($className, $id)
    {
        $entity = new $className;

        $reflectionClass = new \ReflectionClass($className);
        $method = $reflectionClass->getProperty('id');
        $method->setAccessible(true);
        $method->setValue($entity, $id);

        return $entity;
    }
}
