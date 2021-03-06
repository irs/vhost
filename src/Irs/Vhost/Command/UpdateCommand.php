<?php

namespace Irs\Vhost\Command;

use Irs\Vhost\ApacheController;
use Irs\Vhost\InputHelper;

use Symfony\Component\Console\Helper\DialogHelper;

use Symfony\Component\Console\Input\InputArgument;

use Irs\Vhost\HostsFile;

use Irs\Vhost\ConfigurationFile;

use Symfony\Component\Console\Output\OutputInterface,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Input\InputOption,
    Symfony\Component\Console\Command\Command;

/**
 * Command for virtual host adding
 *
 * @author Vadim Kusakin
 */
class UpdateCommand extends Command
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
    	$this->setName('host:update')
    	    ->setDescription('Updates host.')
    	    ->addArgument('name', InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
    	$helper = new InputHelper($output, $this->getHelper('dialog'), $this->configuration, $this->hosts);

	    $host = $this->configuration->getHostByName($input->getArgument('name'));
        $docRoot = $helper->getDocRoot($host->getDocumentRoot());
        $errorLog = $helper->getLog('error', $host->getServerName(), $host->getErrorLog());
        $customLog = $helper->getLog('custom', $host->getServerName(), $host->getCustomLog());

	    $host->setDocumentRoot($docRoot)
	        ->setErrorLog($errorLog)
	        ->setCustomLog($customLog);

    	$this->configuration->updateHost($host);
        $this->configuration->save();
    	$output->writeln('<info>Successfully added.</info>');

    	$apache = new ApacheController();
    	$apache->restart();
    	$output->writeln('<info>Apache restarted.</info>');
    }


}