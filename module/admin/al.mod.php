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
			
			case 'add':
				$this->Add();  //添加新文章
				break;
			
			case 'edit':
				$this->Edit(); //编辑已存在的文章
				break;
				
				
			default:
				$this->Article_list(); //文章列表
				break;
		}

	}
	
	//文章列表
	function Article_list()
	{
		$page_title="后台管理 - 文章列表";
		
		//一级菜单
		$menu_level_1 = 'wenzhang';
		//三级菜单
		$menu_level_3 = 'articlelist';
		
		$countsql = "select count(article_id) as total_record from qkdb_article ";
		$query = $this->DatabaseHandler->Query($countsql);
		extract($this->DatabaseHandler->GetRow($query));//得到变量total_record
		
		$topage = getPG('page');
		if(!$topage)
		{
			$topage = 1;
		}
		$per_page_num = 10;//每页产品条数。同步加载出的产品条数
		
		$page_arr = page_limit($total_record, $per_page_num, $topage, 'admin.php?mod=al');
		
		$sql = "SELECT a.* ,from_unixtime(a.create_time) as createtime,from_unixtime(a.lastmodify_time) as lastmodifytime,
ac.cat_id, c.name as catname
 FROM `qkdb_article` as a left join qkdb_article_cat as ac 
on ac.article_id = a.article_id
left join qkdb_artcategory as c 
on c.cat_id = ac.cat_id  order by a.article_id DESC ".$page_arr['limit'];
		$query = $this->DatabaseHandler->Query($sql);
		
		$articles_info = array();
		while($row = $this->DatabaseHandler->GetRow($query))
		{
			$articles_info[$row['article_id']] = $row;
		}
		
		
		include(template('article_list','admin'));
		
	}
	
	//新建文章界面
	function Add()
	{
		$page_title="后台管理 - 文章添加";
		
		//一级菜单
		$menu_level_1 = 'wenzhang';
		//三级菜单
		$menu_level_3 = 'articlelist';
		
		//封装产品分类
		//得到现已存在的产品分类。
		$sql = "select * from qkdb_artcategory order by paixu_num ASC";
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
		}
		
		include(template('article_add_edit','admin'));
	}
	
	//编辑文章界面
	function Edit()
	{
		$page_title="后台管理 - 文章编辑";
		
		//一级菜单
		$menu_level_1 = 'wenzhang';
		//三级菜单
		$menu_level_3 = 'articlelist';
		
		$article_id = getPG('aid');//要编辑的文章id
		
		if(!$article_id || $article_id<=0)
		{
			$this->Messager('文章id不能为空！', 'admin.php?mod=al', 10);
		}
		
		//判断文章id存不存在。
		$sql = "SELECT a.* ,ac.cat_id
 FROM `qkdb_article` as a left join qkdb_article_cat as ac 
on ac.article_id = a.article_id  
where a.article_id = '".$article_id."' ; ";
		
		$query = $this->DatabaseHandler->Query($sql);
		$articlerow = $this->DatabaseHandler->GetRow($query);
		if(!$articlerow)
		{
			$this->Messager('该文章不存在，可能已被删除！请重新确认后操作。', 'admin.php?mod=al', 10);
		}
		
		//封装文章分类
		//得到现已存在的文章分类。
		$sql = "select * from qkdb_artcategory order by paixu_num ASC";
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
				
				if($articlerow['cat_id'] == $cat_row['cat_id'])
				{
					$catlevel1[$cat_row['cat_id']]['is_selected'] = 'selected';
				}
			}
			else if('2' == $cat_row['level'])
			{
				$catlevel2[$cat_row['cat_id']] = $cat_row;
				
				if($articlerow['cat_id'] == $cat_row['cat_id'])
				{
					$catlevel2[$cat_row['cat_id']]['is_selected'] = 'selected';
				}
			}
			else if('3' == $cat_row['level'])
			{
				$catlevel3[$cat_row['cat_id']] = $cat_row;
				
				if($articlerow['cat_id'] == $cat_row['cat_id'])
				{
					$catlevel3[$cat_row['cat_id']]['is_selected'] = 'selected';
				}
			}
		}
		
		$articlerow['content_specialchar'] = htmlspecialchars($articlerow['content']);
		
		include(template('article_add_edit','admin'));
		
	}
	
	
	
}
?>