<?php
require_once __DIR__ . "/httpserver.php";
$setting = array(
	'host' => '0.0.0.0',
	'port' => '10006',
	'worker_num' => 4,
	'dispatch_mode' => 3, //固定分配请求到worker
	'reactor_num' => 4,	//亲核,
	'daemonize' => false,
	'backlog' => 128,
	'log_file' => '/root/swoole/http.log'
);

$http = new HttpServer();
$http->set($setting);
$http->start();
