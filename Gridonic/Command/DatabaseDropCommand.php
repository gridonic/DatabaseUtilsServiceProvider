<?php

namespace Gridonic\Command;

use Gridonic\Command\Command as GridonicCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DatabaseDropCommand extends GridonicCommand
{
    public function configure()
    {
        $this
            ->setName('database:drop')
            ->setDescription('Clears the database')->setHelp(

                <<<EOF
                    The <info>database:drop</info> command drops the database tables
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

        $tablesToDelete = array();

        while ($row = $stmt->fetch()) {

            $arrayValues = array_values($row);

            if (isset($row[0])) {
                array_push($tablesToDelete, $row[0]);
            } else if (isset($row['Table_type']) && $arrayValues[0] != null) {
                // SQL
                array_push($tablesToDelete, $arrayValues[0]);
            } else if ($arrayValues[0] != null) {
                // SQLite
                array_push($tablesToDelete, $arrayValues[0]);
            }
        }

        unset($stmt);

        foreach($tablesToDelete as $table) {
            $db->query($platform->getDropTableSQL($table));
        }

        if ($platform->supportsForeignKeyConstraints()) {
            $db->exec('SET FOREIGN_KEY_CHECKS=1;');
        }

        $output->writeln('All tables were dropped.');
    }
}
