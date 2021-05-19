<?php


namespace D9ify\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CommitAndPushCommand
 *
 * @package D9ify\Commands
 */
class CommitAndPushCommand extends Command
{

    public static $HELP_TEXT = "";

    /**
     * Configure.
     */
    protected function configure()
    {
        $this->setName('d9ify:commitAndPush')
            ->setDescription('Finalize the changes you made with "process" ')
            ->addArgument('source', InputArgument::REQUIRED, 'The pantheon site name or ID of the site')
            ->setHelp(static::$HELP_TEXT)
            ->setDefinition(new InputDefinition([
                new InputArgument(
                    'source',
                    InputArgument::REQUIRED,
                    "Pantheon Site Name or Site ID of the source"
                ),
                new InputArgument(
                    'destination',
                    InputArgument::OPTIONAL,
                    "Pantheon Site Name or Site ID of the destination"
                ),
            ]));
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->restoreDatabaseToDestinationSite($input, $output);
        $this->unpackSiteFilesAndRsyncToDestination($input, $output);
        $this->checkinVersionManagedFilesAndPush($input, $output);
    }


    /**
     * @step TODO: restore database backup to destination site
     * @description
     * mysql {NEWSITE DATABASE CONNECTION INFO} < backup.tgz
     *
     */
    public function restoreDatabaseToDestinationSite(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("===> TODO: Restore database to destination");
    }

    /**
     * @step TODO: unpack site files archive and rsync them up.
     * @description
     * There's a hard limit to the size archive you can upload. We'll do an rysnc
     * but if/when it times out, we need a way of restarting the rsync.
     *
     */
    public function unpackSiteFilesAndRsyncToDestination(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("===> TODO: unpack files archive and rsync to destination");
    }

    /**
     * @step TODO: check in the version-managed files
     * @description
     * Push them up to dev environment.
     *
     */
    public function checkinVersionManagedFilesAndPush(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("===> TODO: Check-in Version-managed files and push.");
    }
}
