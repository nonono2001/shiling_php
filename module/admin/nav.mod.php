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
			case 'dialog_nav_edit':
				$this->Dialog_nav_edit();  //编辑导航项
				break;
				
			case 'dialog_nav_new':
				$this->Dialog_nav_new();  //新增导航项
				break;
				
			default:
				$this->Nav_list(); //导航管理
				break;
		}

	}
	
	
	function Nav_list()
	{
		$page_title="后台管理 - 导航管理";
		
		//一级菜单
		$menu_level_1 = 'yemian';
		//三级菜单
		$menu_level_3 = 'daohang';
		
		//得到现已存在的店铺导航。
		$sql = "select * from qkdb_nav order by paixu_num ASC";
		$query = $this->DatabaseHandler->Query($sql);
		
		$navlevel1 = array();
		$navlevel2 = array();
		$navlevel3 = array();
		$navlevel4 = array();
		while($nav_row = $this->DatabaseHandler->GetRow($query))
		{
			//每个$nav_row就是一个导航项目，一级到四级都有可能。
			if('1' == $nav_row['level'])
			{
				$navlevel1[$nav_row['nav_id']] = $nav_row;
			}
			else if('2' == $nav_row['level'])
			{
				$navlevel2[$nav_row['nav_id']] = $nav_row;
			}
			else if('3' == $nav_row['level'])
			{
				$navlevel3[$nav_row['nav_id']] = $nav_row;
			}
			else if('4' == $nav_row['level'])
			{
				$navlevel4[$nav_row['nav_id']] = $nav_row;
			}
		}
		
		include(template('nav_list','admin'));
	}
	
	//导航项新增对话框
	function Dialog_nav_new()
	{
		$p_nav_id = getPG('p_nav_id');//新增导航项所属的父导航项id，为空或者为0时，代表新增一个顶级导航项。
		if(!$p_nav_id || $p_nav_id == 0)//新增一个顶级导航项
		{
			
		}
		else 
		{
			//新增一个非顶级导航项
			$sql = "select * from qkdb_nav where nav_id = '".$p_nav_id."'";
			$query = $this->DatabaseHandler->Query($sql);
			$p_navrow = $this->DatabaseHandler->GetRow($query);
			if(!$p_navrow)
			{
				//说明nav_id错误。
				echo '<div style="float:left;width:100%;height:150px;line-height:150px;margin-top:20px;font-family:microsoft yahei;text-align:center;font-size:18px;">父导航项不存在！请重新选择。</div>';
				return;
			}
		}
		
		$optype = 'new';//用于区别是新增还是编辑
		include(template('dialog_nav_edit','admin'));
	}
	
	//导航项编辑对话框
	function Dialog_nav_edit()
	{
		$nav_id = getPG('nav_id');//该值为要编辑的导航项id。
	
		$navrow = '';
	
		$sql = "select * from qkdb_nav where nav_id = '".$nav_id."'";
		$query = $this->DatabaseHandler->Query($sql);
		$navrow = $this->DatabaseHandler->GetRow($query);
		if(!$navrow)
		{
			//说明nav_id错误。
			echo '<div style="float:left;width:100%;height:150px;line-height:150px;margin-top:20px;font-family:microsoft yahei;text-align:center;font-size:18px;">该导航项不存在，无法编辑！请重新选择。</div>';
			return;
		}
		
		$type_name = '';
		if($navrow['nav_type'] == 'product_category')
		{
			$type_name = '产品分类';
			if($navrow['nav_content'] == 0 || !$navrow['nav_content'])
			{
				$navrow['nav_content_selected'] = $type_name." -- "."所有产品";
			}
			else 
			{
				$sql = "select name from qkdb_category where cat_id = '".$navrow['nav_content']."'";
				$query = $this->DatabaseHandler->Query($sql);
				$row = $this->DatabaseHandler->GetRow($query);
				if(!$row)
				{
					$navrow['nav_content_selected'] = $type_name." -- "."(已选的产品分类不存在，可能已被删除)";
				}
				else 
				{
					$navrow['nav_content_selected'] = $type_name." -- ".$row['name'];
				}
			}
		}
		else if( $navrow['nav_type'] == 'product_detail')
		{
			$type_name = '产品详细';
			$sql = "select name from qkdb_product where product_id = '".$navrow['nav_content']."'";
			$query = $this->DatabaseHandler->Query($sql);
			$row = $this->DatabaseHandler->GetRow($query);
			if(!$row)
			{
				$navrow['nav_content_selected'] = $type_name." -- "."(已选的产品不存在，可能已被删除)";
			}
			else
			{
				$navrow['nav_content_selected'] = $type_name." -- ".$row['name'];
			}
			
		}
		else if( $navrow['nav_type'] == 'article_category')
		{
			$type_name = '文章分类';
			if($navrow['nav_content'] == 0 || !$navrow['nav_content'])
			{
				$navrow['nav_content_selected'] = $type_name." -- "."所有文章";
			}
			else
			{
				$sql = "select name from qkdb_artcategory where cat_id = '".$navrow['nav_content']."'";
				$query = $this->DatabaseHandler->Query($sql);
				$row = $this->DatabaseHandler->GetRow($query);
				if(!$row)
				{
					$navrow['nav_content_selected'] = $type_name." -- "."(已选的文章分类不存在，可能已被删除)";
				}
				else
				{
					$navrow['nav_content_selected'] = $type_name." -- ".$row['name'];
				}
			}
		}
		else if($navrow['nav_type'] == 'article_detail')
		{
			$type_name = '文章详细';
			$sql = "select title from qkdb_article where article_id = '".$navrow['nav_content']."'";
			$query = $this->DatabaseHandler->Query($sql);
			$row = $this->DatabaseHandler->GetRow($query);
			if(!$row)
			{
				$navrow['nav_content_selected'] = $type_name." -- "."(已选的文章不存在，可能已被删除)";
			}
			else
			{
				$navrow['nav_content_selected'] = $type_name." -- ".$row['title'];
			}
		}
		else if($navrow['nav_type'] == 'custom_page')
		{
			$type_name = '自定义页';
			$sql = "select title from qkdb_custom_page where custompage_id = '".$navrow['nav_content']."'";
			$query = $this->DatabaseHandler->Query($sql);
			$row = $this->DatabaseHandler->GetRow($query);
			if(!$row)
			{
				$navrow['nav_content_selected'] = $type_name." -- "."(已选的自定义页不存在，可能已被删除)";
			}
			else
			{
				$navrow['nav_content_selected'] = $type_name." -- ".$row['title'];
			}
		}
		else if($navrow['nav_type'] == 'out_link')
		{
			$type_name = '外部链接';
			$navrow['nav_content'] = $navrow['link_url'];
			$navrow['nav_content_selected'] = $type_name." -- ".$navrow['link_url'];
		}
		else if($navrow['nav_type'] == 'homepage')
		{
			$type_name = '首页';
			
			$navrow['nav_content_selected'] = $type_name;
		}
		else if($navrow['nav_type'] == 'advise')
		{
			$type_name = '留言';
			
			$navrow['nav_content_selected'] = $type_name;
		}
		else if($navrow['nav_type'] == 'friendlink')
		{
			$type_name = '友情链接';
			
			$navrow['nav_content_selected'] = $type_name;
		}
	
		$optype = 'edit';//用于区别是新增还是编辑
		include(template('dialog_nav_edit','admin'));
	}
	
}
?>