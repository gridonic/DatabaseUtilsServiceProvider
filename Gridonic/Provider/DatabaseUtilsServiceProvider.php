<?php

namespace Gridonic\Provider;

use Silex\ServiceProviderInterface;
use Silex\Application;

use Knp\Console\ConsoleEvents;
use Knp\Console\ConsoleEvent;

use Gridonic\Command\DatabaseDropCommand;
use Gridonic\Command\DatabaseResetCommand;

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
    }

}
