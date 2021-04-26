<?php

namespace D9ify\Composer;

use Jfcherng\Diff\Differ;
use Jfcherng\Diff\DiffHelper;
use Jfcherng\Diff\Factory\RendererFactory;
use Jfcherng\Diff\Renderer\RendererConstant;
use Nadar\PhpComposerReader\ComposerReader;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ComposerFile
 *
 * @package D9ify\Site
 */
class ComposerFile extends \SplFileObject
{




  /**
   * @var array
   */
    protected array $original = [];

  /**
   * @var \Nadar\PhpComposerReader\ComposerReader
   */
    protected ComposerReader $changes;

  /**
   * ComposerFile constructor.
   *
   * @param $filename
   * @param string $mode
   * @param false $useIncludePath
   * @param null $context
   *
   * @throws \JsonException
   */
    public function __construct($filename, $mode = 'r', $useIncludePath = false, $context = null)
    {
        parent::__construct($filename, $mode, $useIncludePath, $context);
        $this->setOriginal();
    }

  /**
   * @throws \JsonException
   */
    public function setOriginal()
    {
        $this->original = \json_decode(\file_get_contents($this->getRealPath()), true, 10, JSON_THROW_ON_ERROR);
        $this->changes = new ComposerReader($this->getRealPath());
    }

  /**
   * @return array
   */
    public function getOriginal()
    {
        return $this->original;
    }

  /**
   * @param array $newValues
   */
    public function addChange(array $newValues)
    {
        foreach ($newValues as $section => $values) {
            $this->changes->updateSection($section, $values);
        }
    }

  /**
   * @param \Symfony\Component\Console\Output\OutputInterface $output
   *
   * @return int|mixed
   */
    public function writeFile(OutputInterface $output)
    {
        try {
            $backupName = dirname($this->getRealPath()) . "/composer-backup-" . uniqid() . ".json";
            copy($this->getRealPath(), $backupName);

            $command = "echo \"$backupName\" >> " . dirname($this->getRealPath()) . "/.gitignore";
            exec($command, $return, $status);
            $output->writeln("Backup of composer file created: {$backupName}");
            $this->changes->save();
            $output->writeln("Destination Site Composer File Updated with new settings.");
            return 0;
        } catch (\Exception $e) {
            echo $e->getMessage();
            return $e->getCode();
        } catch (\Throwable $t) {
            echo $t->getMessage();
            return $t->getCode();
        }
    }

    public function getDiff()
    {
        $orig = $this->original;
        $changed = $this->changes->getContent();

        $helperResult = DiffHelper::calculate($this->original, $this->changes->getContent());
        print_r($helperResult);
        exit();
    }
}
