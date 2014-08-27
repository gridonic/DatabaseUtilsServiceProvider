<?php

namespace Knp\Command;

use Knp\Command\Command as KnpCommand;

use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Console\Input\ArrayInput;

class DatabaseResetCommand extends KnpCommand
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
        if (true !== $input->hasParameterOption(array('--no-interaction', '-n')) && !$dialog->askConfirmation($output, '<question>You want to reset the database?</question>')) {
            return;
        }


        $output->writeln('Reset');
        $output->writeln('....................................');


        // drop database
        $output->writeln('# drop database');
        $command = $this->getApplication()->find('database:drop');

        $argumentDrop = array(
            'command' => 'database:drop',
        );

        $input = new ArrayInput($argumentDrop);
        $command->run($input, $output, false);

        $output->writeln('....................................');


        // build database
        $output->writeln('# run migration');

        $command = $this->getApplication()->find('migration:migrate');

        if ($command !== null) {

            $argumentDrop = array(
                'command' => 'migration:migrate',
            );

            $input = new ArrayInput($argumentDrop);
            $command->run($input, $output);

        } else {
            $output->writeln('# command "migration:migrate" not found.');
            $output->writeln('# migration not runned.');
        }

        $output->writeln('....................................');

        $output->writeln('finished Reset');
    }
}