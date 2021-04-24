<?php

namespace D9ify;

use D9ify\Site\Directory;
use D9ify\Site\Info;
use Drupal\search_api\Plugin\search_api\parse_mode\Direct;
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
class ProcessCommand extends Command {

  static $HELP_TEXT = <<<EOF

   // TODO Write helptext

EOF;

  protected Directory $sourceDirectory;
  protected Directory $destinationDirectory;

  /**
   * @return \D9ify\Site\Directory
   */
  public function getSourceDirectory(): Directory {
    return $this->sourceDirectory;
  }

  /**
   * @param \D9ify\Site\Directory $sourceDirectory
   */
  public function setSourceDirectory(Directory $sourceDirectory): void {
    $this->sourceDirectory = $sourceDirectory;
  }

  /**
   * @return \D9ify\Site\Directory
   */
  public function getDestinationDirectory(): Directory {
    return $this->destinationDirectory;
  }

  /**
   * @param \D9ify\Site\Directory $destinationDirectory
   */
  public function setDestinationDirectory(Directory $destinationDirectory): void {
    $this->destinationDirectory = $destinationDirectory;
  }


  /**
   * @var string
   */
  protected static $defaultName = 'd9ify';

  /**
   *
   */
  protected function configure() {
    $this
      ->setName('d9ify')
      ->setDescription('The magic d9ificiation machine')
      ->addArgument('source', InputArgument::REQUIRED, 'The pantheon site name or ID of the site')
      ->setHelp(static::$HELP_TEXT)
      ->setDefinition(new InputDefinition([
        new InputArgument('source', InputArgument::REQUIRED, "Pantheon Site Name or Site ID of the source" ),
        new InputArgument('destination', InputArgument::OPTIONAL, "Pantheon Site Name or Site ID of the destination"),
      ]));
  }

  /**
   * @param \Symfony\Component\Console\Input\InputInterface $input
   * @param \Symfony\Component\Console\Output\OutputInterface $output
   *
   * @return int|void
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $this->setSourceDirectory(Directory::ensure( $input->getArgument('source'), $output));
    $this->setDestinationDirectory(Directory::ensure(
      $input->getArgument('destination') ??
        $this->sourceDirectory->getSiteInfo()->getName() . "-" . date('Y'),
      $output
    ));
    $this->updateDestModulesAndThemesFromSource();

    $this->endWrite($input, $output);
  }

  protected function endWrite(InputInterface $input, OutputInterface $output) {
    $output->writeln("The following changes are being applied to the destination site composer: ");
    $question = new ConfirmationQuestion("Write these changes to the composer file at {$this->getRealPath()}?  Type 'yes' to continue: ", false);
    $helper = $this->getHelper('question');
    if (!$helper->ask($input, $output, $question)) {
      return $this->getDestinationDirectory()->getComposerObject()->writeFile($output);
    }
    $output->writeln("The composer Files were not changed");
    return 0;
  }

  /**
   * @param $infoFiles
   */
  function updateDestModulesAndThemesFromSource(InputInterface $input,OutputInterface $output) {
    $infoFiles = $this->sourceDirectory->spelunkFilesFromRegex('/(\.info\.yml|\.info\.yaml?)/');
    $toMerge = [];
    foreach ($infoFiles as $fileName => $fileInfo) {
      $contents = file_get_contents($fileName);
      preg_match('/project\:\ ?\'(.*)\'$/m', $contents, $projectMatches);
      preg_match('/version\:\ ?\'(.*)\'$/m', $contents, $versionMatches);
      if (is_array($projectMatches) && isset($projectMatches[1])) {
        if ($projectMatches[1]) {
          $toMerge['require'][ "drupal/" . $projectMatches[1] ] = "^" . str_replace("8.x-", "", $versionMatches[1]);
        }
      }
    }
    $this->getDestinationDirectory()->getComposerObject()->addChange($toMerge);
  }

  /**
   * @param array $newComposerSettings
   *
   * @throws \JsonException
   */
  function deepMergeWithDestinationSiteComposer(array $newComposerSettings) {
    try {

      $destComposerToWrite = array_merge_recursive($newComposerSettings, $destSiteComposerContents);
      $composerPath = getcwd() . "/" . DESTINATION_SITE_INFO['name'] . '/composer.json';
      $composerBackup = dirname($composerPath) . "/composer-backup-". uniqid() . ".json";
      copy($composerPath, $composerBackup);
      $command = "echo \"$composerBackup\" >> " . dirname($composerPath) . "/.gitignore";
      exec($command, $output, $status);
      echo "Backup of composer file created: {$composerBackup}" . PHP_EOL;
      file_put_contents($composerPath, json_encode($destComposerToWrite, JSON_PRETTY_PRINT, 5));
      echo "The following changes are being applied to the destination site composer: " . print_r($diff, true);
      echo "Write these changes to the composer file at {$composerPath}?  Type 'yes' to continue: ";
      $handle = fopen ("php://stdin","r");
      $line = fgets($handle);
      if(trim($line) != 'yes'){
        echo "ABORTING!\n";
        exit;
      }
      echo "Destination Site Composer File Updated with new settings." . PHP_EOL;
      return true;
    } catch (\Exception $e) {
      echo $e->getMessage();
      exit();
    } catch (\Throwable $t) {
      echo $t->getMessage();
      exit();
    }
    return false;
  }


}
