<?php
namespace AppBundle\Command;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AuthCheckCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('casper:checkauth')
            ->addArgument(
                'proxy'
            )
            ->addArgument(
                'username'
            )
            ->addArgument(
                'password'
            )

        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $username = $input->getArgument('username');
        $password = $input->getArgument('password');
        $proxy = $input->getArgument('proxy');
        $file = __DIR__ . "/Casper/check_auth.js";
        $text = shell_exec("casperjs --web-security=no $file '" . $username . "' '" . $password ."' --proxy=" . $proxy . " --proxy-type=socks5");
        $output->writeln($text);;
    }
}