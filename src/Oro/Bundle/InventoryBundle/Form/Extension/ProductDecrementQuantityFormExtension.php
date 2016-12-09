<?php

namespace Oro\Bundle\InventoryBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;

use Oro\Bundle\CatalogBundle\Fallback\Provider\CategoryFallbackProvider;
use Oro\Bundle\EntityBundle\Entity\EntityFieldFallbackValue;
use Oro\Bundle\EntityBundle\Form\Type\EntityFieldFallbackValueType;
use Oro\Bundle\ProductBundle\Form\Type\ProductType;

class ProductDecrementQuantityFormExtension extends AbstractTypeExtension
{
    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return ProductType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $product = $builder->getData();
        // set category as default fallback
        if (!$product->getDecrementQuantity()) {
            $entityFallback = new EntityFieldFallbackValue();
            $entityFallback->setFallback(CategoryFallbackProvider::FALLBACK_ID);
            $product->setDecrementQuantity($entityFallback);
        }

        $builder->add(
            'decrementQuantity',
            EntityFieldFallbackValueType::NAME,
            [
                'label' => 'oro.inventory.decrement_inventory.label',
            ]
        );
    }
}
