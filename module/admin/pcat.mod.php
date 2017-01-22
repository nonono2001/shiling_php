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
			
				
			default:
				$this->Product_cat(); //产品分类列表
				break;
		}

	}
	
	
	function Product_cat()
	{
		$page_title="后台管理 - 产品分类";
		
		//一级菜单
		$menu_level_1 = 'products';
		//三级菜单
		$menu_level_3 = 'prodcat';
		
		//得到现已存在的产品分类。
		$sql = "select * from qkdb_category order by paixu_num ASC";
		$query = $this->DatabaseHandler->Query($sql);
		
		$catlevel1 = array();
		$catlevel2 = array();
		$catlevel3 = array();
		while($cat_row = $this->DatabaseHandler->GetRow($query))
		{
			//每个$cat_row就是一个产品分类，一级到三级都有可能。
			if('1' == $cat_row['level'])
			{
				$catlevel1[$cat_row['cat_id']] = $cat_row;
			}
			else if('2' == $cat_row['level'])
			{
				$catlevel2[$cat_row['cat_id']] = $cat_row;
			}
			else if('3' == $cat_row['level'])
			{
				$catlevel3[$cat_row['cat_id']] = $cat_row;
			}
			else if('4' == $cat_row['level'])
			{
				$catlevel4[$cat_row['cat_id']] = $cat_row;
			}
		}
		
		include(template('product_cat_list','admin'));
		
	}
	
	
}
?>