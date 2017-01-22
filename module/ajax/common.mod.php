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
		//本mod是通用的，不需登录就可使用。
		//所以没有登录判断。
		
		switch($this->Act)
		{
			case 'messagebox':
				$this->Messagebox();
				break;
				
			case 'do_save_shopname':
				$this->Do_save_shopname(); //保存前端店铺名称
				break;
				
			default:
				$this->Common();
				break;
		}

	}
	
	function Common()
	{
		echo '';
		exit();	
	}
	
	function Messagebox()
	{
		$type = getPG('type');
		$msg = stripslashes(getPG('msg'));
		$msgboxid = getPG('msgboxid');
		
		include(template('msgbox_dialog','admin'));
	}
	
	function Do_save_shopname()
	{
		$shopname_gpc = trim(getPG('shopname'));
		$shopname = stripslashes($shopname_gpc);
		if(mb_strlen($shopname,'utf8') == 0)
		{
			echo '<script type="text/javascript">';
			echo 'window.parent.afterupload("err","请填写店铺名称！");  ';
			echo '</script>';
			exit();
		}
		if(mb_strlen($shopname,'utf8') > 20)
		{
			echo '<script type="text/javascript">';
			echo 'window.parent.afterupload("err","字数过长！");  ';
			echo '</script>';
			exit();
		}
		
		
		setConfKV('shop_name', $shopname_gpc);
		
		echo '<script type="text/javascript">';
		echo 'window.parent.afterupload("succ","");  ';
		echo '</script>';
		exit();
	}
	
}
?>