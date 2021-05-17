<?php

namespace D9ify;

use Symfony\Component\Console\Application as SymfApp;
use D9ify\Commands\DocumentCommand;
use D9ify\Commands\ProcessCommand;
use Symfony\Component\Console\Command\Command;

/**
 * Class Application
 *
 * @package D9ify
 */
class Application extends SymfApp
{

    /**
     * @param \Composer\Script\Event $event
     *
     * @return int
     * @throws \Exception
     */
    public static function process(\Composer\Script\Event $event)
    {
        $arguments = $event->getArguments();
        $process = new ProcessCommand();
        $process->setComposerIOInterface($event->getIO());
        $GLOBALS['app'] = new static();
        $GLOBALS['app']->add($process);
        $GLOBALS['app']->setDefaultCommand($process->getName(), true);
        return $GLOBALS['app']->run();
    }

    /**
     * @param \Composer\Script\Event $event
     *
     * @return int
     * @throws \Exception
     */
    public static function document(\Composer\Script\Event $event)
    {
        $arguments = $event->getArguments();
        $document = new DocumentCommand();
        $GLOBALS['app'] = new static();
        $GLOBALS['app']->add($document);
        $GLOBALS['app']->setDefaultCommand($document->getName(), true);
        return $GLOBALS['app']->run();
    }
}
