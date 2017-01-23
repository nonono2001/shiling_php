<?php
/**
 *所有Module的父类。
 *
 * 
 */

class MasterObject
{
		
	var $Act='';
	var $DatabaseHandler;
	var $sys_setting;
	var $db_flag;
		
	function MasterObject( $db_flag )//构造函数
	{
		$this->db_flag = $db_flag;
		if( $db_flag == 'db_on' )//$db_flag就是为了让每个mod自己控制数据库打开与否。
		{
			//初始化成员变量$DatabaseHandler
//			$this->DatabaseHandler = new SqlClass();
			$this->DatabaseHandler = GLX()->db;
				//连接数据库
			$this->DatabaseHandler->connect();
		}
		
		//得到act
		$this->Act = isset($_POST['act']) ? $_POST['act'] : $_GET['act'];
		
		//初始化系统配置参数
		$this->sys_setting = getSetting('sys_setting');
	}

	function __destruct()//析构函数
	{
		
		
	}
	
	function Messager($msgstr,$redir='',$time = -1,$template_name = '')
	{
//		$sitename = $this->sys_setting['site_name'];
		
		if($redir)//有跳转目的
		{
			if($time >= 0)
			{
				//有跳转目的，有跳转时间，页面将自动跳转
				$url_redirect = '<meta http-equiv="refresh" content="'.$time.'; URL='.$redir.'">';
			}
			else
			{
				//有跳转目的，无跳转时间，页面不自动跳转
				$url_redirect = '';
			}
			
		}
		else//无跳转目的
		{
			//无跳转目的，时间不起效，相当于无目的无时间。相当于页面只显示报错信息，没有跳转链接，也不会自动跳转。
			$url_redirect = '';
			$time = -1;
			
		}
		if($template_name)
		{
			include(template('messager'));
		}
		else 
		{
			include(template('messager','admin'));
		}
		
		exit();
	}
	
	// protected function http($url, $params, $method = 'GET', $header = array(), $multi = false){
 //        $opts = array(
 //            CURLOPT_TIMEOUT        => 30,
 //            CURLOPT_RETURNTRANSFER => 1,
 //            CURLOPT_SSL_VERIFYPEER => false,
 //            CURLOPT_SSL_VERIFYHOST => false,
 //            CURLOPT_HTTPHEADER     => $header
 //        );

 //        /* 根据请求类型设置特定参数 */
 //        switch(strtoupper($method)){
 //            case 'GET':
 //                $opts[CURLOPT_URL] = $url . '?' . http_build_query($params);
 //                break;
 //            case 'POST':
 //                //判断是否传输文件
 //                $params = $multi ? $params : http_build_query($params);
 //                $opts[CURLOPT_URL] = $url;
 //                $opts[CURLOPT_POST] = 1;
 //                $opts[CURLOPT_POSTFIELDS] = $params;
 //                break;
 //            default:
 //                throw new Exception('不支持的请求方式！');
 //        }
        
 //        /* 初始化并执行curl请求 */
 //        $ch = curl_init();
 //        curl_setopt_array($ch, $opts);
 //        $data  = curl_exec($ch);
 //        $error = curl_error($ch);
 //        curl_close($ch);
 //        if($error) throw new Exception('请求发生错误：' . $error);
 //        return  $data;
 //    }


}
?>