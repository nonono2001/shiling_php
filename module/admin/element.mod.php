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
		if(!MEMBER_ID || MEMBER_ID <= 0)
		{
			$this->Messager("请先登录后台管理系统！","admin.php?mod=login");
		}
		if(!IS_MEMBER_ADMIN || IS_MEMBER_ADMIN <= 0)//虽然登录了，但不是管理员
		{
			$this->Messager("您不是管理员，不可进入后台管理系统。即将回到网站首页。","index.php", 10);
		}
		
		switch($this->Act)
		{
			
			case 'logo':
				$this->Logo();  //Logo图片管理
				break;
			
			case 'boardpic':
				$this->Boardpic(); //轮播大图管理
				break;
				
				
			default:
				$this->Common(); 
				break;
		}

	}
	
	function Common()
	{
		$this->Messager("参数错误");
	}
	
	function Logo()
	{
		$page_title="后台管理 - Logo管理";
		
		//一级菜单
		$menu_level_1 = 'yemian';
		//三级菜单
		$menu_level_3 = 'element_logo';
		
		//当前logo的地址
		$currentlogo = get_logo_img_path();
		
		
		include(template('element_logo','admin'));
	}
	
	function Boardpic()
	{
		$page_title="后台管理 - 轮播图管理";
		
		//一级菜单
		$menu_level_1 = 'yemian';
		//三级菜单
		$menu_level_3 = 'element_boardpic';
		
		//当前轮播大图地址
		$currentpic = get_boardpic_path();
		
		include(template('element_boardpic','admin'));
	}
	
	
}
?>