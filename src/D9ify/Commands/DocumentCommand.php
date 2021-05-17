<?php


namespace D9ify\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DocumentCommand extends Command
{


    public function configure()
    {
        $this
            ->setName('d9ify:document')
            ->setDescription('The magic documentation of the d9ification machine');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $reflector = new \ReflectionClass(ProcessCommand::class);
        $comments = $reflector->getMethod('execute')->getDocComment();
        print_r($comments);
    }
}
