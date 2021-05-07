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
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    protected OutputInterface $output;

    /**
     * @var \D9ify\Site\Info
     */
    protected $info;

    /**
     * @var \SplFileInfo
     */
    protected $clonePath;

    /**
     * @var ComposerFile
     */
    protected ?ComposerFile $composerFile = null;

    /**
     * Directory constructor.
     *
     * @param string | \D9ify\Site\Info $site
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @throws \JsonException
     */
    public function __construct($site, OutputInterface $output, $org = null)
    {
        $this->setOutput($output);
        $this->setSiteInfo($site);
    }


    /**
     * @param string $site_id
     * @param null $org
     */
    public function setSiteInfo(string $site_id, $org = null): void
    {
        $this->setInfo(new Info($site_id, $org));
    }

    /**
     * @param string $site
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return static
     * @throws \JsonException
     */
    public static function factory(string $site, OutputInterface $output)
    {
        return new static($site, $output);
    }

    /**
     * @param $dir
     *
     * @return bool Success/Failure.
     */
    public static function delTree($dir): bool
    {
        if (is_dir($dir)) {
            $files = array_diff(scandir($dir), ['.', '..']);
            foreach ($files as $file) {
                (is_dir("$dir/$file")) ? static::delTree("$dir/$file") : unlink("$dir/$file");
            }
            return rmdir($dir);
        }
        return true;
    }

    /**
     * @throws \Exception
     */
    public function ensure(bool $create = false)
    {
        $valid = $this->getInfo()->valid();
        if ($valid === false) {
            // if site doesn't exist
            if ($create) {
                $valid = $this->getInfo()->create();
            }
            if ($valid === false) {
                throw new \Exception("Site does not exist and cannot be created.");
            }
        }
        $this->clonePath = new \SplFileInfo(getcwd() . "/" . $this->info->getName());
        if (!$this->clonePath->isDir()) {
            // -oStrictHostKeyChecking=no
            $$this->output->writeln(sprintf(
                "Local copy of site  %s does not exist... cloning...",
                $this->info->getName()
            ));
            // GET CONNECTION INFO
            $connectionInfo = $this->getConnectionInfo();
            exec(
                $connectionInfo['git_command'] . " -oStrictHostKeyChecking=no",
                $result,
                $status
            );
            if ($status !== 0) {
                throw new \Exception("Cannot clone site with terminus command." .
                    join(PHP_EOL, $result));
            }
        }
        $$this->getOutput()->writeln(
            sprintf(
                "Site Code Folder: %s",
                $this->clonePath->getRealPath()
            )
        );
        $this->setComposerFile();
    }

    /**
     * @return \D9ify\Site\Info
     */
    public function getInfo(): Info
    {
        return $this->info;
    }

    /**
     * @param \D9ify\Site\Info $info
     */
    public function setInfo(Info $info): void
    {
        $this->info = $info;
    }

    /**
     * @return mixed|null
     * @throws \JsonException
     */
    public function getConnectionInfo()
    {
        $command = sprintf(
            "%s connection:info %s.dev --format=json",
            "vendor/bin/terminus.phar",
            $this->info->getName()
        );
        exec($command, $result, $status);
        if ($status !== 0) {
            return null;
        }
        return json_decode(join("", $result), true, 10, JSON_THROW_ON_ERROR);
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
     * @return \D9ify\Site\ComposerFile
     */
    public function &getComposerObject(): ?ComposerFile
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
        return array_filter($allFiles, function (\SPLFileInfo $file) use (
            $regex,
            &$max,
            &$current,
            &
            $output
        ) {
            $this->progressBar($current++, $max, $output);
            return preg_match(
                $regex,
                $file->getRealPath()
            ) && !strpos(
                $file->getRealPath(),
                'test'
            );
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
        $write = sprintf(
            "\033[0G\033[2K[%'={$perc}s>%-{$left}s] - $perc%% - $done/$total",
            "",
            ""
        );
        $output->write($write);
    }

    /**
     * @param OutputInterface $output
     *
     * @return int
     * @throws \Exception
     */
    public function install(OutputInterface $output)
    {
        is_file($this->clonePath . "/composer.lock") ? unlink($this->clonePath . "/composer.lock") : [];
        static::delTree($this->clonePath . "/vendor");
        $command = sprintf(
            "cd %s && composer upgrade --with-dependencies",
            $this->clonePath
        );
        passthru($command, $result);
        if (!is_array($result)) {
            $result = [$result];
        }
        if ($result[0] !== 0) {
            throw new ComposerInstallException($result, $output);
        }
        return $result;
    }

    /**
     * @return \D9ify\Site\Info
     */
    public function getSiteInfo(): Info
    {
        return $this->info;
    }

    /**
     * @return \SplFileInfo
     */
    public function getClonePath(): \SplFileInfo
    {
        return $this->clonePath;
    }

    /**
     * @param \SplFileInfo $clonePath
     */
    public function setClonePath(\SplFileInfo $clonePath): void
    {
        $this->clonePath = $clonePath;
    }

    /**
     * @return \Symfony\Component\Console\Output\OutputInterface
     */
    public function getOutput(): OutputInterface
    {
        return $this->output;
    }

    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    public function setOutput(OutputInterface $output): void
    {
        $this->output = $output;
    }
}
