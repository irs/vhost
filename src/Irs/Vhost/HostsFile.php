<?php

namespace Irs\Vhost;

class HostsFile
{
	private $content,
	    $fileName;

    public function __construct($fileName)
    {
    	$this->fileName = $fileName;
    	$this->content = file_get_contents($fileName);
    }

    public function __destruct()
    {
    	$this->save();
    }

    public function save()
    {
    	file_put_contents($this->fileName, $this->content);

    	return $this;
    }

    public function getHosts()
    {
    	$hosts = array();
        $result = preg_match_all('/^\s*\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\s+(.*)/mi', $this->content, $matches);

        if ($result)
            foreach ($matches[1] as $m)
                $hosts = array_merge($hosts, array_map('trim', explode(' ', trim($m))));

        return array_unique($hosts);
    }

    public function addHost($name, $address = '127.0.0.1')
    {
        if ($this->hasHost($name))
        	throw new \InvalidArgumentException("Host $name already exists.");

        $this->content = trim($this->content) . "\n$address\t$name";

        return $this;
    }

    public function removeHost($name)
    {
        $this->content = str_replace($name, '', $this->content);
        $this->content = preg_replace('/^\s*\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\s+$/m', '', $this->content);

        return $this;
    }

    public function hasHost($name)
    {
        return (bool)preg_match('#\s+' . preg_quote($name, '#') . '\s*#s', $this->content);
    }

    public function toString()
    {
    	return trim($this->content) ."\r\n";
    }

    public function __toString()
    {
    	return $this->toString();
    }

}