<?php

namespace D9ify\Composer;

use Composer\Semver\Comparator;

/**
 * Class Requirement
 *
 * @package D9ify\Composer
 */
class Requirement
{

    /**
     * @var string
     */
    protected string $packageName;

    /**
     * @var string
     */
    protected string $version;

    /**
     * Requirement constructor.
     *
     * @param $packageName
     * @param $version
     */
    public function __construct($packageName, $version)
    {
        $this->packageName = $packageName;
        $this->version = $version;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->packageName;
    }

    /**
     * @return string
     */
    public function getVersion(): string
    {
        return $this->getVersion();
    }

    /**
     * @param $version
     *
     * @return bool
     */
    public function isOlderThan($version): bool
    {
        return Comparator::greaterThan($this->version, $version);
    }

    /**
     * @param $version
     *
     * @return bool
     */
    public function isYoungerThan($version): bool
    {
        return Comparator::lessThan($this->version, $version);
    }
}
