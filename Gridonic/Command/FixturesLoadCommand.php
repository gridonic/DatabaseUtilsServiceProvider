<?php

namespace Gridonic\Command;

use Knp\Command\Command as KnpCommand;

use Silex\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class FixturesLoadCommand extends KnpCommand
{
    protected $passwordColumns = array();

    public function configure()
    {
        $this
            ->setName('database:fixtures:load')
            ->setDescription('Loads example-data')->setHelp(

<<<EOF
    The <info>database:fixtures:load</info> command loads example-data from your fixtures-folder into the database.
    <info>app/console database:fixtures:load</info>
EOF
            );
    }

    public function execute(InputInterface $input, OutputInterface $output, $manually = true)
    {
        $app = $this->getSilexApplication();

        if (isset($app['database_utils.password_keys'])) {
            $this->passwordColumns = $app['database_utils.password_keys'];
        }

        if ($manually) {
            $dialog = $this->getHelperSet()->get('dialog');
            if (true !== $input->hasParameterOption(array('--no-interaction', '-n')) && !$dialog->askConfirmation($output, '<question>This will overwrite all your existing data.</question>')) {
                return;
            }
        }

        $fixtures = array();

        foreach (glob($app['database_utils.fixtures']) as $file) {
            $fixtures = array_merge($fixtures, Yaml::parse(file_get_contents($file)));
        }

        /** @var \Doctrine\DBAL\Connection $db */
        $db = $app['db'];

        // purge
        $output->writeln('Purging database...');

        $platform = $db->getDatabasePlatform();

        if ($platform->supportsForeignKeyConstraints()) {
            $db->exec('SET FOREIGN_KEY_CHECKS=0;');
        }

        foreach ($fixtures as $table => $data) {
            $db->exec($platform->getTruncateTableSQL($table));
        }

        if ($platform->supportsForeignKeyConstraints()) {
            $db->exec('SET FOREIGN_KEY_CHECKS=1;');
        }

        // load fixtures
        $output->writeln('Loading fixtures');

        foreach ($fixtures as $table => $data) {
            foreach ($data as $row) {

                // stripslashes is a workaround for slashes we need
                // to add around markdown text in fixture files
                foreach ($row as $key => $value) {

                    if (is_string($value)) {
                        if (strpos($value, '"', 0) !== false) {
                            $value = substr($value, 1);
                        }

                        if (strpos($value, '"', strlen($value) - 1) !== false) {
                            $value = substr($value, 0, strlen($value) - 1);
                        }

                        // encode password fields which are defined
                        // in the password columns array
                        if (in_array($key, $this->passwordColumns) && isset($app['security'])) {

                            $output->writeln('Encode ' . $key . ' field');

                            $encoder = $app['security.encoder.digest'];
                            $value = $encoder->encodePassword($value, $app['database_utils.security.salt']);
                        }
                    }

                    // serialize arrays
                    if (is_array($value)) {
                        $value = serialize($value);
                    }

                    $row[$key] = $value;
                }

                $db->insert($table, $row);
            }
        }

        $output->writeln('Fixtures loaded');
    }
}