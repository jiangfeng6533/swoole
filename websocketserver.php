<?php
require_once __DIR__ . "/waitgroup.php";
class HttpServer
{
	public $http;
	public $setting = array();
	public $wgg;
	private $redis;
	private $queueisRun = false;
	private $udpserver;
	
	public function __construct(){
		
	}
	
	public function set($setting){
		$this->setting = $setting;
	}
	
	public function init(){
		
		if(!isset($this->setting['host'])){
			$this->setting['host'] = '0.0.0.0';
		}
		
		if(!isset($this->setting['port'])){
			$this->setting['port'] = 9999;
		}
		
		// Websocket
		$this->http = new swoole_websocket_server($this->setting['host'],$this->setting['port'],SWOOLE_PROCESS);
		$this->http->set($this->setting);
		$this->http->on('open',array($this,'onOPen'));
		$this->http->on('close',array($this,'onClose'));
		$this->http->on('request',array($this,'onRequest'));
		$this->http->on('message',array($this,'onMessage'));
		$this->http->on('task',array($this,'onTask'));
		$this->http->on('finish',array($this,'onFinish'));
		$this->http->on('WorkerStart',array($this,'onWorkerStart'));
		
		// Redis
		$this->redis = new Redis();
		$this->redis->connect('127.0.0.1', 6379);
		$this->wgg = new WaitGroup();
		
		// UDP udp_port command: netcat -u 127.0.0.1 9502
		$this->udpserver = $this->http->addListener($this->setting['host'], $this->setting['udp_port'], SWOOLE_SOCK_UDP);
		$this->udpserver->on('Packet',array($this,'onPacket'));
		
		// TCP
		//$this->udpserver->on('receive',array($this,'onReceive'));
		echo 'run success , port :'.$this->setting['port'].PHP_EOL; 
		
	}
	
	function onReceive($_server, $fd, $from_id, $data) {
		$_server->send($fd, "fd: $fd, Server received: ".$data);
		$_server->close($fd);
	}
	
	public function onWorkerStart($serv, $worker_id){
		echo "onWorkerStart workerId : ".$worker_id."\n";
		if($worker_id == 1){
			$this->listenQueue();
			//var_dump($serv);
			//$serv->task("some data",1);
		}
	}
	
	
	public function listenQueue(){
		$this->http->tick(5000, function() {
			echo "队列监听状态 ：";var_dump($this->queueisRun);echo PHP_EOL;
			if(!$this->queueisRun){
                $this->queueisRun = true;
				$i = 0;
                while (true){
                    try{
                        $task = $this->redis->lPop('newlist');
                        //$task = $this->redis->lrange('newlist', 0 ,-1);
                        if($task){
							echo $task.PHP_EOL;
							var_dump($task);
							$data = json_decode($task,true);
							var_dump($data);
							 //$this->http->connection_info($data['to_fd']);
							$ret = $this->http->push($data['to_fd'], $data['data']);
							if($ret){
								echo "推送成功";
							}else{
								echo "推送失败";
								$val = $this->redis->rpush('newlist',$task);
							}
                        }else{
                            break;
                        }
                    }catch (\Throwable $throwable){
                        break;
                    }
                }
                $this->queueisRun = false;
            }
		});
	}
	
	// 建立连接和通讯
	public function onOpen($_server,$request){
		echo "open";
		//$this->wgg->add();
		echo "server#{$_server->worker_pid}: handshake success with ; fd ：#{$request->fd}\n";
		$fd = $request->fd;
		//$this->http->connections[] = $fd;
	}
	
	
	
	// 连接后监听信息
	public function onMessage(swoole_websocket_server $_server, $frame) {
		echo "onMessage";
		//$val = $this->redis->lrange('newlist', 0 ,-1);
		//var_dump($val);
		$data = json_decode($frame->data,true);
		//$this->wgg->done('发送消息1');
		
		echo "received ".strlen($frame->data)." bytes\n";
		echo "received ".$frame->data." end\n";
		echo "fd :".$frame->fd."\n";
		if ($data['status'] == "close")
		{
			$_server->close($frame->fd);
		}
		elseif($data['status'] == "task")
		{
			$_server->task(['go' => 'die']);
		}elseif($data['status'] == "client"){
			
			// 队列
			$val = $this->redis->rpush('newlist',$frame->data);
			if($val){
				$_server->push($frame->fd, 'server receiver success !');
			}else{
				$_server->push($frame->fd, 'fail !');
			}
			
			// 非队列：
			/* if(@$data['ret'] == "to_fd"){
				$_server->push($data['fd'], $frame->fd.'跟你说'.$data['data']);
			}
			if(@$data['ret'] == "to_all_fd"){
				foreach ($this->http->connections as $fd) {
					if ($this->http->isEstablished($fd)) {	//isEstablished($fd)确认是否是正确连接
						echo $fd.PHP_EOL;
						$this->http->push($fd, "hello ya");
					}
				}
			} */
		}
		else
		{
			echo "No auth !".PHP_EOL;
			$_server->push($frame->fd, 'No auth ! fd:'.$frame->fd);
		}
	}
	
	// 任务
	public function onTask($_server, $worker_id, $task_id, $data)
	{	
		// if($task_id == 1){
			// echo "kkkk";
			// $this->listenQueue();
		// }
		
		var_dump("worker_id",$worker_id,"task_id", $task_id, "data",$data);
		return "hello world\n";
	}
	
	// 任务完成
	public function onFinish($_server, $task_id, $result)
	{
		var_dump('task_id',$task_id, 'result',$result);
	}

	// UDP
	public function onPacket($_server, $data, $clientInfo) {
		$_server->sendto($clientInfo['address'], $clientInfo['port'], "Server ".$data);
		echo "#".posix_getpid()."\tPacket {$data}\n";
		$ret = $this->http->push(1, "from UDP push \n");
		var_dump($clientInfo);
	}
	
	// http
	public function onRequest($request,$response){
		var_dump($this->http->stats());
		var_dump($request->get);
		$fd = $request->get['fd'];
		if($this->http->isEstablished($fd)){
			$this->http->push($fd =$fd, "hello ya");
			$response->end("接到信息了".PHP_EOL);
		}else{
			$response->end("接到信息了,用户不在线".PHP_EOL);
		}
	}
	
	// 关闭某个用户
	public function onClose($server,$fd,$reactor_id){
		echo "on close fd = $fd reactor_id = $reactor_id";
	}
	
	//开启进程
	public function start(){
		$this->init();
		$this->http->start();
	}
}