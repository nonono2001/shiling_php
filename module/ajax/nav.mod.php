<?php

class ModuleObject extends MasterObject
{
	var $article_id;

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
			exit('请先登录。');
		}
		if(!IS_MEMBER_ADMIN || IS_MEMBER_ADMIN <= 0)//虽然登录了，但不是管理员
		{
			exit('只有管理员才可以操作。');
		}
		
		switch($this->Act)
		{
			case 'nav_type_click':
				$this->Nav_type_click();  //导航新增或编辑时，点击导航类型，会弹出二级对话框以供选择具体内容
				break;
				
			case 'save_nave':
				$this->Save_nave(); //添加或编辑一个导航，最后的保存。
				break;
				
			case 'delete_nav':
				$this->Delete_nav(); //删除导航
				break;
				
			case 'save_nav_paixu':
				$this->Save_nav_paixu(); //保存导航排序
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
	
	//函数的目的是弹出二级对话框
	function Nav_type_click()
	{
		$type = getPG('type');
		
		switch($type)
		{
			case 'product_category':
				$pagedata = $this->_get_product_category();
				$catlevel1 = $pagedata['catlevel1'];
				$catlevel2 = $pagedata['catlevel2'];
				$catlevel3 = $pagedata['catlevel3'];
				break;
				
			case 'product_detail':
				$topage = getPG('page');
				$result = $this->_get_product_list($topage);
				$page_arr = $result['page_arr'];
				$products_info = $result['products_info'];
				break;
				
			case 'article_category':
				$pagedata = $this->_get_article_category();
				$catlevel1 = $pagedata['catlevel1'];
				$catlevel2 = $pagedata['catlevel2'];
				$catlevel3 = $pagedata['catlevel3'];
				break;
				
			case 'article_detail':   //操作者选择的是文章详细，需要列出文章列表让其选择
				$topage = getPG('page');
				$result = $this->_get_article_list($topage);
				$page_arr = $result['page_arr'];
				$articles_info = $result['articles_info'];
				break;
				
			case 'custom_page':
				$topage = getPG('page');
				$result = $this->_get_custom_page_list($topage);
				$page_arr = $result['page_arr'];
				$custompages_info = $result['custompages_info'];
				break;
				
			case 'out_link':
				break;
				
			default:
				echo '导航类型出错，请刷新后重新选择！';
				exit();
				break;
		}
		
		include(template('dialog_nav_edit_second','admin'));
	}
	
	function Save_nave()
	{
		$optype = getPG('optype'); //new或者edit
		if($optype == 'new')
		{
			$p_nav_id = getPG('nav_id'); //父节点id，即新节点的父节点id。为0则表示要新建顶级节点。
		}
		elseif($optype == 'edit')
		{
			$nav_id = getPG('nav_id'); //即要修改的导航节点id。不可能为0。
			if(!$nav_id)
			{
				json_error('要修改的导航id为空或为0，错误！');
			}
		}
		else 
		{
			json_error('操作类型出错！');
			
		}
		$nav_name_pg = trim(getPG('nav_name')); //导航名称
		$nava_name = stripslashes($nav_name_pg);
		$nav_type_selected_pg = trim(getPG('nav_type_selected')); //导航类型
		$nav_type_selected = stripslashes($nav_type_selected_pg);
		$nav_detail_selected_pg = trim(getPG('nav_detail_selected')); //导航内容
		$nav_detail_selected = stripslashes($nav_detail_selected_pg);
		$open_style = trim(getPG('open_style')); //是否新窗口打开。new或self
		
		if(mb_strlen($nava_name, 'utf8') == 0) //导航名称1~10字
		{
			json_error('导航名称不能为空！');
		}
		else if(mb_strlen($nava_name, 'utf8') > 10) 
		{
			json_error('导航名称过长！');
		}
		
		if(!$nav_type_selected)
		{
			json_error('请选择导航类型！'); //导航类型不能为空
		}
		
		//导航内容在有些情况下可以为空
		$typearr = array('product_detail','article_detail','custom_page','out_link');
		if(in_array($nav_type_selected, $typearr)) //当项航类型是这些情况下，导航内容不能为空。
		{
			if(!$nav_detail_selected)
			{
				json_error('请正确选择导或填写航内容！');
			}
		}
		
		if($open_style != '_self' && $open_style != '_blank')
		{
			json_error('请选择是否新窗口打开！'); 
		}
		
		$link_url = '';
		if($nav_type_selected == 'homepage')
		{
			$link_url = 'index.php';
		}
		else if($nav_type_selected == 'product_category')
		{
			$link_url = 'index.php?mod=pl&pc='.$nav_detail_selected_pg; //$nav_detail_selected有可能为空。为空代表全部产品
		}
		else if($nav_type_selected == 'product_detail')
		{
			$link_url = 'index.php?mod=product&pid='.$nav_detail_selected_pg;//$nav_detail_selected不能为空
		}
		else if($nav_type_selected == 'article_category')
		{
			$link_url = 'index.php?mod=al&ac='.$nav_detail_selected_pg; //$nav_detail_selected有可能为空。为空代表全部文章
		}
		else if($nav_type_selected == 'article_detail')
		{
			$link_url = 'index.php?mod=article&aid='.$nav_detail_selected_pg;
		}
		else if($nav_type_selected == 'custom_page')
		{
			$link_url = 'index.php?mod=custompage&cpid='.$nav_detail_selected_pg;
		}
		else if($nav_type_selected == 'advise')
		{
			$link_url = 'index.php?mod=advise';
		}
		else if($nav_type_selected == 'friendlink')
		{
			$link_url = 'index.php?mod=friendlink';
		}
		else if($nav_type_selected == 'out_link')
		{
			$link_url = $nav_detail_selected_pg; //对于外部链接，$nav_detail_selected_pg是没用的。
			$nav_detail_selected_pg = $nav_detail_selected = 0;
		}
		else
		{
			json_error('导航类型出错！');
		}
		
		if($optype == 'new')
		{
			$p_navrow = '';
			//新建导航的层级（最多四级导航），即父节点层级+1。
			if(!$p_nav_id || $p_nav_id == 0) //新建的是顶级分类
			{
				$level = 1;
			}
			else 
			{
				$sql = "select level, full_id from qkdb_nav where nav_id = '".$p_nav_id."'";
				$query = $this->DatabaseHandler->Query($sql);
				$p_navrow = $this->DatabaseHandler->GetRow($query);
				if(!$p_navrow)
				{
					json_error('父节点不存在！');
				}
				$level = $p_navrow['level'] + 1;
			}
			
			//插入qkdb_nav表。
			$insert_data = array(
					'name' => $nav_name_pg,
					'nav_type' => $nav_type_selected_pg,
					'nav_content' => $nav_detail_selected_pg,
					'link_url' => $link_url,
					'open_type' => $open_style,
					'parent_id' => $p_nav_id,
					'level' => $level,
			);
			$newnav_id = $this->DatabaseHandler->insert('qkdb_nav', $insert_data,true);
			
			if($newnav_id)
			{
				if(!$p_nav_id || $p_nav_id == 0)
				{
					//添加的是顶级导航
					$new_full_id = ",".$newnav_id.",";
				}
				else
				{
// 					error_log(date('Y-m-d H:i:s ') . __CLASS__ . '::' . __FUNCTION__ . ' @ '.
// 							'$p_navrow: ' . var_export($p_navrow, 1) . "\r\n", 3, DATA_PATH."chutest/CHUTEST.log");
						
					$new_full_id = $p_navrow['full_id'].$newnav_id.",";//full_id是这种形式：,3,4,5,前后的逗号一定要保留
				}
				$update_data = array(
						'full_id' => $new_full_id,
						'paixu_num' => $newnav_id,
				);
				$this->DatabaseHandler->update('qkdb_nav', $update_data, "nav_id = '".$newnav_id."'");
			}
			else 
			{
				json_error('新增导航insert失败！');
			}
			
		}
		else if($optype == 'edit')
		{
			//编辑一个导航，只修改该导航的name、nav_type、nav_content、link_url、open_type
			$update_data = array(
					'name' => $nav_name_pg,
					'nav_type' => $nav_type_selected_pg,
					'nav_content' => $nav_detail_selected_pg,
					'link_url' => $link_url,
					'open_type' => $open_style,
			);
			$this->DatabaseHandler->update("qkdb_nav", $update_data, "nav_id = '".$nav_id."'");
		}
		
		json_result();
	}
	
	function Delete_nav()
	{
		$nav_id = getPG('nav_id');//要删除的导航id
		
		$sql = "select * from qkdb_nav where full_id like '%,".$nav_id.",%'";
		$query = $this->DatabaseHandler->Query($sql);//得到nav_id的导航以及它的子导航，这些都是需要删的。
		$navid_to_delete = array();//要删除的navid
		
		while($row = $this->DatabaseHandler->GetRow($query))
		{
			$navid_to_delete[$row['nav_id']] = $row['nav_id'];
		}
		if($navid_to_delete)
		{
			$navid_str = jimplode($navid_to_delete);//得到用逗号隔开的nav_id字符串。
			//删除qkdb_nav表，
			$this->DatabaseHandler->delete('qkdb_nav', "nav_id in (".$navid_str.")");
		}
		
		json_result();
	}
	
	function Save_nav_paixu()
	{
		$nav_paixu = getPG('nav_paixu');//前端传过来该变量是json格式。放到php变量中直接变成数组使用。
		//一般json字符串在php中需要json_decode，才能得到数组，而这里的前端传过来的不是json字符串。
		//前端传过来的是js的数组。在php中直接可用当成数组使用。
		//前端页面json字符串---------ajax---------->后端php，需要json_decocd后赋给php变量，才能当成php对象使用。
		//前端页面js数组-------------ajax---------->后端php，无需json_decode，直接赋给php变量当成数组使用。
		
		//$cat_paixu形如：
		/*array (
		 0 =>
				array (
						'cat_id' => '1',
						'paixu_num' => '1',
				),
				1 =>
				array (
						'cat_id' => '2',
						'paixu_num' => '1',
				),
				2 =>
				array (
						'cat_id' => '7',
						'paixu_num' => '1',
				),
				3 =>
				array (
						'cat_id' => '8',
						'paixu_num' => '2',
				),
				4 =>
				array (
						'cat_id' => '9',
						'paixu_num' => '3',
				),
				5 =>
				array (
						'cat_id' => '3',
						'paixu_num' => '2',
				),
				6 =>
				array (
						'cat_id' => '10',
						'paixu_num' => '1',
				),
				7 =>
				array (
						'cat_id' => '11',
						'paixu_num' => '2',
				),
				8 =>
				array (
						'cat_id' => '12',
						'paixu_num' => '3',
				),
				9 =>
				array (
						'cat_id' => '4',
		'paixu_num' => '2',
		),
		10 =>
		array (
		'cat_id' => '5',
		'paixu_num' => '1',
		),
		11 =>
		array (
		'cat_id' => '6',
		'paixu_num' => '2',
		),
		)
		*/
		//更新qkdb_nav表。
		foreach($nav_paixu as $val)
		{
			$sql = "UPDATE qkdb_nav SET paixu_num = '".$val['paixu_num']."' " .
					" WHERE nav_id = '".$val['nav_id']."'" ;
			$query = $this->DatabaseHandler->Query($sql);
		}
		
		json_result();
		
	}
	
	function _get_product_category()
	{
		//得到现已存在的产品分类。
		$sql = "select * from qkdb_category order by paixu_num ASC";
		$query = $this->DatabaseHandler->Query($sql);
		
		$catlevel1 = array();
		$catlevel2 = array();
		$catlevel3 = array();
		while($cat_row = $this->DatabaseHandler->GetRow($query))
		{
			//每个$cat_row就是一个产品分类，一级到三级都有可能。
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
		}
		$pagedata = array(
			'catlevel1' => $catlevel1,
			'catlevel2' => $catlevel2,
			'catlevel3' => $catlevel3,
		);
		return $pagedata;
	}
	
	function _get_article_category()
	{
		//得到现已存在的产品分类。
		$sql = "select * from qkdb_artcategory order by paixu_num ASC";
		$query = $this->DatabaseHandler->Query($sql);
		
		$catlevel1 = array();
		$catlevel2 = array();
		$catlevel3 = array();
		while($cat_row = $this->DatabaseHandler->GetRow($query))
		{
			//每个$cat_row就是一个产品分类，一级到三级都有可能。
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
		}
		$pagedata = array(
				'catlevel1' => $catlevel1,
				'catlevel2' => $catlevel2,
				'catlevel3' => $catlevel3,
		);
		return $pagedata;
	}
	
	function _get_article_list($topage)
	{
		$countsql = "select count(article_id) as total_record from qkdb_article ";
		$query = $this->DatabaseHandler->Query($countsql);
		extract($this->DatabaseHandler->GetRow($query));//得到变量total_record
		
		if(!$topage)
		{
			$topage = 1;
		}
		$per_page_num = 10;//每页产品条数。同步加载出的产品条数
		
		$page_arr = page_limit($total_record, $per_page_num, $topage, 'ajax.php?mod=nav&act=nav_type_click&type=article_detail');
		
		$sql = "SELECT a.* ,
c.name as catname
 FROM `qkdb_article` as a left join qkdb_article_cat as ac
on ac.article_id = a.article_id
left join qkdb_artcategory as c
on c.cat_id = ac.cat_id  order by a.article_id DESC ".$page_arr['limit'];
		$query = $this->DatabaseHandler->Query($sql);
		
		$articles_info = array();
		while($row = $this->DatabaseHandler->GetRow($query))
		{
			$articles_info[$row['article_id']] = $row;
		}
		
		$result['articles_info'] = $articles_info;
		$result['page_arr'] = $page_arr;
		
		return $result;
		
	}
	
	function _get_product_list($topage)
	{
		$countsql = "select count(product_id) as total_record from qkdb_product ";
		$query = $this->DatabaseHandler->Query($countsql);
		extract($this->DatabaseHandler->GetRow($query));//得到变量total_record
		
		$topage = getPG('page');
		if(!$topage)
		{
			$topage = 1;
		}
		$per_page_num = 10;//每页产品条数。同步加载出的产品条数
		
		$page_arr = page_limit($total_record, $per_page_num, $topage, 'ajax.php?mod=nav&act=nav_type_click&type=product_detail');
		
		//因为这是为导航选择一个产品，导航是前端店铺展示的，所以要过滤下架的产品
		$sql = "SELECT p.* ,
pc.cat_id, c.name as catname
 FROM `qkdb_product` as p left join qkdb_product_cat as pc
on pc.product_id = p.product_id
left join qkdb_category as c
on c.cat_id = pc.cat_id  where p.is_sale = '1' order by p.product_id DESC ".$page_arr['limit'];
		$query = $this->DatabaseHandler->Query($sql);
		
		$products_info = array();
		while($row = $this->DatabaseHandler->GetRow($query))
		{
			$products_info[$row['product_id']] = $row;
		}
		
		$result['products_info'] = $products_info;
		$result['page_arr'] = $page_arr;
		
		return $result;
	}
	
	
	function _get_custom_page_list($topage)
	{
		$countsql = "select count(custompage_id) as total_record from qkdb_custom_page ";
		$query = $this->DatabaseHandler->Query($countsql);
		extract($this->DatabaseHandler->GetRow($query));//得到变量total_record
		
		$topage = getPG('page');
		if(!$topage)
		{
			$topage = 1;
		}
		$per_page_num = 10;//每页条数。同步加载出的条数
		
		$page_arr = page_limit($total_record, $per_page_num, $topage, 'ajax.php?mod=nav&act=nav_type_click&type=custom_page');
		
		$sql = "SELECT cp.* 
 FROM `qkdb_custom_page` as cp order by cp.custompage_id DESC ".$page_arr['limit'];
		$query = $this->DatabaseHandler->Query($sql);
		
		$custompages_info = array();
		while($row = $this->DatabaseHandler->GetRow($query))
		{
			$custompages_info[$row['custompage_id']] = $row;
		}
		
		$result['custompages_info'] = $custompages_info;
		$result['page_arr'] = $page_arr;
		
		return $result;
	}
	
}
?>