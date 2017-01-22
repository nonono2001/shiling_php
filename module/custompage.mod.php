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
		switch($this->Act)
		{

			
				
			default:
				$this->Custompage_detail();
				break;
		}

	}
	
	
	
	//自定义页详情页
	function Custompage_detail()
	{
		$cp_id = trim(getPG('cpid'));//自定义页面id
		
		if($cp_id == 0 || !$cp_id)
		{
			$this->Messager('自定义页面id不能为空！',"index.php",5);
		}
		
		//取自定义页面的详细信息
		$sele_sql = "select * from qkdb_custom_page where custompage_id = '".$cp_id."'";
		$query = $this->DatabaseHandler->Query($sele_sql);
		$cpinfo = $this->DatabaseHandler->GetRow($query);
		
		if(!$cpinfo)
		{
			$this->Messager('该页面不存在，可能已被删除，请重新选择。',"index.php",10);
		}
		
		//////////////////////////////////面包屑数据准备///////////////////////////////////////
		
		$Breadcrumb = $cpinfo['title'];
		//////////////////////////////////面包屑数据准备结束///////////////////////////////////////
		
		///////////////////////左边框数据准备（得到所有自定义页面 : title、id）/////////////////////////
// 		$cp_all_list = array();
// 		$sql = "SELECT * FROM  `qkdb_custom_page` order by lastmodify_time DESC";
// 		$query = $this->DatabaseHandler->Query($sql);
// 		while($onerow = $this->DatabaseHandler->GetRow($query))
// 		{
// 			$cp_all_list[$onerow['custompage_id']]['title'] = $onerow['title']; // 自定义页面标题
// 		}
		///////////////////////左边框数据准备结束////////////////////////////////////////////////////
		
		///////////////////////页头数据准备//////////////////////////////////////////////////////////
		$pagedatalogic = Load::logic('pagedata');
		$page_data = $pagedatalogic->PrepareViewData();//页头页脚数据准备
		///////////////////////页头数据准备结束//////////////////////////////////////////////////////
		
		$page_title = $cpinfo['title'];
		include(template('custompagedetail'));
		
	}
	
	
	
	
	
	
	
	
	
	
}

?>