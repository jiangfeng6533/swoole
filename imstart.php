<?php
require_once __DIR__ . "/websocketserver.php";
$setting = array(
	'host' => '0.0.0.0',
	'port' => '10006',
	'worker_num' => 4,
	'dispatch_mode' => 3, //¹Ì¶¨·ÖÅäÇëÇóµ½worker
	'reactor_num' => 4,	//Ç×ºË,
	'daemonize' => false,
	'backlog' => 128,
	'task_worker_num' => 4,
	'log_file' => '/root/swoole/http.log',
	'udp_port' => 9502
);

$http = new HttpServer();
$http->set($setting);
$http->start();
