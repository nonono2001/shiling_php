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
			$this->Messager("您不是管理员，不可进入后台管理系统。请用管理员身份登录。","admin.php", 10);
		}
		
		switch($this->Act)
		{
			case 'member_edit':
				$this->Member_edit();  //会员编辑页面
				break;

				
			default:
				$this->Member_list(); //会员列表
				break;
		}

	}

	//会员列表
	function Member_list()
	{
		$page_title="后台管理 - 会员列表";

		//一级菜单
		$menu_level_1 = 'member';
		//三级菜单
		$menu_level_3 = 'memberlist';

		$countsql = "select count(member_id) as total_record from qkdb_member ";
		$query = $this->DatabaseHandler->Query($countsql);
		extract($this->DatabaseHandler->GetRow($query));//得到变量total_record

		$topage = getPG('page');
		if(!$topage)
		{
			$topage = 1;
		}
		$per_page_num = 20;//每页条数。同步加载出的条数

		$page_arr = page_limit($total_record, $per_page_num, $topage, 'admin.php?mod=member');

		$sql = "SELECT * FROM qkdb_member order by member_id DESC " . $page_arr['limit'];
		$query = $this->DatabaseHandler->Query($sql);

		$member_info = array();
		while($row = $this->DatabaseHandler->GetRow($query))
		{
			if($row['lastlogin_dateline'] > 0)
			{
				$row['lastlogin_dateline'] =  date("Y-m-d H:i:s",$row['lastlogin_dateline']);
			}
			else
			{
				$row['lastlogin_dateline'] = '';
			}

			$member_info[$row['member_id']] = $row;
		}

		include(template('member_list','admin'));
	}

	//会员编辑页
	function Member_edit()
	{
		$page_title="后台管理 - 会员编辑";
		//一级菜单
		$menu_level_1 = 'member';
		//三级菜单
		$menu_level_3 = 'memberlist';

		$member_id = getPG('member_id'); //会员的id。
		$topage = getPG('topage');//可能为空。用于编辑保存后，返回列表时定位到第几页。

		$sql = "select * from qkdb_member where member_id = '".$member_id."'";
		$query = $this->DatabaseHandler->Query($sql);
		$onememberinfo = $this->DatabaseHandler->GetRow($query);
		if(!$onememberinfo)
		{
			$this->Messager('该会员不存在，可能已被删除。');
		}

		if($onememberinfo['lastlogin_dateline'] > 0)
		{
			$onememberinfo['lastlogin_dateline'] =  date("Y-m-d H:i:s",$onememberinfo['lastlogin_dateline']);
		}
		else
		{
			$onememberinfo['lastlogin_dateline'] = '';
		}


		include(template('member_edit','admin'));
	}
	
	

}
?>