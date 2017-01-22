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
			
			case 'search':
				$this->Search();
				break;
				
			default:
				$this->Prodlist();
				break;
		}

	}
	
	
	
	//产品列表页（即商品分类的结果）
	function Prodlist()
	{
		$pcat_id = getPG('pc');//产品分类id，为0代表全部产品
		
		$topage = getPG('page');

		if(!$topage)
		{
			$topage = 1;
		}
		$per_page_num = 12;//每页产品条数。同步加载出的产品条数
		
		
		//////////////////////////////////面包屑数据准备，$countsql数据准备/////////////////////////////////
		$Breadcrumb = '';
		if($pcat_id == 0 || !$pcat_id)
		{
			$countsql = "select count(*) as total_record from qkdb_product";
			
			$Breadcrumb = "全部产品";
		}
		else 
		{
			$sql = "select * from qkdb_category where cat_id = '".$pcat_id."'";
			$query = $this->DatabaseHandler->Query($sql);
				
			$onecat = $this->DatabaseHandler->GetRow($query);
			if(!$onecat)
			{
				$this->Messager("该分类不存在，请重新选择。","index.php?mod=pl&pc=0", 10);
			}
			
			$countsql = "select count(*) as total_record from qkdb_product p " .
					    "left join qkdb_product_cat pc on p.product_id = pc.product_id " .
			            "left join qkdb_category c on c.cat_id = pc.cat_id where c.full_id like '%,".$pcat_id.",%'";
			
			$Breadcrumb = $onecat['name'];
		}
		//////////////////////////////////面包屑数据准备结束///////////////////////////////////////
		
		
		$query = $this->DatabaseHandler->Query($countsql);
		extract($this->DatabaseHandler->GetRow($query));//得到变量total_record
			
		$page_arr = page_limit($total_record, $per_page_num, $topage, 'index.php?mod=pl&pc='.$pcat_id);
		//得到每个产品的信息
		if($pcat_id == 0 || !$pcat_id)
		{
			//所有产品
			$sql = "select * from qkdb_product order by paixu_num DESC ".$page_arr['limit'];
		}
		else 
		{
			//$pcat_id，这个分类的产品
			$sql = "select p.* from qkdb_product p " .
			       "left join qkdb_product_cat pc on p.product_id = pc.product_id " .
			       "left join qkdb_category c on c.cat_id = pc.cat_id where c.full_id like '%,".$pcat_id.",%' order by p.paixu_num DESC " . 
			       $page_arr['limit'];
		
		}
		
		$productlist = array();
		
		$query = $this->DatabaseHandler->Query($sql);
		while($onerow = $this->DatabaseHandler->GetRow($query))
		{
			$productlist[$onerow['product_id']]['name'] = $onerow['name']; // 产品名称
			$productlist[$onerow['product_id']]['bn'] = $onerow['bn']; //货号
			$productlist[$onerow['product_id']]['introduction'] = $onerow['introduction'];//简介
			
			//产品主图
			$main_img_arr = get_product_main_img_path($onerow['product_id']);
			$productlist[$onerow['product_id']]['main_img'] = $main_img_arr;//可能为空
		}
		
		///////////////////////左边框数据准备（得到所有一级分类 : 名称、id）/////////////////////////
// 		$pc_first_level = array();
// 		$sql = "SELECT * FROM  `qkdb_category` WHERE level = 1 order by paixu_num ASC";
// 		$query = $this->DatabaseHandler->Query($sql);
// 		while($onerow = $this->DatabaseHandler->GetRow($query))
// 		{
// 			$pc_first_level[$onerow['cat_id']]['name'] = $onerow['name']; // 分类名称
// 		}
		///////////////////////左边框数据准备结束////////////////////////////////////////////////////
		
		///////////////////////页头数据准备//////////////////////////////////////////////////////////
		$pagedatalogic = Load::logic('pagedata');
		$page_data = $pagedatalogic->PrepareViewData();//页头页脚数据准备
		///////////////////////页头数据准备结束//////////////////////////////////////////////////////
			
		$page_title = '产品分类';
		include(template('prodlist'));
		
	}
	
	//搜索产品
	function Search()
	{
		$keywords = getPG('keywords');
		$currenthref = getPG('currenthref'); //用户搜索时当前的href。用于报错时返回当前页
		
		if(!$keywords && $keywords!= '0')
		{
			$this->Messager('搜索的关键字不能为空。返回上一页。',stripslashes($currenthref),10);
		}
		$keywords_no_slash = stripslashes($keywords);
		
		$Breadcrumb = "搜索“".$keywords_no_slash."”结果";
		
		//把搜索的内容记录到表中qkdb_searchlog_product
		$insertdata = array(
				'content' => $keywords,
				'time' => time(),
		);
		$this->DatabaseHandler->insert('qkdb_searchlog_product', $insertdata);
		
		//去搜索产品正文。qkdb_product表的detail字段。detail字段里是带有html标签的文本。
		$sql = "select * from qkdb_product where detail like '%".$keywords."%' order by paixu_num DESC " ;
		$query = $this->DatabaseHandler->Query($sql);
		
		$topage = getPG('page');
		if(!$topage)
		{
			$topage = 1;
		}
		$per_page_num = 12;//每页产品条数。同步加载出的产品条数
		$total_record = mysql_num_rows($query);
		$page_arr = page_limit($total_record, $per_page_num, $topage, 'index.php?mod=pl&act=search&keywords='.$keywords_no_slash);
		
		$firstnum = ($topage-1)*$per_page_num;
		$lastnum = $firstnum + $per_page_num -1;
		
		$productlist = array();
		$i = 0;
		while ($onerow = $this->DatabaseHandler->GetRow($query))
		{
			if($i >= $firstnum && $i <= $lastnum)
			{
				$productlist[$onerow['product_id']]['name'] = $onerow['name']; // 产品名称
				$productlist[$onerow['product_id']]['bn'] = $onerow['bn']; //货号
				$productlist[$onerow['product_id']]['introduction'] = $onerow['introduction'];//简介
				
				//产品主图
				$main_img_arr = get_product_main_img_path($onerow['product_id']);
				$productlist[$onerow['product_id']]['main_img'] = $main_img_arr;//可能为空
			}
			if($i > $lastnum)
			{
				break;
			}
			$i++;
		}
		
		///////////////////////页头数据准备//////////////////////////////////////////////////////////
		$pagedatalogic = Load::logic('pagedata');
		$page_data = $pagedatalogic->PrepareViewData();//页头页脚数据准备
		///////////////////////页头数据准备结束//////////////////////////////////////////////////////
		
		$page_title = '搜索结果';
		include(template('prodlist'));
		
	}
	
	
	
	
	
	
	
	
}

?>