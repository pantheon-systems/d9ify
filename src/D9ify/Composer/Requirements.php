<?php

namespace D9ify\Composer;

class Requirements extends ComposerSectionBase
{


    public function ensureRequirement(string $requirement, string $version): string
    {
        if (isset($this->requirements[$requirement])) {
            $this->requirements[$requirement] =
            version_compare($this->requirements[$requirement], $version)
            ? $this->requirements[$requirement] : $version;
            return $this->requirements[$requirement];
        }
        $this->requirements[$requirement] = $version;
        return $this->requirements[$requirement];
    }
}
