<?php

namespace Pantheon\D9ify\Commands;

use Composer\Command\BaseCommand;
use Composer\IO\IOInterface;
use Pantheon\D9ify\Exceptions\D9ifyExceptionBase;
use Pantheon\D9ify\Site\Directory;
use Robo\Symfony\ConsoleIO;
use Robo\Tasks;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Yaml\Yaml;

/**
 * @class DevelopCommand
 * @name d9ify::develop
 * @description
 *
 * This Command allows you to clone a site from a terminus source.
 *
 *
 * | WARNING                                                                     |
 * |-----------------------------------------------------------------------------|
 * | THIS PROJECT IS IN ALPHA VERSION STATUS AND AT THIS POINT HAS VERY LITTLE   |
 * | ERROR CHECKING. PLEASE USE AT YOUR OWN RISK.                                |
 * | The guide to use this file is in /README.md                                 |
 *
 *
 * ![Passing Tests](https://github.com/stovak/d9ify/actions/workflows/php.yml/badge.svg)
 *
 * @usage
 * 1. clone this repo
 * 2. composer install --no-dev
 * 3. composer d9ify:develop {PANTHEON_SITE_ID}
 *
 * NOTE: `composer install` will fail on windows unless you use the --no-dev switch.
 *
 * @package D9ify
 */
class DevelopCommand extends Tasks
{

    /**
     * @var string
     */
    public static $HELP_TEXT = [
        "*******************************************************************************",
        "* THIS PROJECT IS IN ALPHA VERSION STATUS AND AT THIS POINT HAS VERY LITTLE   *",
        "* ERROR CHECKING. PLEASE USE AT YOUR OWN RISK.                                *",
        "* The guide to use this file is in /README.md                                 *",
        "*******************************************************************************",
    ];

    /**
     * @var \Composer\IO\IOInterface|null
     */
    protected ?IOInterface $composerIOInterface = null;

    /**
     * @var string
     */
    protected static $defaultName = 'd9ify';
    /**
     * @var \D9ify\Site\Directory
     */
    protected Directory $sourceDirectory;
    /**
     * @var \D9ify\Site\Directory
     */
    protected Directory $destinationDirectory;

    /**
     * @command develop
     * @description
     * Clone a pantheon site and copy templates for local develpoment.
     *
     * @param string $site
     *
     * @return int|void
     * @throws \JsonException
     */
    public function develop($site)
    {
        try {
            if ($this->io()->isVerbose()) {
                $io->writeln(__CLASS__ . " STARTED");
            }
            $this->output()->writeln(static::$HELP_TEXT);
            $this->setSourceDirectory(
                Directory::factory(
                    $site,
                    $this->io()->output()
                )
            );
            if ($this->output()->isVerbose()) {
                $this->output()->writeln(__CLASS__ . " SOURCE DIR SET");
            }
            $this->getSourceDirectory()->ensure(false);
            if ($this->output()->isVerbose()) {
                $this->output()->writeln(__CLASS__ . " SOURCE DIR ENSURED");
            }
            $this->copyTemplatesForLocalDevelopment($io->output());
        } catch (D9ifyExceptionBase $d9ifyException) {
            // TODO: Composer install exception help text
            $this->output()->writeln((string) $d9ifyException);
            exit(1);
        } catch (\Exception $e) {
            // TODO: General help text and how to restart the process
            $this->output()->writeln("Script ended in Exception state. " . $e->getMessage());
            $this->output()->writeln($e->getTraceAsString());
            exit(1);
        } catch (\Throwable $t) {
            // TODO: General help text and how to restart the process
            $this->output()->write("Script ended in error state. " . $t->getMessage());
            $this->output()->writeln($t->getTraceAsString());
            exit(1);
        }
        return 0;
    }

    /**
     * @step Set Source directory
     * @description
     * Source Param is not optional and needs to be
     * a pantheon site ID or name.
     *
     * @param \D9ify\Site\Directory $sourceDirectory
     */
    protected function setSourceDirectory(Directory $sourceDirectory): void
    {
        $this->sourceDirectory = $sourceDirectory;
    }

    /**
     * @return \D9ify\Site\Directory
     */
    public function getSourceDirectory(): Directory
    {
        return $this->sourceDirectory;
    }


    protected function copyTemplatesForLocalDevelopment(OutputInterface $output)
    {
        print_r($this);
        exit();
    }
}
