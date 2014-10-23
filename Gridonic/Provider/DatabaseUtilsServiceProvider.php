<?php

namespace Gridonic\Provider;

use Silex\ServiceProviderInterface;
use Silex\Application;

use Gridonic\Console\ConsoleEvents;
use Gridonic\Console\ConsoleEvent;

use Gridonic\Command\DatabaseDropCommand;
use Gridonic\Command\DatabaseResetCommand;
use Gridonic\Command\FixturesLoadCommand;

class DatabaseUtilsServiceProvider implements ServiceProviderInterface
{

    public function register(Application $app)
    {
        $app['dispatcher']->addListener(ConsoleEvents::INIT, function(ConsoleEvent $event) {
            $application = $event->getApplication();
            $application->add(new DatabaseDropCommand());
            $application->add(new DatabaseResetCommand());
        });
    }

    public function boot(Application $app)
    {
        if (isset($app['database_utils.fixtures'])) {
            $app['dispatcher']->addListener(ConsoleEvents::INIT, function(ConsoleEvent $event) {
                $application = $event->getApplication();
                $application->add(new FixturesLoadCommand());
            });
        }
    }

}
