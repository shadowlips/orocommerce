<?php

namespace Oro\Bundle\TaxBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 */
class OroTaxBundleInstaller implements Installation
{
    /**
     * {@inheritdoc}
     */
    public function getMigrationVersion()
    {
        return 'v1_5';
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        /** Tables generation **/
        $this->createOroTaxTable($schema);
        $this->createOroTaxAccGrpTcAccGrpTable($schema);
        $this->createOroTaxAccTaxCodeAccTable($schema);
        $this->createOroTaxAccountTaxCodeTable($schema);
        $this->createOroTaxJurisdictionTable($schema);
        $this->createOroTaxProdTaxCodeProdTable($schema);
        $this->createOroTaxProductTaxCodeTable($schema);
        $this->createOroTaxRuleTable($schema);
        $this->createOroTaxZipCodeTable($schema);
        $this->createOroTaxValueTable($schema);

        /** Foreign keys generation **/
        $this->addOroTaxAccGrpTcAccGrpForeignKeys($schema);
        $this->addOroTaxAccTaxCodeAccForeignKeys($schema);
        $this->addOroTaxJurisdictionForeignKeys($schema);
        $this->addOroTaxProdTaxCodeProdForeignKeys($schema);
        $this->addOroTaxRuleForeignKeys($schema);
        $this->addOroTaxZipCodeForeignKeys($schema);
        $this->addOroCustomerTaxCodeForeignKeys($schema);
    }

    /**
     * Create oro_tax table
     *
     * @param Schema $schema
     */
    protected function createOroTaxTable(Schema $schema)
    {
        $table = $schema->createTable('oro_tax');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('code', 'string', ['length' => 255]);
        $table->addColumn('description', 'text', ['notnull' => false]);
        $table->addColumn('rate', 'percent', ['comment' => '(DC2Type:percent)']);
        $table->addColumn('created_at', 'datetime', []);
        $table->addColumn('updated_at', 'datetime', []);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['code'], 'UNIQ_E132B24377153098');
    }

    /**
     * Create oro_tax_cus_grp_tc_cus_grp table
     *
     * @param Schema $schema
     */
    protected function createOroTaxAccGrpTcAccGrpTable(Schema $schema)
    {
        $table = $schema->createTable('oro_tax_cus_grp_tc_cus_grp');
        $table->addColumn('customer_group_tax_code_id', 'integer', []);
        $table->addColumn('customer_group_id', 'integer', []);
        $table->setPrimaryKey(['customer_group_tax_code_id', 'customer_group_id']);
        $table->addUniqueIndex(['customer_group_id'], 'UNIQ_D3457B7869A3BF1');
    }

    /**
     * Create oro_tax_cus_tax_code_cus table
     *
     * @param Schema $schema
     */
    protected function createOroTaxAccTaxCodeAccTable(Schema $schema)
    {
        $table = $schema->createTable('oro_tax_cus_tax_code_cus');
        $table->addColumn('customer_tax_code_id', 'integer', []);
        $table->addColumn('customer_id', 'integer', []);
        $table->setPrimaryKey(['customer_tax_code_id', 'customer_id']);
        $table->addUniqueIndex(['customer_id'], 'UNIQ_53167F2A9B6B5FBA');
    }

    /**
     * Create oro_tax_customer_tax_code table
     *
     * @param Schema $schema
     */
    protected function createOroTaxAccountTaxCodeTable(Schema $schema)
    {
        $table = $schema->createTable('oro_tax_customer_tax_code');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('code', 'string', ['length' => 255]);
        $table->addColumn('description', 'text', ['notnull' => false]);
        $table->addColumn('created_at', 'datetime', []);
        $table->addColumn('updated_at', 'datetime', []);
        $table->addColumn('user_owner_id', 'integer', ['notnull' => false]);
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['code'], 'UNIQ_E98BB26B77153098');
    }

    /**
     * Create oro_tax_jurisdiction table
     *
     * @param Schema $schema
     */
    protected function createOroTaxJurisdictionTable(Schema $schema)
    {
        $table = $schema->createTable('oro_tax_jurisdiction');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('country_code', 'string', ['notnull' => false, 'length' => 2]);
        $table->addColumn('region_code', 'string', ['notnull' => false, 'length' => 16]);
        $table->addColumn('created_at', 'datetime', []);
        $table->addColumn('updated_at', 'datetime', []);
        $table->addColumn('code', 'string', ['length' => 255]);
        $table->addColumn('description', 'text', ['notnull' => false]);
        $table->addColumn('region_text', 'string', ['notnull' => false, 'length' => 255]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['code'], 'UNIQ_2CBEF9AE77153098');
    }

    /**
     * Create oro_tax_prod_tax_code_prod table
     *
     * @param Schema $schema
     */
    protected function createOroTaxProdTaxCodeProdTable(Schema $schema)
    {
        $table = $schema->createTable('oro_tax_prod_tax_code_prod');
        $table->addColumn('product_tax_code_id', 'integer', []);
        $table->addColumn('product_id', 'integer', []);
        $table->setPrimaryKey(['product_tax_code_id', 'product_id']);
        $table->addUniqueIndex(['product_id'], 'UNIQ_3CF9D1FA4584665A');
    }

    /**
     * Create oro_tax_product_tax_code table
     *
     * @param Schema $schema
     */
    protected function createOroTaxProductTaxCodeTable(Schema $schema)
    {
        $table = $schema->createTable('oro_tax_product_tax_code');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('code', 'string', ['length' => 255]);
        $table->addColumn('description', 'text', ['notnull' => false]);
        $table->addColumn('created_at', 'datetime', []);
        $table->addColumn('updated_at', 'datetime', []);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['code'], 'UNIQ_5AF53A4A77153098');
    }

    /**
     * Create oro_tax_rule table
     *
     * @param Schema $schema
     */
    protected function createOroTaxRuleTable(Schema $schema)
    {
        $table = $schema->createTable('oro_tax_rule');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('tax_jurisdiction_id', 'integer', ['notnull' => false]);
        $table->addColumn('customer_tax_code_id', 'integer', ['notnull' => false]);
        $table->addColumn('product_tax_code_id', 'integer', ['notnull' => false]);
        $table->addColumn('tax_id', 'integer', ['notnull' => false]);
        $table->addColumn('description', 'text', ['notnull' => false]);
        $table->addColumn('created_at', 'datetime', []);
        $table->addColumn('updated_at', 'datetime', []);
        $table->setPrimaryKey(['id']);
    }

    /**
     * Create oro_tax_zip_code table
     *
     * @param Schema $schema
     */
    protected function createOroTaxZipCodeTable(Schema $schema)
    {
        $table = $schema->createTable('oro_tax_zip_code');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('tax_jurisdiction_id', 'integer', []);
        $table->addColumn('zip_code', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('zip_range_start', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('zip_range_end', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('created_at', 'datetime', []);
        $table->addColumn('updated_at', 'datetime', []);
        $table->setPrimaryKey(['id']);
    }

    /**
     * Create oro_tax_value table
     *
     * @param Schema $schema
     */
    protected function createOroTaxValueTable(Schema $schema)
    {
        $table = $schema->createTable('oro_tax_value');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('result', 'json_array');
        $table->addColumn('entity_class', 'string', ['length' => 255]);
        $table->addColumn('entity_id', 'integer', ['notnull' => false]);
        $table->addColumn('address', 'text', []);
        $table->addColumn('created_at', 'datetime', ['comment' => '(DC2Type:datetime)']);
        $table->addColumn('updated_at', 'datetime', ['comment' => '(DC2Type:datetime)']);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['entity_class', 'entity_id'], 'oro_tax_value_class_id_idx');
    }

    /**
     * Add oro_tax_cus_grp_tc_cus_grp foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOroTaxAccGrpTcAccGrpForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('oro_tax_cus_grp_tc_cus_grp');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_customer_group'),
            ['customer_group_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_tax_customer_tax_code'),
            ['customer_group_tax_code_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }

    /**
     * Add oro_tax_cus_tax_code_cus foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOroTaxAccTaxCodeAccForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('oro_tax_cus_tax_code_cus');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_tax_customer_tax_code'),
            ['customer_tax_code_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_customer'),
            ['customer_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }

    /**
     * Add oro_tax_jurisdiction foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOroTaxJurisdictionForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('oro_tax_jurisdiction');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_dictionary_country'),
            ['country_code'],
            ['iso2_code'],
            ['onDelete' => null, 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_dictionary_region'),
            ['region_code'],
            ['combined_code'],
            ['onDelete' => null, 'onUpdate' => null]
        );
    }

    /**
     * Add oro_tax_prod_tax_code_prod foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOroTaxProdTaxCodeProdForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('oro_tax_prod_tax_code_prod');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_tax_product_tax_code'),
            ['product_tax_code_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_product'),
            ['product_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }

    /**
     * Add oro_tax_rule foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOroTaxRuleForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('oro_tax_rule');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_tax_jurisdiction'),
            ['tax_jurisdiction_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_tax_customer_tax_code'),
            ['customer_tax_code_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_tax_product_tax_code'),
            ['product_tax_code_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_tax'),
            ['tax_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }

    /**
     * Add oro_tax_zip_code foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOroTaxZipCodeForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('oro_tax_zip_code');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_tax_jurisdiction'),
            ['tax_jurisdiction_id'],
            ['id'],
            ['onDelete' => null, 'onUpdate' => null]
        );
    }

    /**
     * Add oro_tax_customer_tax_code foreign keys.
     * @param Schema $schema
     */
    protected function addOroCustomerTaxCodeForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('oro_tax_customer_tax_code');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_user'),
            ['user_owner_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['organization_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
    }
}
