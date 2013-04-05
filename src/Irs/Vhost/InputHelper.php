<?php

namespace Irs\Vhost;

use Symfony\Component\Console\Helper\DialogHelper;
use Symfony\Component\Console\Output\OutputInterface;

class InputHelper
{
    private $output;
    private $dialog;
    private $configuration;
    private $hosts;

    public function __construct(OutputInterface $output, DialogHelper $dialog, ConfigurationFile $config, HostsFile $hosts)
    {
    	$this->output = $output;
    	$this->dialog = $dialog;
    	$this->configuration = $config;
    	$this->hosts = $hosts;
    }

    public function getHostName($checkHosts = true)
    {
    	$hostName = null;

    	do
    	{
    		$hostName = $this->dialog->ask($this->output, "<question> Type host name:</question>\n");

    		try
    		{
    			$this->configuration->getHostByName($hostName);
    			throw new \InvalidArgumentException(
    					"$hostName is already defined; use host:update command to change it."
    			);
    		}
    		catch (\OutOfRangeException $e)
    		{
    			// ok
    		}

    		if ($checkHosts && !$this->hosts->hasHost($hostName))
    		{
    			$wantToAdd = $this->dialog->askConfirmation(
    					$this->output,
    					"<question> There is no such hostname in hosts; do you want to add it?</question>\n"
    			);

    			if ($wantToAdd)
    				$this->hosts->addHost($hostName);
    			else
    				$hostName = null;
    		}

    	} while ($hostName === null);

    	return $hostName;
    }

    public function getDocRoot($default = null)
    {
    	$docRoot = null;
    	$defaultNotice = ($default) ? " (default: $default)" : '';

    	do
    	{
    		$docRoot = $this->dialog->ask(
				$this->output,
				"<question> Type full path to document root$defaultNotice:</question>\n", $default);

    		if (!file_exists($docRoot))
    		{
    			$this->output->writeln("<error> Directory does not exist; type correct path.</error>");
    			$docRoot = null;
    		}
    		else if (!is_dir($docRoot))
    		{
    			$this->output->writeln("<error> It's not a directory; type correct path.</error>");
    			$docRoot = null;
    		}
    	}
    	while ($docRoot === null);

    	return $docRoot;
    }

    public function getLog($type, $hostName, $default = null)
    {
    	if (!$default)
    	{
        	$hostNameParts = explode('.', $hostName, 2);
        	$default = "logs/{$hostNameParts[0]}-$type.log";
    	}

    	$errorLog = $this->dialog->ask(
			$this->output,
			"<question> Enter path to $type log (default: $default):</question>\n",
			$default
    	);

    	return $errorLog;
    }
}
