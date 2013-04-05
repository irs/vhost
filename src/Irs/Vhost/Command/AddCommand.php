<?php

namespace Irs\Vhost\Command;

use Irs\Vhost\Host;
use Irs\Vhost\ApacheController;
use Irs\Vhost\InputHelper;
use Irs\Vhost\HostsFile;
use Irs\Vhost\ConfigurationFile;

use Symfony\Component\Console\Helper\DialogHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Input\InputOption,
    Symfony\Component\Console\Command\Command;

/**
 * Command for virtual host adding
 *
 * @author Vadim Kusakin
 */
class AddCommand extends Command
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
    	$this->setName('host:add')
    	    ->setDescription('Adds host.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
    	$helper = new InputHelper($output, $this->getHelper('dialog'), $this->configuration, $this->hosts);

    	$host = new Host(
        	$hostName = $helper->getHostName(),
            $helper->getDocRoot(),
            $helper->getLog('error', $hostName),
            $helper->getLog('custom', $hostName)
		);

    	$this->configuration->addHost($host);
    	$this->configuration->save();
    	$output->writeln('<info>Successfully added.</info>');

    	$apache = new ApacheController();
    	$apache->restart();
    	$output->writeln('<info>Apache restarted.</info>');

    }


}