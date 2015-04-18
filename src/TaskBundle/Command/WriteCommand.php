<?php
namespace TaskBundle\Command;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class WriteCommand extends ContainerAwareCommand
{


    protected function configure()
    {
        $this
            ->setName('demo:write')
            ->setDescription('Greet someone')
            ->addArgument(
                'id',
                InputArgument::OPTIONAL,
                'Who do you want to greet?'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $id = $input->getArgument('id');
        $file = __DIR__ . "/action.php";
        shell_exec("php $file '" . $id . "' > /dev/null &");
    }
}