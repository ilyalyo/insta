<?php
namespace AppBundle\Command;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AuthCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('casper:auth')
            ->addArgument(
                'username'
            )
            ->addArgument(
                'password'
            )
            ->addArgument(
                'proxy'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $username = $input->getArgument('username');
        $password = $input->getArgument('password');
        $proxy = $input->getArgument('proxy');
        $file = __DIR__ . "/Casper/auth.js";
        shell_exec("casperjs --web-security=no $file '" . $username . "' '" . $password ."' --proxy=" . $proxy . " --proxy-type=socks5 > /dev/null");
    }
}