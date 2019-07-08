<?php
require_once __DIR__ . "/waitgroup.php";
class HttpServer
{
	public $http;
	public $queue;
	public $setting = array();
	public $wg;
	
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
		
		$this->http = new Swoole\Http\Server($this->setting['host'],$this->setting['port']);
		$this->http->set($this->setting);
		
		$this->http->on('request',array($this,'onRequest'));
		$this->http->on('close',array($this,'onClose'));
		
		$this->wg = new WaitGroup();
		echo '123'.PHP_EOL;
	}
	
	public function onRequest($request,$response){
		echo "client";
		$this->wg->add();
		go(function(){
			
			echo $this->wg->count;
			$this->wg->done('协程1完成');
			
		});
		$this->wg->add();
		go(function(){
			
			echo $this->wg->count;
			$this->wg->done('协程2完成');
		});
		
		$this->wg->wait();
		
	}
	
	public function onClose($server,$fd,$reactor_id){
		echo "on close fd = $fd reactor_id = $reactor_id";
	}
	
	
	public function start(){
		$this->init();
		$this->http->start();
	}
}