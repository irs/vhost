<?php

namespace Irs\Vhost;

class ApacheController
{
	const SERVICE_NAME = 'Apache2.2';

	public function start()
	{
		exec('net start ' . self::SERVICE_NAME);
	}

	public function stop()
	{
		exec('net stop ' . self::SERVICE_NAME);
	}

	public function restart()
	{
	    $this->stop();
	    $this->start();
	}
}
