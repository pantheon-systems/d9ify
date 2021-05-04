<?php

namespace D9ify\Site;

use D9ify\Composer\ComposerFile;
use D9ify\Exceptions\ComposerInstallException;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Directory
 *
 * @package D9ify\Site
 */
class Directory
{

    /**
     * @var \D9ify\Site\Info
     */
    protected $info;

    /**
     * @var \SplFileInfo
     */
    protected $clonePath;

    /**
     * @var
     */
    protected $composerFile;

    /**
     * Directory constructor.
     *
     * @param \D9ify\Site\Info $site
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @throws \JsonException
     */
    public function __construct(string|InfoInterface $site, OutputInterface $output)
    {
        $this->setSiteInfo($site);

        $this->clonePath = new \SplFileInfo(getcwd() . "/" . $this->info->getName());
        if (!$this->clonePath->isDir()) {
            // -oStrictHostKeyChecking=no
            $output->writeln(sprintf("Local copy of site  %s does not exist... cloning...", $this->info->getName()));
            $command = sprintf("terminus connection:info %s.dev --format=json", $this->info->getName());
            exec($command, $result, $status);
            if ($status !== 0) {
                throw new \Exception("Cannot get command to clone site. " . join(PHP_EOL, $output));
            }
            $connectionInfo = json_decode(join("", $result), true, 10, JSON_THROW_ON_ERROR);
            exec($connectionInfo['git_command'] . " -oStrictHostKeyChecking=no", $result, $status);
            if ($status !== 0) {
                throw new \Exception("Cannot clone site with terminus command." . join(PHP_EOL, $result));
            }
        }
        $output->writeln(sprintf("Site Code Folder: %s", $this->clonePath->getRealPath()));
        $this->setComposerFile();
    }

    /**
     * @param string|InfoInterface $site_id
     */
    public function setSiteInfo(string|InfoInterface $site_id): void
    {
        if (is_string($site_id)) {
            $this->info = new Info($site_id);
            return;
        }
        $this->info = $site_id;
    }

    /**
     * @throws \Exception
     */
    public function setComposerFile()
    {
        $this->composerFile = new ComposerFile($this->getComposerFileExpectedPath());
    }

    /**
     * @return string
     */
    private function getComposerFileExpectedPath()
    {
        return sprintf("%s/%s/composer.json", getcwd(), $this->info->getName());
    }

    /**
     * @param \D9ify\Site\Info $site
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return static
     * @throws \JsonException
     */
    public static function ensure(string $site, OutputInterface $output)
    {
        return new static(new Info($site), $output);
    }

    /**
     * @return \D9ify\Site\ComposerFile
     */
    public function &getComposerObject(): ComposerFile
    {
        return $this->composerFile;
    }

    /**
     * @param $regex
     *
     * @return array
     */
    public function spelunkFilesFromRegex($regex, OutputInterface $output)
    {
        $output->writeln(sprintf("Searching files for regex: %s", $regex));
        $allFiles = iterator_to_array(
            new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($this->clonePath)
            )
        );
        $max = count($allFiles);
        $current = 0;
        return array_filter($allFiles, function (\SPLFileInfo $file) use ($regex, &$max, &$current, &$output) {
            $this->progressBar($current++, $max, $output);
            return preg_match($regex, $file->getRealPath()) && !strpos($file->getRealPath(), 'test');
        });
    }

    /**
     * @param $done
     * @param $total
     */
    protected function progressBar($done, $total, OutputInterface $output)
    {
        $perc = floor(($done / $total) * 100);
        $left = 100 - $perc;
        $write = sprintf("\033[0G\033[2K[%'={$perc}s>%-{$left}s] - $perc%% - $done/$total", "", "");
        $output->write($write);
    }

    /**
     * @param OutputInterface $output
     * @return int
     * @throws \Exception
     */
    public function install(OutputInterface $output)
    {
        is_file($this->clonePath . "/composer.lock") ? unlink($this->clonePath . "/composer.lock") : [];
        static::delTree($this->clonePath . "/vendor");
        $command = sprintf("cd %s && composer upgrade --with-dependencies", $this->clonePath);
        passthru($command, $result);
        if ($result !== 0) {
            throw new ComposerInstallException($result, $output);
        }
        return $result;
    }

    /**
     * @param $dir
     * @return bool Success/Failure.
     */
    public static function delTree($dir): bool
    {
        if (is_dir($dir)) {
            $files = array_diff(scandir($dir), array('.', '..'));
            foreach ($files as $file) {
                (is_dir("$dir/$file")) ? static::delTree("$dir/$file") : unlink("$dir/$file");
            }
            return rmdir($dir);
        } return true;
    }

    /**
     * @return \D9ify\Site\Info
     */
    public function getSiteInfo(): Info
    {
        return $this->info;
    }
}
