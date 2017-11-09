<?php

class Tick
{
	private $filename = __DIR__ . '/log/socket.sock';

	public function run()
	{
		$this->isInstance();
		$this->server->on('task', [$this, 'onTask']);
		$this->server->on('finish', [$this, 'onFinish']);
		$this->server->on('open', [$this, 'onOpen']);
		$this->server->on('message', [$this, 'onMessage']);
		$this->server->on('pipeMessage', [$this, 'onPipeMessage']);
		$this->server->on('close', [$this, 'onClose']);
		$this->server->on('start', [$this, 'onStart']);
		$this->server->start();
	}

	public function stop()
	{
		$sock = file_get_contents($this->filename);
		if (empty($sock)) {
			return false;
		}

		shell_exec('kill ' . $sock);
		@unlink($this->filename);
		echo 'Stop Success';
	}
}

if (in_array($argv[1], ['start', 'stop', 'reload'])) {
	$server = new Tick();
	switch ($argv[1]) {
		case 'stop':
			$server->stop();
			break;
		case 'reload':
			$server->stop();
			swoole_timer_after(1500, function () use ($server) {
				$server->run();
			});
			break;
		default:
			$server->run();
	}

	echo PHP_EOL;
	exit;
}

die('php swoole.php (start|stop|reload)');