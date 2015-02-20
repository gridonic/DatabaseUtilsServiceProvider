<?php

namespace Migration;

use Doctrine\DBAL\Schema\Schema;
use Gridonic\Migration\AbstractMigration;
use Silex\Application;

/**
 * Class UserMigration
 *
 * @package Migration
 */
class TestMigration extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function schemaUp(Schema $schema)
    {
        // before app is started, do the following.
        $tableTest = $schema->createTable('test');

        $tableTest->addColumn('test_id', 'integer', array(
            'unsigned'      => true,
            'autoincrement' => true
        ));

        $tableTest->addColumn('test_name', 'string');
        $tableTest->addColumn('test_password', 'string');

        $tableTest->setPrimaryKey(array('test_id'));

        // before app is started, do the following.
        $tableType = $schema->createTable('test_type');
        $tableType->addColumn('string', 'string');
        $tableType->addColumn('integer', 'integer');
        $tableType->addColumn('boolean', 'boolean', array('notnull' => false));

    }

    public function schemaDown(Schema $schema) {
        // after app is Up, do the following.

        $table = $schema->getTable('test');

        $table->addColumn('test_created', 'string', array('unsigned' => true, 'default' => '0'));

    }

    /**
     * @return string
     */
    public function getMigrationInfo()
    {
        return 'Added a test table';
    }
}
