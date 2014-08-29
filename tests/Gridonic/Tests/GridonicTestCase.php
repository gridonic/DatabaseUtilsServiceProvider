<?php

/*
 * This file is part of the MigrationServiceProvider.
 *
 * (c) Gridonic <hello@gridonic.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Gridonic\Tests;

use Gridonic\Provider\DatabaseUtilsServiceProvider;
use Silex\Application;
use Silex\Provider\DoctrineServiceProvider;
use Knp\Provider\ConsoleServiceProvider;
use Gridonic\Provider\MigrationServiceProvider;

/**
 * Custom TestCase class with useful basic functions
 *
 * @author Beat Temperli <beat@gridonic.ch>
 */
abstract class GridonicTestCase extends \PHPUnit_Framework_TestCase
{
    protected $migrationPath = '/../Ressources/migrations';
    protected $fixturesPath = '/../Ressources/fixtures';
    protected $consoleVersion = '1.0.0';
    protected $consoleName = 'SilexTest';

    /**
     * Creates the silex app.
     *
     * @return Application
     */
    public function createApplication()
    {
        // open each time a new database
        $this->clearDatabase();

        /** @var Application $app */
        $app = new Application();

        // add config file
        require __DIR__ . '/../../config.test.php';


        // add this for the tests. Fails otherwise.
        $app['migration.path'] = __DIR__ . $this->migrationPath;


        // return the created app.
        return $app;
    }

    public function registerServices(Application $app) {

        $app->register(new DoctrineServiceProvider());
        $app->register(new MigrationServiceProvider(array(
            'migration.path' => $this->migrationPath,
        )));

        $app->register(new ConsoleServiceProvider(), array(
            'console.name'              => $this->consoleName,
            'console.version'           => $this->consoleVersion,
            'console.project_directory' => __DIR__ . '/'
        ));

        $app->register(new DatabaseUtilsServiceProvider(), array(
            'database_utils.fixtures'                    => __DIR__ . $this->fixturesPath . '/*.yml',
            'database_utils.password_keys'               => array('test_password'),
            'database_utils.security.salt'               => 'abcd',
        ));

        return $app;
    }

    public function createConsoleApplication()
    {

        $this->clearDatabase();

        /** @var Application $app */
        $app = new Application();

        // add config file
        require __DIR__ . '/../../config.test.php';


        // add this for the tests. Fails otherwise.
        $app['migration.path'] = __DIR__ . $this->migrationPath;

        $app = $this->registerServices($app);

        // return the created app.
        return $app['console'];
    }

    private function clearDatabase() {
        $databaseDir = __DIR__ . '/../../database';
        if (is_file($databaseDir . '/test.db')) {
            unlink($databaseDir . '/test.db');
        }
        @chmod($databaseDir, 0777);
        @chmod($databaseDir . '/test.db', 0777);
    }

}
