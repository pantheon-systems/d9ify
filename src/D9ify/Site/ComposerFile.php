<?php


namespace D9ify\Site;

use Symfony\Component\Console\Helper\HelperInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class ComposerFile extends \SplFileObject {

  protected array $original;
  protected array $changes;

  public function __construct($filename, $mode = 'r', $useIncludePath = FALSE, $context = NULL) {
    parent::__construct($filename, $mode, $useIncludePath, $context);
    $this->setOriginal();
  }

  public function setOriginal() {
    $this->original = \json_decode(file_get_contents($this->getRealPath()), TRUE, 5, JSON_THROW_ON_ERROR);
  }

  public function getOriginal() {
    return $this->original;
  }

  public function addChange($newValues){
    $this->changes = array_merge($this->changes, $newValues);
  }

  public function ensureRequired($name, $version) {
    if (!$this->isRequired($name)) {
      $this->addChange(['require' => [$name => $version]]);
    }
  }

  public function isRequired($module) {
    return isset($this->original['require'][$module]);
  }

  public function writeFile(OutputInterface $output) {
    try {
      $backupName = dirname($this->getRealPath()) . "/composer-backup-". uniqid() . ".json";
      copy($this->getRealPath(), $backupName);
      $command = "echo \"$backupName\" >> " . dirname($this->getRealPath()) . "/.gitignore";
      exec($command, $return, $status);
      $output->writeln( "Backup of composer file created: {$backupName}" );
      file_put_contents($this->getRealPath(), json_encode($this->getMergedValues(), JSON_PRETTY_PRINT, 5));
      $output->writeln( "Destination Site Composer File Updated with new settings.");
      return 0;
    } catch (\Exception $e) {
      echo $e->getMessage();
      return $e->getCode();
    } catch (\Throwable $t) {
      echo $t->getMessage();
      return $t->getCode();
    }
  }

  public function getMergedValues() {
    return array_merge($this->original, $this->changes);
  }

}
