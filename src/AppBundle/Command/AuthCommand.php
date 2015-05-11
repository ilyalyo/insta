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
                'username',
                'pass',
                'account_id',
                InputArgument::OPTIONAL,
                'Who do you want to greet?'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $username = $input->getArgument('username');
        $password = $input->getArgument('pass');
        $account_id = $input->getArgument('account_id');
        $file = __DIR__ . "Casper/auth.php";
        shell_exec("casperjs $file '" . $username . "' '" . $password ."' '" . $account_id . "' > /dev/null &");
    }
}