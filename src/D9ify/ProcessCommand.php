<?php

namespace D9ify;

use Composer\IO\IOInterface;
use D9ify\Exceptions\D9ifyExceptionBase;
use D9ify\Site\Directory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * Class ProcessCommand
 *
 *
 *
 * @package D9ify
 */
class ProcessCommand extends Command
{

    /**
     * @var string
     */
    public static $HELP_TEXT = [
        "*******************************************************************************",
        "* THIS SCRIPT IS IN ALPHA VERSION STATUS AND AT THIS POINT HAS VERY LITTLE    *",
        "* ERROR CHECKING. PLEASE USE AT YOUR OWN RISK.                                *",
        "*******************************************************************************",
        "* This script searches for every {modulename}.info.yml. If that file has a    *",
        "* 'project' proerty (i.e. it's been thru the automated services at            *",
        "* drupal.org), it records that property and version number and ensures        *",
        "* those values are in the composer.json 'require' array. Your old composer    *",
        "* file will re renamed backup-*-composer.json.                                *",
        "*******************************************************************************",
        "* The guide to use this file is in /README.md                                 *",
        "*******************************************************************************",
    ];

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
     * @return \D9ify\Site\Directory
     */
    public function getSourceDirectory(): Directory
    {
        return $this->sourceDirectory;
    }

    /**
     * @param \D9ify\Site\Directory $sourceDirectory
     */
    public function setSourceDirectory(Directory $sourceDirectory): void
    {
        $this->sourceDirectory = $sourceDirectory;
    }

    /**
     *
     */
    protected function configure()
    {
        $this
            ->setName('d9ify')
            ->setDescription('The magic d9ificiation machine')
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
     * @return int|void
     * @throws \JsonException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $output->writeln(static::$HELP_TEXT);

            // Set Source & Dest
            $this->setSourceDirectory(
                Directory::ensure(
                    $input->getArgument('source'),
                    $output
                )
            );
            $this->setDestinationDirectory(
                Directory::ensure(
                    $input->getArgument('destination') ??
                    $this->sourceDirectory->getSiteInfo()->getName() . "-" . date('Y'),
                    $output
                )
            );

            // Copy base repositories over from the source composer.json
            $this->copyRepositoriesFromSource($input, $output);

            // Process contrib mods and add to new Composer
            $this->updateDestModulesAndThemesFromSource($input, $output);

            // Process /libraries folder if exists &
            // Add ES Libraries to the composer install payload
            $this->updateDestEsLibrariesFromSource($input, $output);

            // Write the composer file and try to do an install.
            // Exception will be thrown if install fails.
            $this->writeComposer($input, $output);
            $this->getDestinationDirectory()->install($output);


            // TODO:
            // 1. Copy Custom code: e.g.
            //      modules/custom,
            //      themes/custom,
            //      sites/all/modules/custom/*,
            //      sites/all/themes/custom/*
            //      ===> modules/custom, themes/custom,
            // 1. Spelunk custom code in new site and fix module
            //    version numbers (+ ^9) if necessary.
            // 1. Copy config files.
            // 1. commit-push code/config/composer additions.
            // 1. Rsync remote files to local directory
            // 1. Rsync remote files back up to new site
            // 1. Download database backup
            // 1. Restore Database backup to new site
        } catch (D9ifyExceptionBase $d9ifyException) {
            // TODO: Composer install exception help text
            $output->writeln((string) $d9ifyException);
            exit(1);
        } catch (\Exception $e) {
            // TODO: General help text and how to restart the process
            $output->writeln("Script ended in Exception state. " . $e->getMessage());
            $output->writeln($e->getTraceAsString());
            exit(1);
        } catch (\Throwable $t) {
            // TODO: General help text and how to restart the process
            $output->write("Script ended in error state. " . $t->getMessage());
            $output->writeln($t->getTraceAsString());
            exit(1);
        }
        exit(0);
    }

    protected function copyRepositoriesFromSource(InputInterface $input, OutputInterface $output)
    {
        $this->destinationDirectory->getComposerObject()->setRepositories(
            $this->sourceDirectory->getComposerObject()->getOriginal()['repositories'] ?? []
        );
        //$output->writeln(print_r($this->destinationDirectory->getComposerObject()->__toArray(), true));
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     */
    protected function updateDestModulesAndThemesFromSource(InputInterface $input, OutputInterface $output)
    {
        $infoFiles = $this->sourceDirectory->spelunkFilesFromRegex('/(\.info\.yml|\.info\.yaml?)/', $output);
        $toMerge = [];
        $composerFile = $this->getDestinationDirectory()
            ->getComposerObject();
        foreach ($infoFiles as $fileName => $fileInfo) {
            $contents = file_get_contents($fileName);
            preg_match('/project\:\ ?\'(.*)\'$/m', $contents, $projectMatches);
            preg_match('/version\:\ ?\'(.*)\'$/m', $contents, $versionMatches);
            if (is_array($projectMatches) && isset($projectMatches[1])) {
                if ($projectMatches[1]) {
                        $composerFile->addRequirement(
                            "drupal/" . $projectMatches[1],
                            "^" . str_replace("8.x-", "", $versionMatches[1])
                        );
                }
            }
        }
        $output->write(PHP_EOL);
        $output->write(PHP_EOL);
        $output->writeln([
            "*******************************************************************************",
            "* Found new Modules & themes from the source site:                            *",
            "*******************************************************************************",
            print_r($composerFile->getDiff(), true)
        ]);
        return 0;
    }

    /**
     * @return \D9ify\Site\Directory
     */
    public function getDestinationDirectory(): Directory
    {
        return $this->destinationDirectory;
    }

    /**
     * @param \D9ify\Site\Directory $destinationDirectory
     */
    public function setDestinationDirectory(Directory $destinationDirectory): void
    {
        $this->destinationDirectory = $destinationDirectory;
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @throws \JsonException
     */
    protected function updateDestEsLibrariesFromSource(InputInterface $input, OutputInterface $output)
    {
        $fileList = $this->sourceDirectory->spelunkFilesFromRegex('/libraries\/[0-9a-z-]*\/(package\.json$)/', $output);
        $repos = $this->sourceDirectory->getComposerObject()->getOriginal()['repositories'];
        $composerFile = $this->getDestinationDirectory()->getComposerObject();
        foreach ($fileList as $key => $file) {
            $package = \json_decode(file_get_contents($file->getRealPath()), true, 10, JSON_THROW_ON_ERROR);
            $repoString = (string) $package['name'];
            if (empty($repoString)) {
                $repoString = is_string($package['repository']) ?
                    $package['repository'] : $package['repository']['url'];
            }
            if (empty($repoString) || is_array($repoString)) {
                $output->writeln([
                    "*******************************************************************************",
                    "* Skipping the file below because the package.json file does not have         *",
                    "* a 'name' or 'repository' property. Add it by hand to the composer file.     *",
                    "* like so: \"npm-asset/{npm-registry-name}\": \"{Version Spec}\" in           *",
                    "* the REQUIRE section. Search for the id on https://www.npmjs.com             *",
                    "*******************************************************************************",
                    $file->getRealPath(),
                ]);
                continue;
            }
            $array = explode("/", $repoString);
            $libraryName = @array_pop($array);
            if (isset($repos[$libraryName])) {
                $composerFile->addRequirement(
                    $repos[$libraryName]['package']['name'],
                    $repos[$libraryName]['package']['version']
                );
                continue;
            }
            if ($libraryName !== "") {
                // Last ditch guess:
                $composerFile->addRequirement("npm-asset/" . $libraryName, "^" . $package['version']);
            }
        }
        $installPaths = $composerFile->getExtraProperty('installer-paths');
        if (!isset($installPaths['web/libraries/{$name}'])) {
            $installPaths['web/libraries/{$name}'] = [];
        }
        $installPaths['web/libraries/{$name}'] = array_unique(
            array_merge($installPaths['web/libraries/{$name}'] ?? [], [
                "type:bower-asset",
                "type:npm-asset",
            ])
        );

        $composerFile->setExtraProperty('installer-paths', $installPaths);
        $installerTypes = $composerFile->getExtraProperty('installer-types') ?? [];
        $composerFile->setExtraProperty(
            'installer-types',
            array_unique(
                array_merge($installerTypes, [
                    "bower-asset",
                    "npm-asset",
                    "library",
                ])
            )
        );
        $output->write(PHP_EOL);
        $output->write(PHP_EOL);
        $output->writeln([
            "*******************************************************************************",
            "* Found new ESLibraries from the source site:                                 *",
            "*******************************************************************************",
            print_r($composerFile->getDiff(), true)
            ]);
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int|mixed
     */
    protected function writeComposer(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([
            "*********************************************************************",
            "* These changes are being applied to the destination site composer: *",
            "*********************************************************************",
        ]);
        $output->writeln(print_r($this->destinationDirectory->getComposerObject()->getDiff(), true));
        $output->writeln(
            sprintf(
                "Write these changes to the composer file at %s?",
                $this->destinationDirectory->getComposerObject()->getRealPath()
            )
        );
        $question = new ConfirmationQuestion(" Type '(y)es' to continue: ", false);
        $helper = $this->getHelper('question');
        if ($helper->ask($input, $output, $question)) {
            return $this->getDestinationDirectory()
                ->getComposerObject()
                ->write();
        }
        $output->writeln("The composer Files were not changed");
        return 0;
    }

    /**
     * @return IOInterface|null
     */
    public function getComposerIOInterface(): ?IOInterface
    {
        return $this->composerIOInterface;
    }

    /**
     * @param IOInterface $composerIOInterface
     */
    public function setComposerIOInterface(IOInterface $composerIOInterface): void
    {
        $this->composerIOInterface = $composerIOInterface;
    }
}
