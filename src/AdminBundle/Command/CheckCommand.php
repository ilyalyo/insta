<?php
namespace AdminBundle\Command;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CheckCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this->setName('task:get_list');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $text = shell_exec("ps -aux 2>/dev/null | grep follow.php |  awk '{print $13}' | grep -v ps | sort");
        $output->writeln($text);
    }
}