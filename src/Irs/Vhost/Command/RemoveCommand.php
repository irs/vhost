<?php

namespace Irs\Vhost\Command;

use Irs\Vhost\ApacheController;
use Symfony\Component\Console\Input\InputArgument;

use Irs\Vhost\HostsFile;

use Irs\Vhost\ConfigurationFile;

use Symfony\Component\Console\Output\OutputInterface,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Input\InputOption,
    Symfony\Component\Console\Command\Command;

/**
 * Command for virtual host removing
 *
 * @author Vadim Kusakin
 */
class RemoveCommand extends Command
{
	private $configuration;
	private $hosts;

	public function __construct(ConfigurationFile $conf, HostsFile $hosts)
	{
        parent::__construct();

        $this->configuration = $conf;
        $this->hosts = $hosts;
	}

    protected function configure()
    {
    	$this->setName('host:remove')
    	    ->setDescription('Removes host by server name.')
    	    ->addArgument('name', InputArgument::REQUIRED, 'Host name.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
    	$host = $this->configuration->getHostByName($input->getArgument('name'));
        $this->configuration->removeHost($host);
        $this->configuration->save();
    	$output->writeln('<info>Successfully removed.</info>');

    	$apache = new ApacheController();
    	$apache->restart();
    	$output->writeln('<info>Apache restarted.</info>');
    }
}