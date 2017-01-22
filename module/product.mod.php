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
			case 'compare':
				$this->Compare();
				break;
			
				
			default:
				$this->Product_detail();
				break;
		}

	}
	
	
	
	//产品详情页
	function Product_detail()
	{
		$p_id = trim(getPG('pid'));//产品id
		
		if($p_id == 0 || !$p_id)
		{
			$this->Messager('产品id不能为空。',"index.php",5);
		}

		$sql = "select * from qkdb_product where product_id = '".$p_id."'";
		$query = $this->DatabaseHandler->Query($sql);
		$oneproduct = $this->DatabaseHandler->GetRow($query);
		
		if(!$oneproduct)
		{
			$this->Messager('您访问的产品不存在，可能已被删除，请重新选择。',"index.php",10);
		}
		
		//得到该产品是否上架。如果下架，不展示。
		if($oneproduct['is_sale'] == 0 || !$oneproduct['is_sale'])
		{
			//下架了，不展示
			$this->Messager('对不起，该产品已下架，无法访问。',"index.php",10);
		}
		//得到产品名称
		$oneproduct['name'] = htmlspecialchars($oneproduct['name']);
		//得到产品简介
		$oneproduct['introduction'] = htmlspecialchars($oneproduct['introduction']);
		//得到售价、市场价，已在$oneproduct中
				
		//得到产品主图（0~8张）。
		$main_img_arr = get_product_main_img_path($p_id);
		
		//得到产品详情，已在$oneproduct 中。
		
		//判断该产品是否有对比的属性值，如果有，则可以“参与对比”，产品详情页显示“参与对比”的按钮。否则不显示。
		$sql = "select id from qkdb_product_compare_property_value where product_id = '".$p_id."';";
		$query = $this->DatabaseHandler->Query($sql);
		$prow = $this->DatabaseHandler->GetRow($query);
		$is_show_comparebutton = 0;
		if($prow)
		{
			$is_show_comparebutton = 1;
		}
		//////////////////////////////////面包屑数据准备///////////////////////////////////////
		
		$Breadcrumb = $oneproduct['name'];
		//////////////////////////////////面包屑数据准备结束///////////////////////////////////////
		
		
		//得到该产品所属分类id，通过qkdb_product_cat表。
		$sql = "select * from qkdb_product_cat where product_id = '".$p_id."'";
		$query = $this->DatabaseHandler->Query($sql);
		$pcatrow = $this->DatabaseHandler->GetRow($query);
		
		$pcat_id = $pcatrow['cat_id'];//可能为空，如果为空，说明产品未分类
		///////////////////////左边框数据准备（得到所有一级分类 : 名称、id）/////////////////////////
		$pc_first_level = array();
		$sql = "SELECT * FROM  `qkdb_category` WHERE level = 1 order by paixu_num ASC";
		$query = $this->DatabaseHandler->Query($sql);
		while($onerow = $this->DatabaseHandler->GetRow($query))
		{
			$pc_first_level[$onerow['cat_id']]['name'] = $onerow['name']; // 分类名称
		}
		///////////////////////左边框数据准备结束////////////////////////////////////////////////////
		
		///////////////////////页头数据准备//////////////////////////////////////////////////////////
		$pagedatalogic = Load::logic('pagedata');
		$page_data = $pagedatalogic->PrepareViewData();//页头页脚数据准备
		///////////////////////页头数据准备结束//////////////////////////////////////////////////////
		
		$page_title = '产品详情';
		include(template('productdetail'));
		
	}
	
	
	//产品对比页
	function Compare()
	{
		///////////////////////页头数据准备//////////////////////////////////////////////////////////
		$pagedatalogic = Load::logic('pagedata');
		$page_data = $pagedatalogic->PrepareViewData();//页头页脚数据准备
		///////////////////////页头数据准备结束//////////////////////////////////////////////////////
		
		//读取cookie，获得最新的对比列表。
		$cpid = $_COOKIE['product_compare_list'];//该cookie存的就是参与产品对比的product_id，以英文逗号隔开。也可能为空。
		
		$cpid = trim($cpid,',');
		
		if(!$cpid)
		{
			//无产品id
			$product_baseinfo_compare = '';
			
		}
		else 
		{
			//有产品id，1~4个。
			//获取这些产品的基本信息。图片、名称、简介。
			$sql = "select * from qkdb_product where product_id in (".$cpid.")  order by field(product_id,".$cpid.");";
			$query = $this->DatabaseHandler->Query($sql);
			$product_baseinfo_compare = array();
			while($oneproduct = $this->DatabaseHandler->GetRow($query))
			{
				//$oneproduct就是一个产品的信息
				//得到产品名称
				$oneproduct['name'] = htmlspecialchars($oneproduct['name']);
				//得到产品简介
				$oneproduct['introduction'] = htmlspecialchars($oneproduct['introduction']);
				
				//得到产品主图（0~8张）。
				$main_img_arr = get_product_main_img_path($oneproduct['product_id']);
				
				$product_baseinfo_compare[$oneproduct['product_id']] = array(
						'pic' => $main_img_arr[0],
						'prod_name' => $oneproduct['name'],
						'prod_intro' => $oneproduct['introduction'],
				);
				
			}
			// 		$product_baseinfo_compare 形如： array(
			// 				'12' => array(
			// 						'pic' => 'attachment/prod_12/images/main_images/0.jpg',
			// 						'prod_name' => 'AA057VD01',
			// 						'prod_intro' => '此产品主要应用在仪器仪表上；日本原装进口',
			
			// 				), //key是产品id
			// 				'13' => array(
			// 						'pic' => 'attachment/prod_13/images/main_images/0.jpg',
			// 						'prod_name' => 'AA084VE01',
			// 						'prod_intro' => '阳光下可视液晶屏',
			
			// 				),
			// 				'45' => array(
			// 						'pic' => 'attachment/prod_45/images/main_images/0.jpg',
			// 						'prod_name' => 'AA084SB01',
			// 						'prod_intro' => '8.4寸LED背光宽温工业屏',
			
			// 				),
			// 				'22' => array(
			// 						'pic' => 'attachment/prod_22/images/main_images/0.jpg',
			// 						'prod_name' => 'AA043MA01',
			// 						'prod_intro' => '4.3寸800*480宽温LED背光工业屏',
			
			// 				),
			// 		);
			
			//获取这些产品的对比属性值。
			//先取出对比属性名称值对。qkdb_product_compare_property_value表
			$sql = "select * from qkdb_product_compare_property_value where product_id in (".$cpid.");";
			$query = $this->DatabaseHandler->Query($sql);
			
			$cpid_arr = explode(',', $cpid);
			$cpid_arr = array_filter($cpid_arr);//由产品id组成的数组
			
			$all_property_name_value = array();
			while($onepropertynamevalue = $this->DatabaseHandler->GetRow($query))
			{
				$all_property_name_value[$onepropertynamevalue['product_id']][$onepropertynamevalue['property_id']] = $onepropertynamevalue['property_value'];
			}
			
			//获取所有的产品对比属性名称
			$sql = "select * from qkdb_product_compare_property ;";
			$query = $this->DatabaseHandler->Query($sql);
			$all_compare_property_name = array();
			$comparelist = array();
			while($onepropertyname = $this->DatabaseHandler->GetRow($query))
			{
				$comparelist[$onepropertyname['property_id']] = array(
						'attr_name' => $onepropertyname['property_name'],
						
				);
				foreach($cpid_arr as $oneproduct_id)
				{
					$comparelist[$onepropertyname['property_id']]['attr_value'][$oneproduct_id] = $all_property_name_value[$oneproduct_id][$onepropertyname['property_id']];//可能是空。空代表该产品的该属性还没有填值。
				}
			}
			/*$comparelist 形如： array(
				'1' =>  array(
						'attr_name' => '分辨率',
						'attr_value' => array(
								'12' => '2000*3000', //key是产品id
								'13' => '3000*4000',
								'45' => '2000*3000',
								'22' => '900*800',
						)
				), //key是属性id
				'22' => array(
						'attr_name' => '电压',
						'attr_value' => array(
								'12' => '12V', //key是产品id
								'13' => '120V',
								'45' => '36V',
								'22' => '90V',
						)
				),
				'43' => array(
						'attr_name' => 'CPU频率',
						'attr_value' => array(
								'12' => '1.2G', //key是产品id
								'13' => '2.0G',
								'45' => '2.2G',
								'22' => '1.9G',
						)
				),
		);
			 * 
			 */
			
			
			
		}
		
		$page_title = '产品对比';
		include(template('product_compare'));
		
	}
	
	
	
	
	
	
	
	
}

?>