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
				$this->Advise_list(); //留言列表
				break;
		}

	}
	
	//留言列表
	function Advise_list()
	{
		$page_title="后台管理 - 留言列表";
		
		//一级菜单
		$menu_level_1 = 'liuyan';
		//三级菜单
		$menu_level_3 = 'adviselist';
		
		$countsql = "select count(advise_id) as total_record from qkdb_advise ";
		$query = $this->DatabaseHandler->Query($countsql);
		extract($this->DatabaseHandler->GetRow($query));//得到变量total_record
		
		$topage = getPG('page');
		if(!$topage)
		{
			$topage = 1;
		}
		$per_page_num = 10;//每页条数。同步加载出的条数
		
		$page_arr = page_limit($total_record, $per_page_num, $topage, 'admin.php?mod=adl');
		
		$sql = "SELECT * ,from_unixtime(create_time) as createtime 
 FROM `qkdb_advise` order by advise_id DESC ".$page_arr['limit'];
		$query = $this->DatabaseHandler->Query($sql);
		
		$advises_info = array();
		while($row = $this->DatabaseHandler->GetRow($query))
		{
			$row['nickname'] = htmlspecialchars($row['nickname']);
			$row['email'] = htmlspecialchars($row['email']);
			$row['phone'] = htmlspecialchars($row['phone']);
			$row['content'] = htmlspecialchars($row['content']);
			$row['createtime'] = date("Y-m-d H:i:s",$row['create_time']);
			
			$advises_info[$row['advise_id']] = $row;
		}
		
		
		include(template('advise_list','admin'));
		
	}
	
	
}
?>