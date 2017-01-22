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
				$this->Article_detail();
				break;
		}

	}
	
	
	
	//文章详情页
	function Article_detail()
	{
		
		$a_id = trim(getPG('aid'));//文章id
		
		if($a_id == 0 || !$a_id)
		{
			$this->Messager('文章id不能为空。',"index.php",5);
		}
		
		//浏览次数+1.
		$update_sql = "update qkdb_article set visit_times = visit_times + 1 where article_id = '".$a_id."'";
		$this->DatabaseHandler->Query($update_sql);
		
		$sele_sql = "select * from qkdb_article where article_id = '".$a_id."'";
		$query = $this->DatabaseHandler->Query($sele_sql);
		$articleinfo = $this->DatabaseHandler->GetRow($query);
		
		
		$articleinfo['create_time'] = date("Y-m-d H:i:s",$articleinfo['create_time']);
		
		
		if(!$articleinfo)
		{
			$this->Messager('该文章不存在，可能已被删除，请重新选择。',"index.php",10);
		}
		
		//////////////////////////////////面包屑数据准备///////////////////////////////////////
		
		$Breadcrumb = $articleinfo['title'];
		//////////////////////////////////面包屑数据准备结束///////////////////////////////////////
		
		//得到该文章所属分类id，通过qkdb_article_cat表。
		$sql = "select * from qkdb_article_cat where article_id = '".$a_id."'";
		$query = $this->DatabaseHandler->Query($sql);
		$acatrow = $this->DatabaseHandler->GetRow($query);
		
		$acat_id = $acatrow['cat_id'];//可能为空，如果为空，说明文章未分类
		///////////////////////左边框数据准备（得到所有一级分类 : 名称、id）/////////////////////////
		$ac_first_level = array();
		$sql = "SELECT * FROM  `qkdb_artcategory` WHERE level = 1 order by paixu_num ASC";
		$query = $this->DatabaseHandler->Query($sql);
		while($onerow = $this->DatabaseHandler->GetRow($query))
		{
			$ac_first_level[$onerow['cat_id']]['name'] = $onerow['name']; // 分类名称
		}
		///////////////////////左边框数据准备结束////////////////////////////////////////////////////
		
		///////////////////////页头数据准备//////////////////////////////////////////////////////////
		$pagedatalogic = Load::logic('pagedata');
		$page_data = $pagedatalogic->PrepareViewData();//页头页脚数据准备
		///////////////////////页头数据准备结束//////////////////////////////////////////////////////
		
		$page_title = '文章详情';
		include(template('articledetail'));
		
	}
	
	
	
	
	
	
	
	
	
	
}

?>