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
				$this->Add(); //新增一个下载
				break;
				
			case 'edit':
				$this->Edit(); //编辑一个下载
				break;
			
			case 'download_detail':
				$this->Download_detail(); //下载详情
				break;
				
			default:
				$this->Download_list(); //下载列表
				break;
		}

	}
	
	//下载列表
	function Download_list()
	{
		$page_title="后台管理 - 下载列表";
		
		//一级菜单
		$menu_level_1 = 'xiazai';
		//三级菜单
		$menu_level_3 = 'downloadlist';
		
		$countsql = "select count(id) as total_record from qkdb_download_url ";
		$query = $this->DatabaseHandler->Query($countsql);
		extract($this->DatabaseHandler->GetRow($query));//得到变量total_record
		
		$topage = getPG('page');
		if(!$topage)
		{
			$topage = 1;
		}
		$per_page_num = 10;//每页条数。同步加载出的条数
		
		$page_arr = page_limit($total_record, $per_page_num, $topage, 'admin.php?mod=download');
		
		$sql = "SELECT * 
 FROM `qkdb_download_url` order by paixu_num DESC ".$page_arr['limit'];
		$query = $this->DatabaseHandler->Query($sql);
		
		$item_info = array();
		while($row = $this->DatabaseHandler->GetRow($query))
		{
			$item_info[$row['id']] = $row;
		}
		
		
		include(template('download_list','admin'));
		
	}
	
	//下载详情
	function Download_detail()
	{
		$page_title="后台管理 - 下载详情";
		
		//一级菜单
		$menu_level_1 = 'xiazai';
		//三级菜单
		$menu_level_3 = 'downloadlist';
		
		$did = getPG('did');//要查看的下载id
		
		if(!$did || $did<=0)
		{
			$this->Messager('下载的id不能为空！', 'admin.php?mod=download', 10);
		}
		
		//判断下载id存不存在。
		$sql = "select * from qkdb_download_url where id = '".$did."'";
		$query = $this->DatabaseHandler->Query($sql);
		$downloadurlrow = $this->DatabaseHandler->GetRow($query);
		if(!$downloadurlrow)
		{
			$this->Messager('该下载不存在，可能已被删除！请重新确认后操作。', 'admin.php?mod=download', 10);
		}
		
		
		$countsql = "select count(*) as total_record FROM qkdb_download_info where download_id = '".$did."'";
		$query = $this->DatabaseHandler->Query($countsql);
		extract($this->DatabaseHandler->GetRow($query));//得到变量total_record
		
		$topage = getPG('page');
		if(!$topage)
		{
			$topage = 1;
		}
		$per_page_num = 20;//每页条数。同步加载出的条数
		$page_arr = page_limit($total_record, $per_page_num, $topage, 'admin.php?mod=download&&act=download_detail&did='.$did);
		
		$sql = "SELECT * FROM qkdb_download_info where download_id = '".$did."' order by download_time DESC ".$page_arr['limit'];;
		$query = $this->DatabaseHandler->Query($sql);
		
		$downloadinfo = array();
		while($downloadrow = $this->DatabaseHandler->GetRow($query))
		{
			$downloadrow['time'] = date("Y-m-d H:i:s",$downloadrow['download_time']);
			$downloadinfo[$downloadrow['diid']] = $downloadrow;
		}
		
		include(template('download_detail','admin'));
	}
	
	function Add()
	{
		$page_title="后台管理 - 添加友情链接";
		
		//一级菜单
		$menu_level_1 = 'youlian';
		//三级菜单
		$menu_level_3 = 'friendlinklist';
		
		
		
		include(template('friendlink_add_edit','admin'));
	}
	
	function Edit()
	{
		$page_title="后台管理 - 添加友情链接";
		
		//一级菜单
		$menu_level_1 = 'youlian';
		//三级菜单
		$menu_level_3 = 'friendlinklist';
		
		$friendlink_id = getPG('flid');
		
		$sql = "select * from qkdb_friendlink where friendlink_id = '".$friendlink_id."'";
		
		$qurey = $this->DatabaseHandler->Query($sql);
		
		$friendlinkrow = $this->DatabaseHandler->GetRow($qurey);
		
		if(!$friendlinkrow)
		{
			$this->Messager('该友链不存在，可能已被删除！请重新确认后操作。', 'admin.php?mod=fll', 10);
		}
		
		
		include(template('friendlink_add_edit','admin'));
	}
	
	
}
?>