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
		//必需要已登录，并且是管理员身份
//		if(!MEMBER_ID || MEMBER_ID <= 0)
//		{
//			$this->Messager("请先登录后台管理系统！","admin.php?mod=login");
//		}
//		if(!IS_MEMBER_ADMIN || IS_MEMBER_ADMIN <= 0)//虽然登录了，但不是管理员
//		{
//			$this->Messager("您不是管理员，不可进入后台管理系统。请用管理员身份登录。","admin.php", 10);
//		}
		
		switch($this->Act)
		{
				
			default:
				$this->Check_is_login();
				break;
		}

	}



	//判断当前用户是否已登录
	function Check_is_login()
	{
		if(!MEMBER_ID || MEMBER_ID <= 0)
		{
			json_error('未登录','40010');
		}
		else
		{
			json_result();//已登录
		}


	}
	
	

}
?>