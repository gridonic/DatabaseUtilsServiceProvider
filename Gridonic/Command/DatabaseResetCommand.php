<?php

namespace Gridonic\Command;

use Gridonic\Command\Command as GridonicCommand;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Console\Input\ArrayInput;

class DatabaseResetCommand extends GridonicCommand
{
    public function configure()
    {
        $this
            ->setName('database:reset')
            ->setDescription('Resets the database')
            ->setHelp(

                <<<EOF
                    The <info>database:reset</info> command resets the database.
    First it drops the whole database, after it recreates the schema.
    <info>app/console database:reset</info>
EOF
            );
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $dialog = $this->getHelperSet()->get('dialog');
        if (true !== $input->hasParameterOption(array('--no-interaction', '-n')) && !$dialog->askConfirmation($output, '<question>This will drop all tables, recreate the schema and load fixtures. Are you sure to drop all data?</question>')) {
            return;
        }

        // Drop database
        $output->writeln('Dropping Database...');
        $command = $this->getApplication()->find('database:drop');

        $argumentDrop = array(
            'command' => 'database:drop',
        );

        $input = new ArrayInput($argumentDrop);
        $command->run($input, $output, false);

        $output->writeln('....................................');


        // Recreate database
        $output->writeln('Running Migrations...');

        $command = $this->getApplication()->find('migration:migrate');

        if ($command !== null) {
            $argumentDrop = array(
                'command' => 'migration:migrate',
            );

            $input = new ArrayInput($argumentDrop);
            $command->run($input, $output);
        } else {
            $output->writeln('Command "migration:migrate" not found.');
            $output->writeln('No migrations were run.');
        }

        $output->writeln('....................................');

        // Load fixtures
        $output->writeln('Loading fixtures...');

        $command = $this->getApplication()->find('database:fixtures:load');

        $argumentDrop = array(
            'command' => 'database:fixtures:load',
        );

        $input = new ArrayInput($argumentDrop);
        $command->run($input, $output);

        $output->writeln('....................................');

        // at the end
        $output->writeln('Reset finished.');
    }
}
