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
		//因为是前端网站使用的ajax，并且是为了加载widget，所以不用限定已登录，不用限定必需管理员身份
		
// 		if(!MEMBER_ID || MEMBER_ID <= 0)  //判断必需要已登录，并且是管理员身份
// 		{
// 			exit('请先登录。');
// 		}
// 		if(!IS_MEMBER_ADMIN || IS_MEMBER_ADMIN <= 0)//虽然登录了，但不是管理员
// 		{
// 			exit('只有管理员才可以操作。');
// 		}
		
		switch($this->Act)
		{
			case 'get_widget_newsbar':
				$this->Get_widget_newsbar();  //得到newsbar这个widget
				break;
				
			case 'get_widget_leftsidenewsbox':
				$this->Get_widget_leftsidenewsbox(); //得到newsbox这个widget
				break;
				
			case 'get_widget_leftside_productcenter': //得到左侧栏的产品中心这个widget
				$this->Get_widget_leftside_productcenter();
				break;
				
			case 'get_widget_product_cat_select': //得到一个产品分类的下拉框
				$this->Get_widget_product_cat_select();
				break;
			
			case 'get_widget_productcompare': //得到一个产品对比的小控件，在商品详情页
				$this->Get_widget_productcompare();
				break;
				
			default:
				$this->Common();
				break;
		}

	}
	
	function Common()
	{
		echo '';
		exit();	
	}
	
	//获取newsbar这个widget。"dianzi107"模板用到这个widget。
	function Get_widget_newsbar()
	{
		//新闻热点。也就是文章列表
		//所有文章
		$sql = "select * from qkdb_article order by paixu_num DESC ";
		$query = $this->DatabaseHandler->Query($sql);
		$w_articlelist = array();
		while($onerow = $this->DatabaseHandler->GetRow($query))
		{
			$w_articlelist[$onerow['article_id']] = $onerow;
			$w_articlelist[$onerow['article_id']]['lastmodify_date'] = date("Y-m-d",$onerow['lastmodify_time']);
		}
		
		include(template('widget_news_bar'));
	}
	
	//获取leftsidenewsbox这个widget。放在页面左侧栏。
	function Get_widget_leftsidenewsbox()
	{
		//公司新闻box，为了结诚网站而写
		//得到最新的6篇文章
		$sql = "select * from qkdb_article order by lastmodify_time DESC limit 6 ";
		$query = $this->DatabaseHandler->Query($sql);
		$w_articlelist = array();
		
		while($onerow = $this->DatabaseHandler->GetRow($query))
		{
			$w_articlelist[$onerow['article_id']] = $onerow['title'];
// 			$w_articlelist[$onerow['article_id']]['lastmodify_date'] = date("Y-m-d",$onerow['lastmodify_time']);
		}
		
		include(template('widget_leftsidenewsbox'));
	}
	
	//获取所有产品分类的widget，放在页面左侧栏。
	function Get_widget_leftside_productcenter()
	{
		$sql = "select * from qkdb_category order by paixu_num ASC ";
		$query = $this->DatabaseHandler->Query($sql);

		$cat_list = array();
		$catlevel1 = array();
		$catlevel2 = array();
		$catlevel3 = array();
		$catlevel4 = array();
		while($onecatrow = $this->DatabaseHandler->GetRow($query))
		{
			//每$onecatrow就是一个产品分类，一级到四级都有可能。
			if('1' == $onecatrow['level'])
			{
				$catlevel1[$onecatrow['cat_id']] = $onecatrow;
			}
			else if('2' == $onecatrow['level'])
			{
				$catlevel2[$onecatrow['cat_id']] = $onecatrow;
			}
			else if('3' == $onecatrow['level'])
			{
				$catlevel3[$onecatrow['cat_id']] = $onecatrow;
			}
			else if('4' == $onecatrow['level'])
			{
				$catlevel4[$onecatrow['cat_id']] = $onecatrow;
			}
		}
		
		foreach($catlevel2 as $onecatrow)
		{
			$catlevel1[$onecatrow['parent_id']]['haschild'] = 1;

		}
		
		foreach($catlevel3 as $onecatrow)
		{
			$catlevel2[$onecatrow['parent_id']]['haschild'] = 1;
		
		}
		
		foreach($catlevel4 as $onecatrow)
		{
			$catlevel3[$onecatrow['parent_id']]['haschild'] = 1;
		
		}
		
		$cat_list['catlevel1'] = $catlevel1;
		$cat_list['catlevel2'] = $catlevel2;
		$cat_list['catlevel3'] = $catlevel3;
		$cat_list['catlevel4'] = $catlevel4;
		
		$pagedata['catlist'] = $cat_list;
		
		include(template('widget_leftside_productcenter'));
	}
	
	function Get_widget_product_cat_select()
	{
		$selected_cat_id = getPG('cat_id');//可能为空，可能不为空。
		
		//得到现已存在的产品分类。
		$sql = "select * from qkdb_category order by paixu_num ASC";
		$query = $this->DatabaseHandler->Query($sql);
		
		$catlevel1 = array();
		$catlevel2 = array();
		$catlevel3 = array();
		while($cat_row = $this->DatabaseHandler->GetRow($query))
		{
			//每个$cat_row就是一个产品分类，一级到四级都有可能。
			if('1' == $cat_row['level'])
			{
				$catlevel1[$cat_row['cat_id']] = $cat_row;
				if($selected_cat_id == $cat_row['cat_id'])
				{
					$catlevel1[$cat_row['cat_id']]['selected'] = 'selected';
				}
			}
			else if('2' == $cat_row['level'])
			{
				$catlevel2[$cat_row['cat_id']] = $cat_row;
				if($selected_cat_id == $cat_row['cat_id'])
				{
					$catlevel2[$cat_row['cat_id']]['selected'] = 'selected';
				}
			}
			else if('3' == $cat_row['level'])
			{
				$catlevel3[$cat_row['cat_id']] = $cat_row;
				if($selected_cat_id == $cat_row['cat_id'])
				{
					$catlevel3[$cat_row['cat_id']]['selected'] = 'selected';
				}
			}
			else if('4' == $cat_row['level'])
			{
				$catlevel4[$cat_row['cat_id']] = $cat_row;
				if($selected_cat_id == $cat_row['cat_id'])
				{
					$catlevel4[$cat_row['cat_id']]['selected'] = 'selected';
				}
			}
		}
		
		
		
		include(template('widget_product_cat_select','admin'));
		
		
	}
	
	
	function Get_widget_productcompare()
	{
		$cpid = $_COOKIE['product_compare_list'];//该cookie存的就是参与产品对比的product_id，以英文逗号隔开。也可能为空。
		
		$cpid = trim($cpid,',');
		
		$product_compare_list = array();
		
		if($cpid)
		{
			//有产品id，1~4个。
			//获取这些产品的基本信息。图片、名称。
			$sql = "select * from qkdb_product where product_id in (".$cpid.")  order by field(product_id,".$cpid.");";
			$query = $this->DatabaseHandler->Query($sql);
			
			while($oneproduct = $this->DatabaseHandler->GetRow($query))
			{
				//$oneproduct就是一个产品的信息
				//得到产品名称
				$oneproduct['name'] = htmlspecialchars($oneproduct['name']);
				
				//得到产品主图（0~8张）。
				$main_img_arr = get_product_main_img_path($oneproduct['product_id']);
				
				$product_compare_list[$oneproduct['product_id']] = array(
						'product_id' => $oneproduct['product_id'],
						'pic' => $main_img_arr[0],
						'prod_name' => $oneproduct['name'],
				);
				
			}
			
			// 		$product_compare_list 形如： array(
			// 				10  =>  array(
			//                        'product_id' => 10,
			//                        'pic' => 'attachment/prod_10/images/main_images/0.jpg',
			//                        'prod_name' => '天地一号',
			//                       ), //key是product_id
			// 				13  =>  array(
			//                        'product_id' => 13,
			//                        'pic' => 'attachment/prod_13/images/main_images/0.jpg',
			//                        'prod_name' => '飞龙在天',
			//                       ),
			// 		        14  =>  array(
			//                        'product_id' => 14,
			//                        'pic' => 'attachment/prod_14/images/main_images/0.jpg',
			//                        'prod_name' => '糊涂虫',
			//                       ),
			// 				);
		}
		
		include(template('widget_product_compare'));
	}
	
	
}
?>