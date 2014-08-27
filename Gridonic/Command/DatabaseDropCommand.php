<?php

namespace Gridonic\Command;

use Knp\Command\Command as KnpCommand;

use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DatabaseDropCommand extends KnpCommand
{
    public function configure()
    {
        $this
            ->setName('database:drop')
            ->setDescription('Clears the database')->setHelp(

<<<EOF
    The <info>database:drop</info> command drops the database schema
    <info>app/console database:drop</info>
EOF
            );
    }

    public function execute(InputInterface $input, OutputInterface $output, $manually = true)
    {
        $app = $this->getSilexApplication();

        $db = $app['db'];

        if ($manually) {
            $dialog = $this->getHelperSet()->get('dialog');
            if (true !== $input->hasParameterOption(array('--no-interaction', '-n')) && !$dialog->askConfirmation($output, '<question>This will delete all data in your database. Are you sure?</question>')) {
                return;
            }
        }

        $output->writeln('Dropping tables...');

        $platform = $db->getDatabasePlatform();

        if ($platform->supportsForeignKeyConstraints()) {
            $db->exec('SET FOREIGN_KEY_CHECKS=0;');
        }

        $stmt = $db->query($platform->getListTablesSQL());

        while ($row = $stmt->fetch()) {
            if (isset($row[0])) {
                $db->query($platform->getDropTableSQL($row[0]));
            } else if (isset($row['Table_type']) && array_values($row)[0] != null) {
                $db->query($platform->getDropTableSQL(array_values($row)[0]));
            }
        }

        if ($platform->supportsForeignKeyConstraints()) {
            $db->exec('SET FOREIGN_KEY_CHECKS=1;');
        }

        $output->writeln('All the tables dropped.');
    }
}