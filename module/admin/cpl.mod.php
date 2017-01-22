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
				$this->Add();  //添加自定义页面
				break;
			
			case 'edit':
				$this->Edit(); //编辑已存在的自定义页面
				break;
				
				
			default:
				$this->Custompage_list(); //自定义页面列表
				break;
		}

	}
	
	//自定义页面列表
	function Custompage_list()
	{
		$page_title="后台管理 - 自定义页面列表";
		
		//一级菜单
		$menu_level_1 = 'yemian';
		//三级菜单
		$menu_level_3 = 'zidingyi';
		
		$countsql = "select count(custompage_id) as total_record from qkdb_custom_page ";
		$query = $this->DatabaseHandler->Query($countsql);
		extract($this->DatabaseHandler->GetRow($query));//得到变量total_record
		
		$topage = getPG('page');
		if(!$topage)
		{
			$topage = 1;
		}
		$per_page_num = 10;//每页条数。同步加载出的条数
		
		$page_arr = page_limit($total_record, $per_page_num, $topage, 'admin.php?mod=cpl');
		
		$sql = "SELECT cp.* ,from_unixtime(cp.create_time) as createtime,from_unixtime(cp.lastmodify_time) as lastmodifytime 
 FROM `qkdb_custom_page` as cp order by cp.custompage_id DESC ".$page_arr['limit'];
		$query = $this->DatabaseHandler->Query($sql);
		
		$custompages_info = array();
		while($row = $this->DatabaseHandler->GetRow($query))
		{
			$custompages_info[$row['custompage_id']] = $row;
		}
		
		
		include(template('custompage_list','admin'));
		
	}
	
	//新建自定义页面的输入界面
	function Add()
	{
		$page_title="后台管理 - 新建自定义页面";
		
		//一级菜单
		$menu_level_1 = 'yemian';
		//三级菜单
		$menu_level_3 = 'zidingyi';
		
		
		include(template('custompage_add_edit','admin'));
	}
	
	//编辑自定义页面界面
	function Edit()
	{
		$page_title="后台管理 - 编辑自定义页面";
		
		//一级菜单
		$menu_level_1 = 'yemian';
		//三级菜单
		$menu_level_3 = 'zidingyi';
		
		$custompage_id = getPG('cpid');//要编辑的自定义页面id
		
		if(!$custompage_id || $custompage_id<=0)
		{
			$this->Messager('自定义页面id不能为空！', 'admin.php?mod=al', 10);
		}
		
		//判断自定义页面的id存不存在。
		$sql = "SELECT * FROM `qkdb_custom_page` where custompage_id = '".$custompage_id."' ; ";
		
		$query = $this->DatabaseHandler->Query($sql);
		$custompagerow = $this->DatabaseHandler->GetRow($query);
		if(!$custompagerow)
		{
			$this->Messager('该自定义页不存在，可能已被删除！请重新确认后操作。', 'admin.php?mod=cpl', 10);
		}
		
		
		$custompagerow['content_specialchar'] = htmlspecialchars($custompagerow['content']);
		
		include(template('custompage_add_edit','admin'));
		
	}
	
	
	
}
?>