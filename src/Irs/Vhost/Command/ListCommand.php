<?php

namespace Irs\Vhost\Command;

use Irs\Vhost\HostsFile,
    Irs\Vhost\ConfigurationFile;

use Symfony\Component\Console\Output\OutputInterface,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Input\InputOption,
    Symfony\Component\Console\Command\Command;

/**
 * Command for virtual hosts listing
 *
 * @author Vadim Kusakin
 */
class ListCommand extends Command
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
    	$this->setName('host:list')
    	    ->setDescription('List of defined virtual hosts.')
    	    ->addOption('full', 'f', InputOption::VALUE_NONE, 'Display full info.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $fullView = $input->getOption('full');
        $output->writeln("<comment> Virtual hosts:</comment>");

        foreach ($this->configuration->getHosts() as $host)
        {
        	$message = '';
        	$errors = $this->getHostErrors($host);

        	if ($fullView)
        	{
        		$errDomain = isset($errors['domain']) ? "<error>{$errors['domain']}</error>" : '';
        		$errDocRoot = isset($errors['docroot']) ? "<error>{$errors['docroot']}</error>" : '';
        		$message .= <<<MSG
  - {$host->getServerName()} $errDomain
<info>      root:   {$host->getDocumentRoot()}</info> $errDocRoot
<info>      error:  {$host->getErrorLog()}</info>
<info>      custom: {$host->getCustomLog()}</info>

MSG;

        		$output->writeln($message);
        	}
        	else
        	{
                $message .= '   - ' . $host->getServerName() . "\t" . $host->getDocumentRoot();
                $errMsg = '<error>' . implode('</error> <error>', array_flip($errors)) . '</error>';
            	$output->writeln("<info>$message</info> $errMsg");
        	}
        }
    }


    protected function getHostErrors(\Vhost\Host $host)
    {
        $errors = array();

        if (!$this->hosts->hasHost($host->getServerName()))
        	$errors['domain'] = 'Hostname is undefined!';

        $dr = $host->getDocumentRoot();
        if (!file_exists($dr) || !is_dir($dr))
        	$errors['docroot'] = 'Document root does not exist!';

        return $errors;
    }
}