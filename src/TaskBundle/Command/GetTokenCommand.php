<?php
namespace TaskBundle\Command;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GetTokenCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('casper:gettoken')
            ->addArgument(
                'username'
            )
            ->addArgument(
                'password'
            )
            ->addArgument(
                'client'
            )

        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $username = $input->getArgument('username');
        $password = $input->getArgument('password');
        $client = $input->getArgument('client');
        $file = __DIR__ . "/Casper/get_token.js";
        shell_exec("casperjs $file '" . $username . "' '" . $password ."' '" . $client . "' ");
    }
}