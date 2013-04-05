<?php

namespace Irs\Vhost;

use Symfony\Component\Console\Application as SymfonyApplication;

class Application extends SymfonyApplication
{
	const VERSION = '@version@ @state';

	private $vhostConfFile,
	    $hostsFile;

	public function __construct($vhostConfFileName, $hostsFileName)
	{
		parent::__construct('Apache virtual host manager', self::VERSION);

		$this->vhostConfFile = new ConfigurationFile($vhostConfFileName);
		$this->hostsFile = new HostsFile($hostsFileName);

		$this->addCommands(array(
            new Command\ListCommand($this->vhostConfFile, $this->hostsFile),
            new Command\RemoveCommand($this->vhostConfFile, $this->hostsFile),
            new Command\AddCommand($this->vhostConfFile, $this->hostsFile),
            new Command\UpdateCommand($this->vhostConfFile, $this->hostsFile),
		));
	}

}