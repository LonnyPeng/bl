<?php

ini_set("display_errors", "On");
error_reporting(E_ALL | E_STRICT);

class Tick
{
	public $server;

	private $filename = __DIR__ . '/log/socket.sock';

	private function isInstance()
	{
		$this->server = new swoole_websocket_server("0.0.0.0", 9506);
		$this->server->set([]);
	}

	public function onMessage(swoole_websocket_server $server)
	{
		
	}

	public function onStart($server)
	{
		echo "Start Success";
		file_put_contents($this->filename, $server->master_pid);
		swoole_set_process_name('websocket_client_server');
	}

	public function run()
	{
		$this->isInstance();
		$this->server->on('message', [$this, 'onMessage']);
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