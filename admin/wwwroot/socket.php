<?php

class Tick
{
	/** @var swoole_websocket_server $server */
	public $server;
	
	/** @var swoole_process $process */
	public $process;
	
	/** @var Redis $redis */
	public $redis;
	
	private $data = [];
	
	//bridge work 交易体系相关接口参数
	private $bridgeWorkID = 'T001288';
	
	private $bridgeWorkKey = 'br3E4c78IHBMOEGC';
	
	private $bridgeWorkApiUrl = 'https://bridge.api.lwork.com';
	
	private $bridgeServerId = '81';
	
	private function isInstance()
	{
		$this->server = new swoole_websocket_server("0.0.0.0", 9506);
		$this->server->set([
			'worker_num' => 2, //设置启动的worker进程数量。swoole采用固定worker进程的模式
			'reactor_num' => 6,
			// 'backlog' => 40000, //此参数将决定最多同时有多少个待accept的连接。
			// 'max_request' => 40000, //此参数表示worker进程在处理完n次请求后结束运行。manager会重新创建一个worker进程。此选项用来防止worker进程内存溢出。
			// 'max_conn' => 40000,
			// 'open_eof_check' => false, //打开buffer
			// 'package_eof' => "\r\n\r\n", //设置EOF
			'dispatch_mode' => 0,
			'daemonize' => 1, //是否以守护进程模式启动
			'open_cpu_affinity' => 1, //启用CPU亲和设置
			'open_tcp_nodelay' => 1, //启用tcp_nodelay
			'tcp_defer_accept' => 1, //此参数设定一个秒数，当客户端连接连接到服务器时，在约定秒数内并不会触发accept，直到有数据发送，或者超时时才会触发。
			'task_worker_num' => 50,
			'enable_port_reuse' => true,
			'log_file' => 'server.log',
			'discard_timeout_request' => false,
			'open_mqtt_protocol' => true,
			'task_ipc_mode' => 1,
			'message_queue_key' => 0x72000120,
			'buffer_output_size' => 4 * 1024 * 1024,
			//'user' => 'root',
			//'group' => 'root',
			//'heartbeat_idle_time' => 600,
			//'heartbeat_check_interval' => 40,
			'package_max_length' => 3096000,
			//'ssl_cert_file' => 'name.crt',
			//'ssl_key_file' => ssl.key',
		]);
		
		$this->process = new swoole_process([$this, 'runProcess']);
		$this->server->addProcess($this->process);
	}
	
	public function runProcess(swoole_process $process)
	{
		$server = $this->server;
		$this->realtime($process, $server);
		$this->loadPositions($process, $server);
		swoole_timer_tick(300 * 1000, function () {
			$redis = $this->redis();
			$results = $this->curl_push($this->bridgeWorkApiUrl . '/v2/trademeta/symbols', 'get', ['vendor' => 'mt4']);
			if ($results['result'] == true) {
				$redis->set('symbols', serialize($results['data']));
			}
		});
	}
	
	private function curl_push($url, $type, $param = [], $header = [])
	{
		if (strtolower($type) == 'get' && !empty($param)) {
			if (strpos($url, '?')) {
				$url .= '&' . http_build_query($param);
			} else {
				$url .= '?' . http_build_query($param);
			}
		}
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_NOBODY, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);//返回内容
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);// 跟踪重定向
		curl_setopt($ch, CURLOPT_TIMEOUT, 6);// 超时设置
		curl_setopt($ch, CURLOPT_HTTPHEADER, $this->dateHeader([
			'X-Api-Token'    => $this->bridgeWorkKey,
			'X-Api-Tenantid' => $this->bridgeWorkID,
			'x-api-serverid' => $this->bridgeServerId
		]));
		curl_setopt($ch, CURLINFO_HEADER_OUT, true);        //至关重要，CURLINFO_HEADER_OUT选项可以拿到请求头信息
		curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate');
		if ($type == 'post') {
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($param));
		} else {
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
		}
		$output = curl_exec($ch);
		if ($output === false) {
			throw new \Exception(curl_error($ch));
		}
		if (is_null(json_decode($output))) {
			return $output;
		}
		return json_decode($output, true);
	}
	
	private function dateHeader($header)
	{
		$data = [];
		if (!empty($header) && is_array($header)) {
			foreach ($header as $key => $val) {
				$data[] = $key . ':' . $val;
			}
		}
		$data[] = 'Content-Type:application/json; charset=UTF-8';
		return $data;
	}
	
	public function realtime(swoole_process $process, swoole_websocket_server $server)
	{
		swoole_async_dns_lookup("bridge.api.lwork.com", function ($host, $ip) use ($server, $process) {
			$cli = new swoole_http_client($ip, 443, true);
			$cli->setHeaders([
				'Host'           => $host,
				'X-Api-Token'    => $this->bridgeWorkKey,
				'X-Api-TenantId' => $this->bridgeWorkID,
				'X-Api-ServerId' => $this->bridgeServerId,
			]);
			
			$cli->on('message', function ($_cli, $frame) use ($server) {
				$txt = __DIR__ . '/push_day_' . date('Y-m-d') . '.txt';
				if (!file_exists($txt))  touch($txt);
				file_put_contents($txt, 'Message Time [' . date('Y-m-d H:i:s') . ']' . $frame->data . PHP_EOL, FILE_APPEND);
				$server->sendMessage($frame->data, rand(0, $server->setting['worker_num'] + $server->setting['task_worker_num'] - 1));
				if (is_null(json_decode($frame->data))) {
					return;
				}
				$data = json_decode($frame->data, true);
				$redis = $this->redis();
				$redis->pipeline();
				foreach ($data['quote'] as $key => $val) {
					$redis->hMset(md5($val['symbol']), $val);
				}
				$redis->exec();
				$redis->publish('v1_quote_realtime_tick', $frame->data);
			});
			$cli->upgrade('/v2/quote/realtime/tick', function ($cli) {
				swoole_timer_tick(59000, function () use ($cli) {
					$cli->push('心跳连接');
				});
				echo 'client start';
				echo PHP_EOL;
			});
			
			$cli->on('close', function () use ($process, $server) {
				echo 'close';
				echo PHP_EOL;
				$this->realtime($process, $server);
			});
		});
	}
	
	public function loadPositions(swoole_process $process, swoole_websocket_server $server)
	{
		swoole_async_dns_lookup("bridge.api.lwork.com", function ($host, $ip) use ($server, $process) {
			$cli = new swoole_http_client($ip, 443, true);
			$cli->setHeaders([
				'Host'           => $host,
				'X-Api-Token'    => $this->bridgeWorkKey,
				'X-Api-TenantId' => $this->bridgeWorkID,
				'X-Api-ServerId' => $this->bridgeServerId,
			]);
			
			$cli->on('message', function ($_cli, $frame) use ($server) {
				if (!empty($frame->data)) {
					$data = json_encode(['event' => 'positions', 'data' => json_decode($frame->data, true)]);
					$server->sendMessage($data, rand(0, $server->setting['worker_num'] + $server->setting['task_worker_num'] - 1));
				}
			});
			$cli->upgrade('/v2/port/streaming', function ($cli) {
				swoole_timer_tick(59000, function () use ($cli) {
					$cli->push('心跳连接');
				});
				echo 'client start';
				echo PHP_EOL;
			});
			
			$cli->on('close', function () use ($process, $server) {
				echo 'close';
				echo PHP_EOL;
				$this->loadPositions($process, $server);
			});
		});
	}
	
	public function onOpen(swoole_websocket_server $server, $request)
	{
		echo "server: handshake success with fd{$request->fd}\n";
	}
	
	public function onMessage(swoole_websocket_server $server, $frame)
	{
		if (!empty($frame->data)) {
			if (!is_null(json_decode($frame->data)) && !is_numeric($frame->data)) {
			
			} else {
				$accountId = $this->redis()->get('accountId_' . md5($frame->data));
				if (!empty($accountId)) {
					$this->redis()->set('user_fd_' . $accountId, $frame->fd);
				} else {
					$server->push($frame->fd, '错误的用户ID');
					$server->close($frame->fd);
				}
			}
		}
	}
	
	public function onStart($serv)
	{
		echo "服务启动成功";
		file_put_contents(__DIR__ . '/runtime/http_client.sock', $serv->master_pid);
		swoole_set_process_name('websocket_client_server');
	}
	
	public function onPipeMessage(swoole_server $serv, $src_worker_id, $data)
	{
		$start_fd = 0;
		$json = json_decode($data, true);
		if (isset($json['event']) && !empty($json['data'])) {
			if ($json['type'] != 'Position') return;
			if (!isset($json['data']['data']) || !is_array($json['data']['data'])) return;
			foreach ($json['data']['data'] as $key => $val) {
				$accountId = $this->redis()->get('user_fd_' . $val['account']);
				if (!$this->server->exist($accountId)) {
					continue;
				}
				$this->server->push($accountId, array_merge(['event' => strtolower($val['positionStatus'])], $val));
			}
		} else {
			$_data = [];
			foreach ($json as $Key => $val) {
				$_data[$Key] = [];
				foreach ($val as $item) {
					if (!preg_match('/\w+app$/', $item['symbol'])) continue;
					array_push($_data[$Key], $item);
				}
			}
			$first = array_shift($_data);
			if (empty($first)) {
				return;
			}
			while (true) {
				$conn_list = $serv->connection_list($start_fd, 10);
				if ($conn_list === false or count($conn_list) === 0) {
					break;
				}
				$start_fd = end($conn_list);
				foreach ($conn_list as $fd) {
					$serv->push($fd, json_encode(['quote' => $first]));
				}
			}
		}
	}
	
	private function response($code, $event, $results = null)
	{
		return json_encode([
			'code'    => $code,
			'event'   => $event,
			'results' => $results
		]);
	}
	
	public function onTask(\swoole_websocket_server $serv, $task_id, $from_id, $data)
	{
		if (is_null($data) || !is_array($data)) {
			return;
		}
		if (!$this->server->exist($data['fd'])) {
			return;
		}
		if (!empty($content) && $this->server->exist($data['fd'])) {
			$results = $this->response(0, '', $data);
			if (strlen($results) > 2048000) {
				$this->server->push($data['fd'], $this->response(0, '', '数据大小超出2M, 推送失败'), 1, true);
				return;
			}
			$this->server->push($data['fd'], $results, 1, true);
		}
		$serv->finish('success');
	}
	
	public function onFinish($server, $data)
	{
	
	}
	
	public function onClose($ser, $fd)
	{
		echo "client {$fd} closed\n";
	}
	
	/**
	 * @return Redis
	 */
	public function redis()
	{
		return $this->connect();
	}
	
	/**
	 * @param $redis
	 *
	 * @return mixed
	 */
	private function connect()
	{
		if ($this->redis instanceof \Redis && $this->redis->ping()) {
			return $this->redis;
		}
		$this->redis = new Redis();
		$this->redis->connect('127.0.0.1', '6379', 0);
		$this->redis->auth('64e549f1d967a1e92493ff48ec74aa90');
		return $this->redis;
	}
	
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
		$fileName = __DIR__ . '/socket.sock';

		$sock = file_get_contents($fileName);
		if (empty($sock)) {
			return false;
		}

		shell_exec('kill ' . $sock);
		@unlink($fileName);
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

die('php websocket.php (start|stop|reload)');