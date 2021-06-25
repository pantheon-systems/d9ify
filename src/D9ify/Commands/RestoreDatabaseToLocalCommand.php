<?php


namespace Pantheon\D9ify\Commands;

use Pantheon\D9ify\Exceptions\D9ifyExceptionBase;
use Robo\Common\ConfigAwareTrait;
use Robo\Tasks;

class RestoreDatabaseToLocalCommand extends Tasks {

    use ConfigAwareTrait;


    /**
     * @description
     * mysql {NEWSITE DATABASE CONNECTION INFO} < backup.tgz
     *
     */
    public function restoreDatabaseToDestinationSite($site, $env = "live")
    {
        $site_env = $site . "." . $env;

        $this->say('Creating backup on Pantheon.');
        $this->taskExec('vendor/bin/terminus.phar')
            ->args('backup:create', $site_env, '--element=db')->run();
        $this->say('Downloading backup file.');
        $this->taskExec('vendor/bin/terminus.phar')
            ->args('backup:get', $site_env, '--to=./local-copies/' . $site . '.sql.gz', '--element=db')
            ->run();
        $this->say('Unzipping and importing data');
        $mysql = "mysql";
        $mysql .= " -u " . $this->getConfig()->get('db_user');
        $mysql .= " -p" . $this->getConfig()->get('db_password');
        $mysql .= ' ' . $this->getConfig()->get('db_name');
        $this->_exec('gunzip < ' . $site . '.sql.gz | ' . $mysql);
        $this->say('Data Import complete, deleting db file.');
        $this->_exec('rm ' . $site . '.sql.gz');
    }
}
