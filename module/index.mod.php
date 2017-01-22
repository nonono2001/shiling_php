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
				$this->Main();
				break;
		}

	}
	
	function Main()
	{
		//最新资讯。也就是文章列表
		//所有文章
		$sql = "select * from qkdb_article order by paixu_num DESC ";
		$query = $this->DatabaseHandler->Query($sql);
		$articlelist = array();
		while($onerow = $this->DatabaseHandler->GetRow($query))
		{
			$articlelist[$onerow['article_id']] = $onerow; 
			$articlelist[$onerow['article_id']]['lastmodify_date'] = date("Y-m-d",$onerow['lastmodify_time']);
		}
		
		
		//关于我们。也就是自定义页列表
		$cp_all_list = array();
		$sql = "SELECT * FROM  `qkdb_custom_page` order by lastmodify_time DESC";
		$query = $this->DatabaseHandler->Query($sql);
		while($onerow = $this->DatabaseHandler->GetRow($query))
		{
			$cp_all_list[$onerow['custompage_id']] = $onerow; 
			$cp_all_list[$onerow['custompage_id']]['lastmodify_date'] = date("Y-m-d",$onerow['lastmodify_time']);
		}
		
		
		//产品中心。产品列表。这段暂不开启
// 		$sql = "select * from qkdb_product order by paixu_num DESC ";
// 		$query = $this->DatabaseHandler->Query($sql);
// 		$productlist = array();
// 		while($onerow = $this->DatabaseHandler->GetRow($query))
// 		{
// 			$productlist[$onerow['product_id']]['name'] = $onerow['name']; // 产品名称
				
// 			//产品主图
// 			$main_img_arr = get_product_main_img_path($onerow['product_id']);
// 			$productlist[$onerow['product_id']]['main_img'] = $main_img_arr;//可能为空
// 		}

		//模板展示。六个分类，每个分类展示6个模板。
		$templates_show = array(
				array('cat_id'=>'140', 'name'=>'电子、电气'),
				array('cat_id'=>'128', 'name'=>'美容、护肤'),
				array('cat_id'=>'132', 'name'=>'化工、涂料'),
				array('cat_id'=>'137', 'name'=>'设计、装饰'),
				array('cat_id'=>'138', 'name'=>'教育、培训'),
				array('cat_id'=>'157', 'name'=>'珠宝、首饰'),
		);
		foreach($templates_show as $key => $onecat)
		{
			$sql = "SELECT *, p.name as proname
					FROM  `qkdb_product` p
					LEFT JOIN qkdb_product_cat pc ON p.product_id = pc.product_id
					LEFT JOIN qkdb_category cat ON cat.cat_id = pc.cat_id
					WHERE cat.cat_id =  '".$onecat['cat_id']."' order by p.product_id DESC limit 6";
			$query = $this->DatabaseHandler->Query($sql);
			$productlist = array();
			while($onerow = $this->DatabaseHandler->GetRow($query))
			{
				$productlist[$onerow['product_id']]['name'] = $onerow['proname']; // 产品名称
				$productlist[$onerow['product_id']]['introduction'] = $onerow['introduction'];//产品介绍，也就是模板图片
				$productlist[$onerow['product_id']]['product_id'] = $onerow['product_id'];//模板编号
			}
			
			$templates_show[$key]['products'] = $productlist;
		}
		
		
		
		///////////////////////////////////公司环境图片//////////////////////////////////////////////
		
		
		
		
		///////////////////////页头数据准备//////////////////////////////////////////////////////////
		$pagedatalogic = Load::logic('pagedata');
		$page_data = $pagedatalogic->PrepareViewData();//页头页脚数据准备
		///////////////////////页头数据准备结束//////////////////////////////////////////////////////
		
		
		$page_title = '首页';
		include(template('index'));
		
		
	}	
	
}

?>