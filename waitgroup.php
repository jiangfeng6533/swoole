<?php
class WaitGroup
{
	public $count = 0;
	private $chan;
	
	/*
	* waitgroup constructor.
	* @desc 初始化一个channel
	*/
	public function __construct(){
		$this->chan  = new chan;
	}
	
	public function add (){
		$this->count++;
	}
	
	public function done($data){
		$this->chan->push($data);
	}
	
	public function wait(){
		
		while($this->count--){
			echo $this->chan->pop();
		}
	}	
}



	
