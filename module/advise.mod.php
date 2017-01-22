<?php

class ModuleObject extends MasterObject
{
	function ModuleObject()//构造函数
	{
		$this->MasterObject( 'db_on' );
		
		$this->Execute();
		
	}
	
	
    function Execute()
	{
		switch($this->Act)
		{

			case 'dosub':
				$this->Dosub();
				break;
				
			default:
				$this->Adivse();
				break;
		}

	}
	
	
	
	//用户留言
	function Adivse()
	{
		
		///////////////////////页头数据准备//////////////////////////////////////////////////////////
		$pagedatalogic = Load::logic('pagedata');
		$page_data = $pagedatalogic->PrepareViewData();//页头页脚数据准备
		///////////////////////页头数据准备结束//////////////////////////////////////////////////////
		
		$page_title = '用户留言';
		include(template('adivse'));
		
	}
	
	
	//用户提交留言
	function Dosub()
	{
		$username = trim(getPG('username')); //用户姓名，可能为空
		$userphone = trim(getPG('userphone')); //用户电话，可能为空
		$useremail = trim(getPG('useremail')); //用户邮箱，可能为空
		$advisecontent = trim(getPG('advisecontent'));//用户留言内容，可能为空
		
		//首先判断验证码有没有错
		session_start();
		if(!empty($_SESSION["VCODE"]))
		{
			$v_valid_client = getPG('valicode');
			$v_valid_server = strtolower($_SESSION["VCODE"]);
			$v_valid_client = strtolower($v_valid_client);//这里的$v_valid_client其实应该加一个stripslashes，因为它是通过post或get传来的，而且这里它又不是用于sql语句。如果验证码是abc'd，那么$v_valid_client里保存的其实是abc\'d六个字符。
				
			if($v_valid_server != $v_valid_client)//不相等，说明客户端验证码输入错误
			{
				$this->Messager("验证码输入错误，请重新提交！","index.php?mod=advise",10);
			}
			//else 相等，验证码正确，继续往下走。
		}
		else//session发生错误。
		{
			$this->Messager("验证码未生成！","index.php?mod=advise",10);
			// 			$r_json = '{"result":false,"data":"验证码未生成！"}';
			// 			echo $r_json;
			// 			return ;
		}
		
		
		if(!$advisecontent)
		{
			$this->Messager("留言内容不能为空，请填写留言内容后提交。谢谢。","index.php?mod=advise",10);
		}
		
		$insert_data = array(
			'nickname' => $username,
			'email' => $useremail,
			'phone' => $userphone,
			'create_time' => time(),
			'content' => $advisecontent,
		);
		
		$this->DatabaseHandler->insert('qkdb_advise', $insert_data);
		
		$errorinfo = mysql_error();
		if($errorinfo)
		{
			$this->Messager("留言保存失败！失败原因：".$errorinfo,"index.php?mod=advise",10);
		}
		
		$this->Messager("您的留言已成功提交，谢谢您宝贵的意见，我们会认真查看。","index.php?mod=advise",10);
	}
	
	
	
	
	
	
	
	
}

?>