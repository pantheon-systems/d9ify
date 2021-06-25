<?php

namespace Pantheon\D9ify\Commands;


use Robo\Tasks;

class CopyRespositoryFromSource extends Tasks {


    /**
     * @command develop:copyRepo
     * @step Clone Source & Destination.
     * @description
     * Clone both sites to folders inside this root directory.
     * If destination does not exist, create the using Pantheon's
     * Terminus API. If destination doesn't exist, Create it.
     *
     * @param string $site
     */
    protected function copyRepositoryFromSource(string $site)
    {
        $this->output()->writeln([
            "===> Ensuring source and destination folders exist.",
            PHP_EOL,
            "*********************************************************************",
            "**     If you've never accessed the site before you may be         **",
            "**  asked to accept the site's fingerprint. Type 'yes' when asked  **",
            "*********************************************************************",
            PHP_EOL,
        ]);
        $this->getSourceDirectory()->ensure(false);
        $this->getDestinationDirectory()->ensure(true);
        $this->destinationDirectory->getComposerObject()->setRepositories(
            $this->sourceDirectory->getComposerObject()->getOriginal()['repositories'] ?? []
        );
        $this->output()->writeln([
            "*********************************************************************",
            sprintf("Source Folder: %s", $this->getSourceDirectory()->getClonePath()),
            sprintf("Destination Folder: %s", $this->getDestinationDirectory()->getClonePath()),
            "*********************************************************************",
        ]);
    }

}
