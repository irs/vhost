<?php

namespace Irs\Vhost;

class Host
{
	private $serverName,
	    $documentRoot,
	    $errorLog = 'logs/error.log',
	    $customLog = 'logs/custom.log';

	/**
	 * @return string $serverName
	 */
	public function getServerName()
	{
		return $this->serverName;
	}

	/**
	 * @return string $documentRoot
	 */
	public function getDocumentRoot()
	{
		return $this->documentRoot;
	}

	/**
	 * @return string $errorLog
	 */
	public function getErrorLog()
	{
		return $this->errorLog;
	}

	/**
	 * @return string $customLog
	 */
	public function getCustomLog()
	{
		return $this->customLog;
	}

	/**
	 * @param string $serverName
	 */
	public function setServerName($serverName)
	{
		$this->serverName = $serverName;

		return $this;
	}

	/**
	 * @param string $documentRoot
	 */
	public function setDocumentRoot($documentRoot)
	{
		$this->documentRoot = $documentRoot;

		return $this;
	}

	/**
	 * @param field_type $errorLog
	 */
	public function setErrorLog($errorLog)
	{
		$this->errorLog = $errorLog;

		return $this;
	}

	/**
	 * @param field_type $customLog
	 */
	public function setCustomLog($customLog)
	{
		$this->customLog = $customLog;

		return $this;
	}

	public function __construct($serverName, $documentRoot, $errorLog = 'logs/error.log', $customLog = 'logs/custom.log')
	{
	    $this->setServerName($serverName)
	        ->setDocumentRoot($documentRoot)
	        ->setErrorLog($errorLog)
	        ->setCustomLog($customLog);
	}
}