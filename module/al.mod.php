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
// 			case 'prodlist':
// 				$this->Prodlist();
// 				break;
			
				
			default:
				$this->Articlelist();
				break;
		}

	}
	
	
	
	//文章列表页
	function Articlelist()
	{
		
		$acat_id = getPG('ac');//文章分类id，为0代表全部文章
		
		
		$topage = getPG('page');
		
		if(!$topage)
		{
			$topage = 1;
		}
		
		$per_page_num = 10;//每页文章条数。同步加载出的文章条数

		//////////////////////////////////面包屑数据准备，$countsql数据准备/////////////////////////////////
		$Breadcrumb = '';
		if($acat_id == 0 || !$acat_id)
		{
			$countsql = "select count(*) as total_record from qkdb_article";
			$Breadcrumb = "全部文章";
		}
		else
		{
			$sql = "select * from qkdb_artcategory where cat_id = '".$acat_id."'";
			$query = $this->DatabaseHandler->Query($sql);
			
			$onecat = $this->DatabaseHandler->GetRow($query);
			if(!$onecat)
			{
				$this->Messager("该分类不存在，请重新选择。","index.php?mod=al&ac=0", 10);
			}
			
			$countsql = "select count(*) as total_record from qkdb_article a " .
					"left join qkdb_article_cat ac on a.article_id = ac.article_id " .
					"left join qkdb_artcategory c on c.cat_id = ac.cat_id where c.full_id like '%,".$acat_id.",%'";
				
			$Breadcrumb = $onecat['name'];
		}
		//////////////////////////////////面包屑数据准备结束///////////////////////////////////////
		
		$query = $this->DatabaseHandler->Query($countsql);
		extract($this->DatabaseHandler->GetRow($query));//得到变量total_record
			
		$page_arr = page_limit($total_record, $per_page_num, $topage, 'index.php?mod=al&ac='.$acat_id);
		//得到每个文章的信息
		if($acat_id == 0 || !$acat_id)
		{
			//所有文章
			$sql = "select * from qkdb_article order by paixu_num DESC ".$page_arr['limit'];
		}
		else
		{
			//$acat_id，这个分类的文章
			$sql = "select a.* from qkdb_article a " .
					"left join qkdb_article_cat ac on a.article_id = ac.article_id " .
					"left join qkdb_artcategory c on c.cat_id = ac.cat_id where c.full_id like '%,".$acat_id.",%' order by a.paixu_num DESC " .
					$page_arr['limit'];
		}
		
		$articlelist = array();
		
		$query = $this->DatabaseHandler->Query($sql);
		while($onerow = $this->DatabaseHandler->GetRow($query))
		{
			$articlelist[$onerow['article_id']] = $onerow; 
			$articlelist[$onerow['article_id']]['lastmodify_date'] = date("Y-m-d H:i:s",$onerow['lastmodify_time']);
		}
		
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
				
		$page_title = '文章分类';
		include(template('articlelist'));
		
	}
	
	
	
	
	
	
	
	
	
	
}

?>