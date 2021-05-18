<?php


namespace D9ify\Commands;

use D9ify\Utility\DocBlock;
use D9ify\Utility\DocumentationRenderer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @class DocumentCommand
 *
 * @package D9ify\Commands
 */
class DocumentCommand extends Command
{

    /**
     *
     */
    public function configure()
    {
        $this
            ->setName('d9ify:document')
            ->setDescription('The magic documentation of the d9ification machine');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int|void|null
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $reflector = new \ReflectionClass(ProcessCommand::class);
        $heading = new DocBlock($reflector->getDocComment());
        $methods = $reflector->getMethods();
        $comments = [
            new DocumentationRenderer($heading),
        ];
        foreach ($methods as $method) {
            $docBlock = new DocBlock($method->getDocComment());
            if (isset($docBlock->step) && !empty($docBlock->step)) {
                $comments[] = new DocumentationRenderer($docBlock);
            }
        }
        file_put_contents("README.md", $comments);
        $output->writeln("Documention regenerated.");
    }
}
