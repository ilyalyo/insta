<?php
namespace TaskBundle\Command;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class FollowCommand extends ContainerAwareCommand
{


    protected function configure()
    {
        $this
            ->setName('action:follow')
            ->setDescription('Greet someone')
            ->addArgument(
                'id'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $id = $input->getArgument('id');
        $file = __DIR__ . "/action_new.php";
        shell_exec("php $file '" . $id . "' > /dev/null &");
    }
}