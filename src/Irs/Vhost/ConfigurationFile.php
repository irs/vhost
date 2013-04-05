<?php

namespace Irs\Vhost;

class ConfigurationFile
{
	private $content,
	    $fileName;

	public function __construct($fileName)
	{
		$this->fileName = $fileName;
		$this->content = trim(file_get_contents($fileName));
	}

	public function __destruct()
	{
		$this->save();
	}

	/**
	 * @return array Array fo hosts
	 */
	public function getHosts()
	{
	    $count = preg_match_all('#<VirtualHost.*>(.*)</VirtualHost>#iUs', $this->content, $matches);
	    $hosts = array();

	    if ($count)
	        foreach ($matches[1] as $m)
	    	    $hosts[] = $this->createHostFromConfig($m);

	    return $hosts;
	}

	/**
	 *
	 * @param string $hostName
	 * @throws \RuntimeException On incorrect regular expression
	 * @throws \OutOfRangeException When host is not found.
	 * @return \Vhost\Host
	 */
	public function getHostByName($hostName)
	{
		$regexp = '#<VirtualHost\s+\*:80>(.*ServerName\s+"?' . preg_quote($hostName, '#') . '"?\s+.*)</VirtualHost>#Us';
		$result = preg_match($regexp, $this->content, $matches);

		if ($result === false)
			throw new \RuntimeException("Wrong regular expression: $regexp.");
		else if ($result)
			return $this->createHostFromConfig($matches[1]);

		throw new \OutOfRangeException("Virtual host record with server name $hostName not found.");
	}

	/**
	 * Adds host
	 *
	 * @param  \Vhost\Host $host         Host to add
	 * @throws \InvalidArgumentException Host already exists
	 * @return \Vhost\ConfigurationFile  Self
	 */
	public function addHost(Host $host)
	{
		try
		{
			$this->getHostByName($host->getServerName());
		}
		catch (\OutOfRangeException $e)
		{
			$this->content .= <<<HST
\r
\r
<VirtualHost *:80>\r
    ServerAdmin a@b.com\r
    DocumentRoot "{$host->getDocumentRoot()}"\r
    ServerName "{$host->getServerName()}"\r
    ErrorLog "{$host->getErrorLog()}"\r
    CustomLog "{$host->getCustomLog()}" common\r
</VirtualHost>\r
HST;
			return $this;
		}

		throw new \InvalidArgumentException("Host with server name {$host->getServerName()} already exists.");
	}

	/**
	 * Removes host from config
	 *
	 * @param  \Vhost\Host $host         Host to delete
	 * @throws \RuntimeException        Incorrect regular expression
	 * @throws \OutOfRangeException     Host not found
	 * @return \Vhost\ConfigurationFile Self
	 */
	public function removeHost(Host $host)
	{
		$regexp = '#<VirtualHost\s+\*:80>([^<]*ServerName\s+"?' . preg_quote($host->getServerName(), '#') . '"?[^<]*)</VirtualHost>#Us';
        $result = preg_replace($regexp, '', $this->content, 1, $count);

        if (null === $result)
        	throw new \RuntimeException("Wrong regular expression: $regexp.");

        if (!$count)
            throw new \OutOfRangeException("Host with name {$host->getServerName()} not found.");

        $this->content = $result;

        return $this;
	}

	/**
	 * Updates host
	 *
	 * @param  \Vhost\Host $host        Host to update
	 * @throws \RuntimeException        Incorrect regular expression on remove
	 * @throws \OutOfRangeException     Host not found
	 * @return \Vhost\ConfigurationFile Self
	 */
	public function updateHost(Host $host)
	{
        $this->removeHost($host);
        $this->addHost($host);

        return $this;
	}

	/**
	 *
	 * @param strign $config VirtualHost tag content
	 */
	protected function createHostFromConfig($config)
	{
		$options = array();
		$required = array('DocumentRoot', 'ServerName');

		foreach (array_map('trim', explode("\n", trim($config))) as $row)
		{
			$option = explode(' ', $row, 2);

			if (count($option) > 1)
			{
				list ($name, $value) = $option;
                $options[trim($name)] = trim($value, "\" \r\n");
			}
		}

		foreach ($required as $r)
		    if (!isset($options['DocumentRoot']))
			    throw new \InvalidArgumentException('DocumentRoot is undefined.');

		$errorLog = isset($options['ErrorLog']) ? $options['ErrorLog'] : null;

		if (!isset($options['CustomLog']))
			$customLog = null;
		else
			list ($customLog) = explode(' ', $options['CustomLog'], 2);

        return new Host($options['ServerName'], $options['DocumentRoot'], $errorLog, trim($customLog, ' "'));
	}

	public function save()
	{
		file_put_contents($this->fileName, $this->content);

		return $this;
	}

    public function toString()
    {
        return trim($this->content) . "\n";
    }

    public function __toString()
    {
    	return $this->toString();
    }
}