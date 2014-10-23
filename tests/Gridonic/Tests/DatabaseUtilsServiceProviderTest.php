<?php
/**
 * This file is part of the DatabaseUtilsServiceProvider.
 *
 * (c) Gridonic <hello@gridonic.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Gridonic\Tests;

use Symfony\Component\Console\Tester\CommandTester;
use Doctrine\DBAL\DBALException;

/**
 * Tests for the DatabaseUtilsServiceProvider
 */
class DatabaseUtilsServiceProviderTest extends GridonicTestCase
{
    protected $wrongCommand = 'test';
    protected $migrationCommand = 'migration:migrate';
    protected $dropCommand = 'database:drop';
    protected $resetCommand = 'database:reset';
    protected $fixturesLoadCommand = 'database:fixtures:load';
    protected $databaseInsert = array(
        'test_id' => '1',
        'test_created' => '100000000',
        'test_name' => 'testname',
        'test_password' => '1234'
    );

    /**
     * Check some basic stuff.
     */
    public function testBasics()
    {
        // Are the tests running correct?
        $this->assertTrue(true);

        // Is everything correct with our created Application?
        $app = $this->createApplication();
        $this->assertInstanceOf('Silex\Application', $app);
    }

    /**
     * test the console functions
     */
    public function testServices()
    {
        $app = $this->createApplication();

        // register Services
        $app = $this->registerServices($app);

        $this->assertInstanceOf('Gridonic\Migration\Manager', $app['migration']);
        $this->assertInstanceOf('Gridonic\Console\Application', $app['console']);
    }

    /**
     * test if the console commands exist.
     */
    public function testExistConsoleCommands()
    {
        /** @var \Knp\Console\Application $app */
        $app = $this->createConsoleApplication();

        /** @var \Silex\Application $silexApp */
        $silexApp = $app->getSilexApplication();

        $this->assertInstanceOf('Gridonic\Console\Application', $app);
        $this->assertInstanceOf('Silex\Application', $silexApp);

        $this->assertStringStartsWith('<info>SilexTest</info> version <comment>' . $this->consoleVersion . '</comment>', $app->getHelp());
        $this->assertInstanceOf('Gridonic\Command\MigrationCommand', $app->get($this->migrationCommand));
        $this->assertInstanceOf('Gridonic\Command\DatabaseDropCommand', $app->get($this->dropCommand));
        $this->assertInstanceOf('Gridonic\Command\DatabaseResetCommand', $app->get($this->resetCommand));
        $this->assertInstanceOf('Gridonic\Command\FixturesLoadCommand', $app->get($this->fixturesLoadCommand));

        $this->assertEquals($this->consoleVersion, $app->getVersion());
        $this->assertEquals($this->consoleName, $app->getName());

        try {
            $app->get($this->wrongCommand);
            $this->fail('not throwing exception as expected');
        } catch(\InvalidArgumentException $e) {
            $this->assertInstanceOf('InvalidArgumentException', $e);
            $this->assertEquals('The command "' . $this->wrongCommand . '" does not exist.', $e->getMessage());
        }
    }

    /**
     * Test the migration command migration:migrate
     * - Execute, check output
     * - Execute again, check output
     * - Insert a row in the migrated table
     */
    public function testMigrationCommand()
    {
        /** @var \Knp\Console\Application $app */
        $app = $this->createConsoleApplication();

        $expectedMigrationMessageSuccess = "Successfully executed 1 migration(s)!\n - Added a test table\n";
        $expectedMigrationMessageFailed = "No migrations to execute, you are up to date!\n";

        // get migrationCommand
        $command = $app->get($this->migrationCommand);
        $tester = new CommandTester($command);

        // execute first time
        $tester->execute(array(
            'command' => $command->getName(),
        ));

        // should be successfully
        $this->assertEquals($expectedMigrationMessageSuccess, $tester->getDisplay());

        // execute second time
        $tester->execute(array(
            'command' => $command->getName(),
        ));

        // should be fail
        $this->assertEquals($expectedMigrationMessageFailed, $tester->getDisplay());

        // test content of database
        $silexApp = $app->getSilexApplication();

        /** @var \Doctrine\DBAL\Connection $db */
        $db =$silexApp['db'];

        $db->insert('test', $this->databaseInsert);

        $result = $db->fetchAssoc("SELECT `test_name` FROM `test` WHERE `test_id` = 1");

        $this->assertEquals($this->databaseInsert['test_name'], $result['test_name']);
    }

    /**
     * Test the drop command database:drop
     * - Migrate database
     * - Insert data
     * - Drop database
     * - Try to insert data again
     */
    public function testDropCommand()
    {
        /** @var \Knp\Console\Application $app */
        $app = $this->createConsoleApplication();

        $expectedDropMessage = "Dropping tables...\nAll the tables dropped.\n";

        // create database
        $migrationCommand = $app->get($this->migrationCommand);
        $tester = new CommandTester($migrationCommand);
        $tester->execute(array(
            'command' => $migrationCommand->getName(),
        ));

        // fill up one row
        $silexApp = $app->getSilexApplication();

        /** @var \Doctrine\DBAL\Connection $db */
        $db =$silexApp['db'];

        $db->insert('test', $this->databaseInsert);

        // drop database
        $dropCommand = $app->get($this->dropCommand);
        $dialog = $this->getMock('Symfony\Component\Console\Helper\DialogHelper', array('askConfirmation'));
        $dialog->expects($this->at(0))
            ->method('askConfirmation')
            ->will($this->returnValue(true)); // The user confirms

        $dropCommand->getHelperSet()->set($dialog, 'dialog');

        $tester = new CommandTester($dropCommand);

        // execute once
        $tester->execute(array(
            'command' => $dropCommand->getName(),
        ));

        $displayTry1 = $tester->getDisplay();
        $this->assertEquals($expectedDropMessage, $displayTry1);

        /** @var \Doctrine\DBAL\Connection $db */
        $db =$silexApp['db'];

        $request = "SELECT * FROM `test` WHERE `test_id` = 1";

        try {
            $db->fetchAssoc($request);
        } catch(DBALException $e) {
            $this->assertStringEndsWith('General error: 1 no such table: test', $e->getMessage());
        }
    }

    /**
     * Test reset command database:reset
     */
    public function testResetCommand()
    {
        // Not tested because the reset-command just calls other commands.
    }

    /**
     * Test to load fixtures database:fixtures:load
     * - create database
     * - load fixtures
     * - check database content
     */
    public function testFixturesLoadCommand()
    {
        $expectedMessage = "Purging database...\nLoading fixtures\nFixtures loaded\n";

        /** @var \Knp\Console\Application $app */
        $app = $this->createConsoleApplication();

        // create database
        $migrationCommand = $app->get($this->migrationCommand);
        $tester = new CommandTester($migrationCommand);
        $tester->execute(array(
            'command' => $migrationCommand->getName(),
        ));

        // drop database
        $fixturesLoadCommand = $app->get($this->fixturesLoadCommand);
        $dialog = $this->getMock('Symfony\Component\Console\Helper\DialogHelper', array('askConfirmation'));
        $dialog->expects($this->at(0))
            ->method('askConfirmation')
            ->will($this->returnValue(true)); // reset start?

        $fixturesLoadCommand->getHelperSet()->set($dialog, 'dialog');

        $tester = new CommandTester($fixturesLoadCommand);

        // execute once
        $tester->execute(array(
            'command' => $fixturesLoadCommand->getName(),
        ));

        $displayTry1 = $tester->getDisplay();

        $this->assertEquals($expectedMessage, $displayTry1);

        // fill up one row
        $silexApp = $app->getSilexApplication();

        /** @var \Doctrine\DBAL\Connection $db */
        $db =$silexApp['db'];

        $request = "SELECT * FROM `test` WHERE `test_id` = 1";

        $result = $db->fetchAssoc($request);

        $expectedResult = array(
            'test_id' => '1',
            'test_name' => 'aaaa',
            'test_password' => 'bbbb',
            'test_created' => '1000000041',
        );

        $this->assertEquals($expectedResult, $result);
    }
}
