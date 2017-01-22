<?php
//fs.mod.php代表free style的页面。
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

			case 'caseshow':
				$this->Caseshow();
				break;
				
			case 'fuwu':
				$this->Fuwu();
				break;
				
			case 'aboutus':
				$this->Aboutus();
				break;
				
			default:
				$this->Common();
				break;
		}

	}
	
	function Common()
	{
		echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">缺少参数......';
	}
	
	
	//成功案例展示
	function Caseshow()
	{
		///////////////////////页头数据准备//////////////////////////////////////////////////////////
		$pagedatalogic = Load::logic('pagedata');
		$page_data = $pagedatalogic->PrepareViewData();//页头页脚数据准备
		///////////////////////页头数据准备结束//////////////////////////////////////////////////////
		
		$page_title = '案例展示';
		include(template('fs_caseshow'));
		
	}
	
	
	//服务范围
	function Fuwu()
	{
		
		///////////////////////页头数据准备//////////////////////////////////////////////////////////
		$pagedatalogic = Load::logic('pagedata');
		$page_data = $pagedatalogic->PrepareViewData();//页头页脚数据准备
		///////////////////////页头数据准备结束//////////////////////////////////////////////////////
		
		$page_title = '服务范围';
		include(template('fs_fuwu'));
		
		
	}
	
	//关于我们
	function Aboutus()
	{
		///////////////////////页头数据准备//////////////////////////////////////////////////////////
		$pagedatalogic = Load::logic('pagedata');
		$page_data = $pagedatalogic->PrepareViewData();//页头页脚数据准备
		///////////////////////页头数据准备结束//////////////////////////////////////////////////////
		
		$page_title = '关于我们';
		include(template('fs_aboutus'));
	}
	
	
	
	
	
}

?>