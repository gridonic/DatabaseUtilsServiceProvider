<?php

namespace Gridonic\Command;

use Gridonic\Command\Command as GridonicCommand;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * The doctrine:schema:create task class
 */
class DatabaseCreateCommand extends Command
{
    public function __construct($name = null)
    {
        parent::__construct($name);
    }

    /**
     * Set up options and arguments
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('database:create')
            ->setDescription('Creates the database schema')
            ->setHelp(<<<EOF
    The <info>doctrine:schema:create</info> creates the database schema

    <info>app/console database:create</info>
EOF
            );
    }

    /**
     * Run the task
     *
     * @param  \Symfony\Component\Console\Input\InputInterface   $input
     * @param  \Symfony\Component\Console\Output\OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $app = $this->getSilexApplication();

        /** @var \Doctrine\DBAL\Connection $db */
        $db = $app['db'];

        // Create schema
        $output->writeln('Creating schema');

        $db->getSchemaManager()->createSchema();

        /** @var \Gridonic\Migration\Manager $manager */
        $migrationManager = $app['migration'];

        if (!$migrationManager->hasVersionInfo()) {
            $migrationManager->createVersionInfo();
        }

        $output->writeln('Processing migrations');

        // Migrate
        $migrationManager->migrate();

        $output->writeln('Schema created');
    }
}
