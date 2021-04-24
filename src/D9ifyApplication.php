<?php

use Symfony\Component\Console\Application;
use D9ify\ProcessCommand;

class D9ifyApplication extends Application {

  public static function process(\Composer\Script\Event $event) {
    $arguments = $event->getArguments();
    $site_id = sprintf("%s", end($arguments));
    $process = new ProcessCommand();
    $GLOBALS['app'] = new static();
    $GLOBALS['app']->add($process);
    $GLOBALS['app']->setDefaultCommand($process->getName(), true);
    $GLOBALS['app']->run();
  }

}
