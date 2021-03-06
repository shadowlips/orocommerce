<?php

namespace Oro\Bundle\ProductBundle\Migrations\Schema\v1_11;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class AddIsFeaturedToProduct implements Migration
{
    const PRODUCT_TABLE_NAME = 'oro_product';

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable(self::PRODUCT_TABLE_NAME);
        $table->addColumn('is_featured', 'boolean', ['default' => false]);
    }
}
