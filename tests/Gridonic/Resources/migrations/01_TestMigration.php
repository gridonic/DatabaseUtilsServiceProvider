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
        $table = $schema->createTable('test');

        $table->addColumn('test_id', 'integer', array(
            'unsigned'      => true,
            'autoincrement' => true
        ));

        $table->addColumn('test_name', 'string');
        $table->addColumn('test_password', 'string');

        $table->setPrimaryKey(array('test_id'));
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
