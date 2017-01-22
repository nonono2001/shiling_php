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
				$this->Add();  //添加新产品
				break;
			
			case 'edit':
				$this->Edit(); //编辑已存在的产品
				break;
				
			case 'comparekey':
				$this->Comparekey(); //编辑产品对比的属性
				break;
				
			case 'edit_compare_value': //编辑一个产品它用于对比的值。
				$this->Edit_compare_value();
				break;
				
			case 'search_log_product': //搜索产品的关键字记录
				$this->Search_log_product();
				break;
				
			default:
				$this->Product_list(); //产品列表
				break;
		}

	}
	
	//产品列表
	function Product_list()
	{
		$page_title="后台管理 - 产品列表";
		
		//一级菜单
		$menu_level_1 = 'products';
		//三级菜单
		$menu_level_3 = 'productlist';
		
		//cat_id参数
		$cat_id = getPG('cat_id');
		
		$countsql ='';
		if($cat_id)
		{
			$countsql = "SELECT count(p.product_id) as total_record FROM `qkdb_product` as p left join qkdb_product_cat as pc 
on pc.product_id = p.product_id  
left join qkdb_category as c 
on c.cat_id = pc.cat_id  where c.full_id like '%,".$cat_id.",%'";
		}
		else 
		{
			$countsql = "select count(product_id) as total_record from qkdb_product ";
		}
		
		$query = $this->DatabaseHandler->Query($countsql);
		extract($this->DatabaseHandler->GetRow($query));//得到变量total_record
		
		$topage = getPG('page');
		if(!$topage)
		{
			$topage = 1;
		}
		
		$per_page_num = 10;//每页产品条数。同步加载出的产品条数
		
		$page_arr = '';
		$sql = '';
		if($cat_id)
		{
			$page_arr = page_limit($total_record, $per_page_num, $topage, 'admin.php?mod=pl&cat_id='.$cat_id);
			$sql = "SELECT p.* ,from_unixtime(p.create_time) as createtime,from_unixtime(p.lastmodify_time) as lastmodifytime,
pc.cat_id, c.name as catname
 FROM `qkdb_product` as p left join qkdb_product_cat as pc
on pc.product_id = p.product_id
left join qkdb_category as c
on c.cat_id = pc.cat_id where c.full_id like '%,".$cat_id.",%' order by p.paixu_num DESC ".$page_arr['limit'];
		}
		else
		{
			$page_arr = page_limit($total_record, $per_page_num, $topage, 'admin.php?mod=pl');
			$sql = "SELECT p.* ,from_unixtime(p.create_time) as createtime,from_unixtime(p.lastmodify_time) as lastmodifytime,
pc.cat_id, c.name as catname
 FROM `qkdb_product` as p left join qkdb_product_cat as pc
on pc.product_id = p.product_id
left join qkdb_category as c
on c.cat_id = pc.cat_id  order by p.paixu_num DESC ".$page_arr['limit'];
		}
		
		
		
		$query = $this->DatabaseHandler->Query($sql);
		
		$products_info = array();
		while($row = $this->DatabaseHandler->GetRow($query))
		{
			$products_info[$row['product_id']] = $row;
			$products_info[$row['product_id']]['name'] = htmlspecialchars($products_info[$row['product_id']]['name']);
		}
		
		include(template('product_list','admin'));
		
	}
	
	//新建产品界面
	function Add()
	{
		$page_title="后台管理 - 产品添加";
		
		//一级菜单
		$menu_level_1 = 'products';
		//三级菜单
		$menu_level_3 = 'productlist';
		
		//封装产品分类
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
			}
			else if('2' == $cat_row['level'])
			{
				$catlevel2[$cat_row['cat_id']] = $cat_row;
			}
			else if('3' == $cat_row['level'])
			{
				$catlevel3[$cat_row['cat_id']] = $cat_row;
			}
			else if('4' == $cat_row['level'])
			{
				$catlevel4[$cat_row['cat_id']] = $cat_row;
			}
		}
		
		include(template('product_add','admin'));
	}
	
	//编辑产品界面
	function Edit()
	{
		$page_title="后台管理 - 产品编辑";
		
		$product_id = getPG('pid');
		
		if(!$product_id || $product_id<=0)
		{
			$this->Messager('产品id不能为空！', 'admin.php?mod=pl', 10);
		}
		
		//判断产品id存不存在。
		$sql = "SELECT p.* ,from_unixtime(p.create_time) as createtime,from_unixtime(p.lastmodify_time) as lastmodifytime,
pc.cat_id
 FROM `qkdb_product` as p left join qkdb_product_cat as pc 
on pc.product_id = p.product_id  
where p.product_id = '".$product_id."' ; ";
		
		$query = $this->DatabaseHandler->Query($sql);
		$productrow = $this->DatabaseHandler->GetRow($query);
		if(!$productrow)
		{
			$this->Messager('该产品不存在，可能已被删除！请重新确认后操作。', 'admin.php?mod=pl', 10);
		}
		
		//一级菜单
		$menu_level_1 = 'products';
		//三级菜单
		$menu_level_3 = 'productlist';
		
		//封装产品分类
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
				
				if($productrow['cat_id'] == $cat_row['cat_id'])
				{
					$catlevel1[$cat_row['cat_id']]['is_selected'] = 'selected';
				}
			}
			else if('2' == $cat_row['level'])
			{
				$catlevel2[$cat_row['cat_id']] = $cat_row;
				
				if($productrow['cat_id'] == $cat_row['cat_id'])
				{
					$catlevel2[$cat_row['cat_id']]['is_selected'] = 'selected';
				}
			}
			else if('3' == $cat_row['level'])
			{
				$catlevel3[$cat_row['cat_id']] = $cat_row;
				
				if($productrow['cat_id'] == $cat_row['cat_id'])
				{
					$catlevel3[$cat_row['cat_id']]['is_selected'] = 'selected';
				}
			}
			else if('4' == $cat_row['level'])
			{
				$catlevel4[$cat_row['cat_id']] = $cat_row;
			
				if($productrow['cat_id'] == $cat_row['cat_id'])
				{
					$catlevel4[$cat_row['cat_id']]['is_selected'] = 'selected';
				}
			}
		}
		
		//得到产品主图
		$main_img_arr = get_product_main_img_path($product_id);
		
		$productrow['detail_specialchar'] = htmlspecialchars($productrow['detail']);
		
		include(template('product_edit','admin'));
		
	}
	
	
	//编辑“产品对比”的属性
	function Comparekey()
	{
		$page_title="后台管理 - 产品对比属性编辑";
		//一级菜单
		$menu_level_1 = 'products';
		//三级菜单
		$menu_level_3 = 'comparekey';
		
		//读取数据库qkdb_product_compare_property
		$sql = "select * from qkdb_product_compare_property order by property_id ASC";
		$query = $this->DatabaseHandler->Query($sql);
		$property_name_list = array();
		while($property_row = $this->DatabaseHandler->GetRow($query))
		{
			//每个$property_row就是一个属性名称
			$property_name_list[$property_row['property_id']] = $property_row['property_name'];
			
		}
		
		include(template('product_compare_key','admin'));
	}
	
	
	//编辑一个产品它用于对比的值。
	function Edit_compare_value()
	{
		$page_title="后台管理 - 产品对比编辑";
		
		$product_id = getPG('pid');
		
		if(!$product_id || $product_id<=0)
		{
			$this->Messager('产品id不能为空！', 'admin.php?mod=pl', 10);
		}
		
		//判断产品id存不存在。
		$sql = "SELECT product_id, bn, name FROM `qkdb_product` where product_id = '".$product_id."' ; ";
		
		$query = $this->DatabaseHandler->Query($sql);
		$productrow = $this->DatabaseHandler->GetRow($query);
		if(!$productrow)
		{
			$this->Messager('该产品不存在，可能已被删除！请重新确认后操作。', 'admin.php?mod=pl', 10);
		}
		
		//一级菜单
		$menu_level_1 = 'products';
		//三级菜单
		$menu_level_3 = 'productlist';
		
		//读取属性名称表qkdb_product_compare_property
		$sql = "select * from qkdb_product_compare_property order by property_id ASC";
		$query = $this->DatabaseHandler->Query($sql);
		$property_name_list = array();
		while($onepropertyname = $this->DatabaseHandler->GetRow($query))
		{
			$property_name_list[$onepropertyname['property_id']] = $onepropertyname['property_name'];
		}
		
		//读取属性值表qkdb_product_compare_property_value
		$sql = "SELECT pv.*, p.property_name FROM `qkdb_product_compare_property_value` as pv ".
			   "inner join qkdb_product_compare_property as p on p.property_id = pv.property_id ".
			   "where pv.product_id = '".$product_id."'";
		$query = $this->DatabaseHandler->Query($sql);
		
		$property_value_list = array();
		while($onepropertyrow = $this->DatabaseHandler->GetRow($query))
		{
			$property_value_list[$onepropertyrow['property_id']] = $onepropertyrow;
		}
		
		include(template('product_compare_value_edit','admin'));
	}
	
	//搜索产品的关键字记录
	function Search_log_product()
	{
		$page_title="后台管理 - 搜索产品关键词列表";
		
		//一级菜单
		$menu_level_1 = 'products';
		//三级菜单
		$menu_level_3 = 'searchloglist';
		
		$countsql = "select count(id) as total_record from qkdb_searchlog_product ";
		$query = $this->DatabaseHandler->Query($countsql);
		extract($this->DatabaseHandler->GetRow($query));//得到变量total_record
		
		$topage = getPG('page');
		if(!$topage)
		{
			$topage = 1;
		}
		$per_page_num = 10;//每页条数。同步加载出的条数
		
		$page_arr = page_limit($total_record, $per_page_num, $topage, 'admin.php?mod=pl&act=search_log_product');
		
		$sql = "SELECT * FROM `qkdb_searchlog_product` order by id DESC ".$page_arr['limit'];
		$query = $this->DatabaseHandler->Query($sql);
		
		$log_info = array();
		while($row = $this->DatabaseHandler->GetRow($query))
		{
			
			$row['createtime'] = date("Y-m-d H:i:s",$row['time']);
				
			$log_info[$row['id']] = $row;
		}
		
		
		include(template('search_product_keyword_list','admin'));
		
		
	}
	
}
?>