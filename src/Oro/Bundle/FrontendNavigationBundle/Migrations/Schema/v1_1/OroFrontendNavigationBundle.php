<?php

namespace Oro\Bundle\FrontendNavigationBundle\Migrations\Schema\v1_1;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\StringType;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroFrontendNavigationBundle implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        /** Table updates **/
        $this->updateOroNavigationMenuUpdateTable($schema);

        /** Tables generation **/
        $this->createOroNavigationMenuUpdateDescriptionTable($schema);

        /** Foreign keys generation **/
        $this->addOroNavigationMenuUpdateDescriptionForeignKeys($schema);
    }

    /**
     * Update oro_navigation_menu_upd
     *
     * @param Schema $schema
     */
    protected function updateOroNavigationMenuUpdateTable(Schema $schema)
    {
        $table = $schema->getTable('oro_front_nav_menu_upd');
        $table->addColumn('is_divider', 'boolean', []);
        $table->addColumn('is_custom', 'boolean', []);
        $table->changeColumn('ownership_type', ['type' => StringType::getType('string')]);
        $table->dropColumn('website_id');
        $table->addUniqueIndex(['key', 'ownership_type'], 'unq_menu_key');
    }

    /**
     * Create `oro_navigation_menu_upd_descr` table
     *
     * @param Schema $schema
     */
    protected function createOroNavigationMenuUpdateDescriptionTable(Schema $schema)
    {
        $table = $schema->createTable('oro_front_nav_menu_upd_descr');
        $table->addColumn('menu_update_id', 'integer', []);
        $table->addColumn('localized_value_id', 'integer', []);
        $table->setPrimaryKey(['menu_update_id', 'localized_value_id']);
        $table->addUniqueIndex(['localized_value_id']);
    }

    /**
     * Add `oro_navigation_menu_upd_descr` foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOroNavigationMenuUpdateDescriptionForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('oro_front_nav_menu_upd_descr');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_fallback_localization_val'),
            ['localized_value_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_navigation_menu_upd'),
            ['menu_update_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
        );
    }
}
