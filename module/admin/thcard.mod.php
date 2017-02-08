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
			case 'thcard_edit': //编辑提货卡券的页面
				$this->Thcard_edit();
				break;

				
			default:
				$this->Thcard_list(); //提货卡列表
				break;
		}

	}
	
	//提货卡列表
	function Thcard_list()
	{
		$page_title="后台管理 - 提货卡";
		
		//一级菜单
		$menu_level_1 = 'kaquan';
		//三级菜单
		$menu_level_3 = 'tihuoka';
		
		$countsql = "select count(id) as total_record from qkdb_tihuo_card ";
		$query = $this->DatabaseHandler->Query($countsql);
		extract($this->DatabaseHandler->GetRow($query));//得到变量total_record
		
		$topage = getPG('page');
		if(!$topage)
		{
			$topage = 1;
		}
		$per_page_num = 20;//每页产品条数。同步加载出的产品条数
		
		$page_arr = page_limit($total_record, $per_page_num, $topage, 'admin.php?mod=thcard');
		
		$sql = "SELECT * FROM qkdb_tihuo_card order by ship_status DESC, is_ask_tihuo DESC , last_ask_tihuo_time DESC " . $page_arr['limit'];
		$query = $this->DatabaseHandler->Query($sql);
		
		$thcard_info = array();
		while($row = $this->DatabaseHandler->GetRow($query))
		{
			$row['validity_start_date'] = date("Y-m-d",$row['validity_start_date']); //有效期开始日期
			$row['validity_end_date'] = date("Y-m-d",$row['validity_end_date']); //有效期结束日期
			if($row['last_ask_tihuo_time'] > 0)
			{
				$row['last_ask_tihuo_time'] = date("Y-m-d H:i:s",$row['last_ask_tihuo_time']); //申请提货的时间
			}
			else
			{
				$row['last_ask_tihuo_time'] = '';
			}


			if($row['ship_time'] > 0)
			{
				$row['ship_time'] = date("Y-m-d H:i:s",$row['ship_time']); //发货时间
			}
			else
			{
				$row['ship_time'] = '';
			}

			//提货客户端类型
			if($row['front_type'] == 'wx')
			{
				$row['front_type_name'] = '微信';
			}
			else if($row['front_type'] == 'xcx')
			{
				$row['front_type_name'] = '小程序';
			}

			$thcard_info[$row['id']] = $row;
		}
		
		
		include(template('tihuoka_list','admin'));
		
	}

	//编辑提货卡券的页面
	function Thcard_edit()
	{
		$thcard_id = getPG('id'); //提货卡券的id。
		$topage = getPG('topage'); //编辑完毕后，跳回列表的第几页

		$sql = "select * from qkdb_tihuo_card where id = '".$thcard_id."'";
		$query = $this->DatabaseHandler->Query($sql);
		$onecardinfo = $this->DatabaseHandler->GetRow($query);
		if(!$onecardinfo)
		{
			$this->Messager('该提货卡id不存在，可能已被删除。');
		}

		$onecardinfo['validity_start_date'] = date("Y-m-d",$onecardinfo['validity_start_date']); //有效期开始日期
		$onecardinfo['validity_end_date'] = date("Y-m-d",$onecardinfo['validity_end_date']); //有效期结束日期
		if($onecardinfo['last_ask_tihuo_time'] > 0)
		{
			$onecardinfo['last_ask_tihuo_time'] = date("Y-m-d H:i:s",$onecardinfo['last_ask_tihuo_time']); //申请提货的时间
		}
		else
		{
			$onecardinfo['last_ask_tihuo_time'] = '';
		}

		if($onecardinfo['is_ask_tihuo'] > 0)
		{
			$onecardinfo['is_ask_tihuo'] = '是';
		}
		else
		{
			$onecardinfo['is_ask_tihuo'] = '否';
		}


		if($onecardinfo['ship_time'] > 0)
		{
			$onecardinfo['ship_time'] = date("Y-m-d H:i:s",$onecardinfo['ship_time']); //发货时间
		}
		else
		{
			$onecardinfo['ship_time'] = '';
		}


		$page_title="后台管理 - 编辑提货卡券";
		//一级菜单
		$menu_level_1 = 'kaquan';
		//三级菜单
		$menu_level_3 = 'tihuoka';
		include(template('tihuoka_edit','admin'));
	}

}
?>