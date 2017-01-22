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
			case 'download_detail':  //下载详情
				$this->Download_detail();
				break;
			
				
			default:
				$this->Downloadlist();  //下载列表
				break;
		}

	}
	
	
	
	//下载列表页
	function Downloadlist()
	{
		$topage = getPG('page');
		
		if(!$topage)
		{
			$topage = 1;
		}
		
		$per_page_num = 10;//每页条数。同步加载出的条数

		//////////////////////////////////面包屑数据准备，$countsql数据准备/////////////////////////////////
		$Breadcrumb = '下载列表';
		//////////////////////////////////面包屑数据准备结束///////////////////////////////////////
		
		$countsql = "select count(*) as total_record from qkdb_download_url ";;
		$query = $this->DatabaseHandler->Query($countsql);
		extract($this->DatabaseHandler->GetRow($query));//得到变量total_record
			
		$page_arr = page_limit($total_record, $per_page_num, $topage, 'index.php?mod=dl');
		
		//所有下载资料
		$sql = "select * from qkdb_download_url order by paixu_num DESC ".$page_arr['limit'];
		
		$downloadlist = array();
		
		$query = $this->DatabaseHandler->Query($sql);
		while($onerow = $this->DatabaseHandler->GetRow($query))
		{
			$downloadlist[$onerow['id']] = $onerow; 
		}
		
		
		///////////////////////页头数据准备//////////////////////////////////////////////////////////
		$pagedatalogic = Load::logic('pagedata');
		$page_data = $pagedatalogic->PrepareViewData();//页头页脚数据准备
		///////////////////////页头数据准备结束//////////////////////////////////////////////////////
				
		$page_title = '下载列表';
		include(template('downloadlist'));
		
	}
	
	//下载详情
	function Download_detail()
	{
		$d_id = trim(getPG('did'));//下载id
		
		if($d_id == 0 || !$d_id)
		{
			$this->Messager('下载id不能为空。',"index.php?mod=dl",5);
		}
		
		$sele_sql = "select * from qkdb_download_url where id = '".$d_id."'";
		$query = $this->DatabaseHandler->Query($sele_sql);
		$downloadinfo = $this->DatabaseHandler->GetRow($query);
		
		if(!$downloadinfo)
		{
			$this->Messager('该下载不存在，可能已被删除，请重新选择。',"index.php?mod=dl",10);
		}
		
		//////////////////////////////////面包屑数据准备///////////////////////////////////////
		
		$Breadcrumb = $downloadinfo['download_title'];
		//////////////////////////////////面包屑数据准备结束///////////////////////////////////////
		
		
		///////////////////////页头数据准备//////////////////////////////////////////////////////////
		$pagedatalogic = Load::logic('pagedata');
		$page_data = $pagedatalogic->PrepareViewData();//页头页脚数据准备
		///////////////////////页头数据准备结束//////////////////////////////////////////////////////
		
		$page_title = '下载详情';		
		
		include(template('downloaddetail'));
	}
	
	
	
	
	
	
	
	
}

?>