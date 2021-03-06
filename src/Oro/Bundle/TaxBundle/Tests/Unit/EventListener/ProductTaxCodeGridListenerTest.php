<?php

namespace Oro\Bundle\TaxBundle\Tests\Unit\EventListener;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\TaxBundle\EventListener\ProductTaxCodeGridListener;
use Oro\Bundle\TaxBundle\EventListener\TaxCodeGridListener;

class ProductTaxCodeGridListenerTest extends AbstractTaxCodeGridListenerTest
{
    public function testOnBuildBefore()
    {
        $gridConfig = DatagridConfiguration::create(['name' => 'products-grid']);
        $gridConfig->offsetSetByPath('[source][query][from]', [['alias' => 'product']]);

        /** @var \PHPUnit_Framework_MockObject_MockObject|DatagridInterface $dataGrid */
        $dataGrid = $this->createMock('Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface');
        $event = new BuildBefore($dataGrid, $gridConfig);

        $metadata = $this->getMockBuilder('Doctrine\ORM\Mapping\ClassMetadata')->disableOriginalConstructor()
            ->getMock();

        $this->doctrineHelper->expects($this->once())->method('getEntityMetadataForClass')
            ->with('Oro\Bundle\TaxBundle\Entity\AbstractTaxCode')->willReturn($metadata);

        $metadata->expects($this->once())->method('getAssociationsByTargetClass')->with('\stdClass')
            ->willReturn(['stdClass' => ['fieldName' => 'products']]);

        $this->listener->onBuildBefore($event);

        $this->assertEquals(
            [
                'source' => [
                    'query' => [
                        'select' => ['taxCodes.code AS taxCode'],
                        'join' => [
                            'left' => [
                                [
                                    'join' => 'Oro\Bundle\TaxBundle\Entity\AbstractTaxCode',
                                    'alias' => 'taxCodes',
                                    'conditionType' => 'WITH',
                                    'condition' => 'product MEMBER OF taxCodes.products',
                                ],
                            ],
                        ],
                        'from' => [['alias' => 'product']],
                    ],
                ],
                'columns' => [
                    'taxCode' => [
                        'label' => 'oro.tax.taxcode.label',
                        'inline_editing' => [
                            'enable' => true,
                            'editor' => [
                                'view' => 'orotax/js/app/views/editor/product-tax-code-editor-view',
                                'view_options' => [
                                    'value_field_name' => 'taxCode',
                                ]
                            ],
                            'autocomplete_api_accessor' => [
                                'class' => 'oroui/js/tools/search-api-accessor',
                                'label_field_name' => 'code',
                                'search_handler_name' => 'oro_product_tax_code_entity_search'
                            ],
                            'save_api_accessor' => [
                                'route' => 'oro_api_patch_product_tax_code',
                                'query_parameter_names' => ['id']
                            ]
                        ]
                    ]
                ],
                'sorters' => ['columns' => ['taxCode' => ['data_name' => 'taxCode']]],
                'filters' => ['columns' => ['taxCode' => ['data_name' => 'taxCode', 'type' => 'string']]],
                'name' => 'products-grid',
            ],
            $gridConfig->toArray()
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage A root entity is missing for grid "std-grid"
     */
    public function testOnBuildBeforeWithoutFromPart()
    {
        $gridConfig = DatagridConfiguration::create(['name' => 'std-grid']);
        /** @var \PHPUnit_Framework_MockObject_MockObject|DatagridInterface $dataGrid */
        $dataGrid = $this->createMock('Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface');
        $event = new BuildBefore($dataGrid, $gridConfig);

        $this->listener->onBuildBefore($event);
    }


    /**
     * @return TaxCodeGridListener
     */
    protected function createListener()
    {
        return new ProductTaxCodeGridListener(
            $this->doctrineHelper,
            'Oro\Bundle\TaxBundle\Entity\AbstractTaxCode',
            '\stdClass'
        );
    }
}
