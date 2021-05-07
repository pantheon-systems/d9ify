<?php

namespace D9ify;

use Symfony\Component\Console\Application as SymfApp;
use D9ify\ProcessCommand;

class Application extends SymfApp
{

    public static function process(\Composer\Script\Event $event)
    {
        $arguments = $event->getArguments();
        $process = new ProcessCommand();
        $process->setComposerIOInterface($event->getIO());
        $GLOBALS['app'] = new static();
        $GLOBALS['app']->add($process);
        $GLOBALS['app']->setDefaultCommand($process->getName(), true);
        $GLOBALS['app']->run();
    }
}
