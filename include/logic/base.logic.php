<?php

//该类的作用是全局基础类，在入口文件处被实例化。全局可用。
class BaseLogic{

	public $db;
	function __construct(){
		$this->db = new SqlClass();
		$this->db->connect();

	}

    //该类在一个php进程中间不会析构。直到一个php进程结束时才会。
    function __destruct()//析构函数
    {
        $this->db->close();
    }
	
	
}



















?>