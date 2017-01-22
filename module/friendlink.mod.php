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
				$this->Friendlink();
				break;
		}

	}
	
	
	
	//友情链接页
	function Friendlink()
	{
		$sql = "select * from qkdb_friendlink order by paixu_num DESC";
		$query = $this->DatabaseHandler->Query($sql);
		$fl_list = array();
		while($onerow = $this->DatabaseHandler->GetRow($query))
		{
			$fl_list[$onerow['friendlink_id']] = $onerow;
		}
		
		///////////////////////页头数据准备//////////////////////////////////////////////////////////
		$pagedatalogic = Load::logic('pagedata');
		$page_data = $pagedatalogic->PrepareViewData();//页头页脚数据准备
		///////////////////////页头数据准备结束//////////////////////////////////////////////////////
		
		$page_title = '友情链接页';
		include(template('friendlink'));
		
	}
	
	
	
	
	
	
	
	
	
	
}

?>