<?php

namespace D9ify;

use D9ify\Site\Directory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
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
   * @var string
   */
    protected static $defaultName = 'd9ify';

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
        new InputArgument('source', InputArgument::REQUIRED, "Pantheon Site Name or Site ID of the source"),
        new InputArgument('destination', InputArgument::OPTIONAL, "Pantheon Site Name or Site ID of the destination"),
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
        $output->writeln(static::$HELP_TEXT);
        $this->setSourceDirectory(Directory::ensure($input->getArgument('source'), $output));
        $this->setDestinationDirectory(Directory::ensure(
            $input->getArgument('destination') ??
            $this->sourceDirectory->getSiteInfo()->getName() . "-" . date('Y'),
            $output
        ));
        $this->updateDestModulesAndThemesFromSource($input, $output);
        $this->updateDestEsLibrariesFromSource($input, $output);
        return $this->endWrite($input, $output);
    }

  /**
   * @param \Symfony\Component\Console\Input\InputInterface $input
   * @param \Symfony\Component\Console\Output\OutputInterface $output
   *
   * @return int|mixed
   */
    protected function endWrite(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([
        "*********************************************************************",
        "* These changes are being applied to the destination site composer: *",
        "*********************************************************************",
        ]);
        $output->writeln($this->destinationDirectory->getComposerObject()->getDiff());
        $output->writeln(
            sprintf(
                "Write these changes to the composer file at %s?",
                $this->destinationDirectory->getComposerObject()->getRealPath()
            )
        );
        $question = new ConfirmationQuestion(" Type '(y)es' to continue: ", false);
        $helper = $this->getHelper('question');
        if ($helper->ask($input, $output, $question)) {
            return $this->getDestinationDirectory()->getComposerObject()->writeFile($output);
        }
        $output->writeln("The composer Files were not changed");
        return 0;
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
        foreach ($infoFiles as $fileName => $fileInfo) {
            $contents = file_get_contents($fileName);
            preg_match('/project\:\ ?\'(.*)\'$/m', $contents, $projectMatches);
            preg_match('/version\:\ ?\'(.*)\'$/m', $contents, $versionMatches);
            if (is_array($projectMatches) && isset($projectMatches[1])) {
                if ($projectMatches[1]) {
                    $toMerge['require'][ "drupal/" . $projectMatches[1] ] =
                      "^" . str_replace("8.x-", "", $versionMatches[1]);
                }
            }
        }
        $this->getDestinationDirectory()->getComposerObject()->addChange($toMerge);
        $output->write(PHP_EOL);
        $output->write(PHP_EOL);
        $output->writeln("Found new modules from old site:");
        $output->writeln(print_r($toMerge['require'], true));
        return 0;
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
        $toMerge = [];
        foreach ($fileList as $key => $file) {
            $package = \json_decode(file_get_contents($file->getRealPath()), true, 10, JSON_THROW_ON_ERROR);
            if (isset($package['name'])) {
                $libraryName = @array_pop(explode("/", $package['name']));
                $toMerge['require']["npm-asset/" . $libraryName] = "^" . $package['version'];
            }
        }

        $toMerge['extra']['installer-paths']['web/libraries/{$name}'] = [
        "type:bower-asset",
        "type:npm-asset",
        ];
        $output->writeln("Found new ESLibraries from old site:");
        $output->writeln(print_r($toMerge, true));
        $this->getDestinationDirectory()->getComposerObject()->addChange($toMerge);
    }
}
