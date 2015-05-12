<?php
namespace TaskBundle\Command;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CasperFollowCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('casper:follow')
            ->addArgument(
                'username'
            )->addArgument(
                'password'
            )->addArgument(
                'task_id'
            )->addArgument(
                'wait'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $username = $input->getArgument('username');
        $password = $input->getArgument('password');
        $task_id = $input->getArgument('task_id');
        $wait = $input->getArgument('wait');
        $file = __DIR__ . "/Casper/follow.js";
        shell_exec("casperjs $file '" . $username . "' '" . $password ."' '" . $task_id . "' '" . $wait . "' > /dev/null &");
    }
}