<?php

class ModuleObject extends MasterObject
{
	var $new_product_id;

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
			case 'save_cat_paixu':
				$this->Save_cat_paixu();  //保存产品分类排序
				break;
			
			case 'dialog_cat_edit':
				$this->Dialog_cat_edit();  //产品分类编辑对话框
				break;
				
			case 'dialog_cat_new':
				$this->Dialog_cat_new();  //产品分类新增对话框
				break;
				
			case 'do_save_cat':
				$this->Do_save_cat();  //对产品分类编辑或新增的保存
				break;
				
			case 'delete_cat':
				$this->Delete_cat();  //删除一个产品分类
				break;
				
			case 'do_add_product':  //新增一个产品
				$this->Do_add_product();
				break;
				
			case 'do_delete_one_main_img':  //删除一个产品的一个主图
				$this->Do_delete_one_main_img();
				break;
				
			case 'do_edit_product':  //编辑一个产品
				$this->Do_edit_product();
				break;
				
			case 'do_delete_products': //删除一个或多个产品
				$this->Do_delete_products();
				break;
				
			case 'save_product_paixu': //保存产品排序
				$this->Save_product_paixu();
				break;
				
			case 'do_save_compare_property': //保存产品对比的属性名
				$this->Do_save_compare_property();
				break;
				
			case 'do_save_compare_property_value': //保存一个产品的产品对比的属性值
				$this->Do_save_compare_property_value();
				break;
				
			case 'do_delete_searchlog': //删除一条搜索产品的关键词记录
				$this->Do_delete_searchlog();
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
	
	//保存产品分类排序
	function Save_cat_paixu()
	{
		$cat_paixu = getPG('cat_paixu');//前端传过来该变量是json格式。放到php变量中直接变成数组使用。
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
		//更新qkdb_category表。
		foreach($cat_paixu as $val)
		{
			$sql = "UPDATE qkdb_category SET paixu_num = '".$val['paixu_num']."' " .
				   " WHERE cat_id = '".$val['cat_id']."'" ;
			$query = $this->DatabaseHandler->Query($sql);
		}
		
		json_result();
	}
	
	function Dialog_cat_edit()
	{
		$cat_id = getPG('cat_id');//该值为要编辑的分类id。
		
		$sql = "select * from qkdb_category where cat_id = '".$cat_id."'";
		$query = $this->DatabaseHandler->Query($sql);
		$catrow = $this->DatabaseHandler->GetRow($query);
		if(!$catrow)
		{
			//说明$cat_id错误。
			echo '<div style="float:left;width:100%;height:100px;line-height:100px;margin-top:20px;font-family:microsoft yahei;text-align:center;font-size:18px;">该分类不存在，无法编辑！请重新选择。</div>';
			return;
		}
		
		$sql2 = "select * from qkdb_category where cat_id = '".$catrow['parent_id']."'";
		$query2 = $this->DatabaseHandler->Query($sql2);
		$parent_catrow = $this->DatabaseHandler->GetRow($query2);
		
		$optype = 'edit';//用于区别是新增还是编辑
		$catname_tobe_edit = $catrow['name'];
		$catname_parent = $parent_catrow['name'];
		include(template('dialog_cat_edit','admin'));
	}
	
	function Dialog_cat_new()
	{
		$p_cat_id = getPG('p_cat_id');//该值为父分类id，新增的分类为它的子分类。
		
		$sql = "select * from qkdb_category where cat_id = '".$p_cat_id."'";
		$query = $this->DatabaseHandler->Query($sql);
		$pcatrow = $this->DatabaseHandler->GetRow($query);
		if(!$pcatrow)
		{
			if($p_cat_id != 0)
			{
				//说明$cat_id错误。
				echo '<div style="float:left;width:100%;height:100px;line-height:100px;margin-top:20px;font-family:microsoft yahei;text-align:center;font-size:18px;">该分类不存在，无法添加新分类！请重新选择。</div>';
				return;
			}
		}
		
		$optype = 'new';//用于区别是新增还是编辑

		$catname_parent = $pcatrow['name'];
		include(template('dialog_cat_edit','admin'));
		
	}
	
	//产品分类编辑或新增后保存。
	function Do_save_cat()
	{
		$optype = getPG('optype');
		if($optype == 'edit')
		{
			$cat_id = getPG('cat_id');//要编辑的cat_id
			$cat_name = trim(getPG('cat_name'));//可能有反斜杠的。
			$cat_name_len = mb_strlen(stripslashes($cat_name),'utf8');//中文、英文、数字、符号，都算一个字符。并不是中文算2个或3个。
			if($cat_name_len<1 || $cat_name_len>30)
			{
				json_error('对不起，分类名称长度出错。');
			}
			
			//该cat_id可能不存在。
			$sql = "select * from qkdb_category where cat_id = '".$cat_id."'";
			$query = $this->DatabaseHandler->Query($sql);
			$catrow = $this->DatabaseHandler->GetRow($query);
			if(!$catrow)
			{
				json_error('对不起，分类不存在，请重新选择。');
			}
			
			$sql = "UPDATE qkdb_category set name = '".$cat_name."' where cat_id = '".$cat_id."' ";
			$res = $this->DatabaseHandler->Query($sql);
			if($res)
			{
				json_result();
			}
			else
			{
				json_error('更新qkdb_category表失败！');
			}
		}
		else if($optype == 'new')
		{
			$p_cat_id = getPG('cat_id');//父分类cat_id，往它下面新添加子分类
			$cat_name = trim(getPG('cat_name'));//可能有反斜杠的。
			$cat_name_len = mb_strlen(stripslashes($cat_name),'utf8');//中文、英文、数字、符号，都算一个字符。并不是中文算2个或3个。
			if($cat_name_len<1 || $cat_name_len>30)
			{
				json_error('对不起，分类名称长度出错。');
			}
				
			//该cat_id可能不存在。
			$sql = "select * from qkdb_category where cat_id = '".$p_cat_id."'";
			$query = $this->DatabaseHandler->Query($sql);
			$pcatrow = $this->DatabaseHandler->GetRow($query);
			if(!$pcatrow)
			{
				if($p_cat_id == 0)
				{
					//$p_cat_id为0，就添加一个顶级分类
					$new_level = 1;
				}
				else 
				{
					json_error('对不起，该分类不存在，请重新选择！');
					exit();
				}
				
			}
			else 
			{
				//新增的分类，它的level是多少？是父亲的下一级。
				$new_level = $pcatrow['level']+1;
			}
			
			$sql2 = "SELECT MAX( paixu_num ) as maxpaixu FROM  `qkdb_category` WHERE parent_id =  '".$p_cat_id."'";
			$query2 = $this->DatabaseHandler->Query($sql2);
			$row = $this->DatabaseHandler->GetRow($query2);//$row[maxpaixu]就是孩子分类中最大的排序号
			$new_paixu_num = $row['maxpaixu']+1;
			
			$insertdata = array(
					'name' => $cat_name,
					'level' => $new_level,
					'parent_id' => $p_cat_id,
					'paixu_num' => $new_paixu_num,
			);
			$res = $this->DatabaseHandler->insert('qkdb_category', $insertdata, true);
			
			if($res)
			{
				//$res就是新插入记录的id
				if($p_cat_id == 0)
				{
					//添加的是顶级分类,$pcatrow为空
					$new_full_id = ",".$res.",";
				
				}
				else
				{
					$new_full_id = $pcatrow['full_id'].$res.",";//full_id是这种形式：,3,4,5,前后的逗号一定要保留
				}
					
				//对刚才新生成的分类，update它的full_id
				$update_data = array(
					'full_id' => $new_full_id,
				);
				$this->DatabaseHandler->update('qkdb_category', $update_data, "cat_id = '".$res."'");
				
				json_result();
			}
			else
			{
				json_error('新增分类失败！');
			}
		}
		
	}
	
	//删除一个产品分类
	function Delete_cat()
	{
		$cat_id = getPG('cat_id'); //要删除的catid
		
		$sql = "select * from qkdb_category where full_id like '%,".$cat_id.",%'";
		$query = $this->DatabaseHandler->Query($sql);//得到cat_id的分类以及它的子分类，这些都是需要删的。
		$catid_to_delete = array();//要删除的catid
		while($row = $this->DatabaseHandler->GetRow($query))
		{
			$catid_to_delete[$row['cat_id']] = $row['cat_id'];
		}
		if($catid_to_delete)
		{
			$catid_str = jimplode($catid_to_delete);//得到用逗号隔开的catid字符串。
			//先删除qkdb_category表，
			$this->DatabaseHandler->delete('qkdb_category', "cat_id in (".$catid_str.")");
			
			//再删除qkdb_product_cat表
			$this->DatabaseHandler->delete('qkdb_product_cat', "cat_id in (".$catid_str.")");
		}
		
		json_result();
	}
	
	
	//添加一个产品（新增产品）
	function Do_add_product()
	{
		/*$_POST过来的内如例子如下：
		 * $_POST: array (
  'product_bn' => 'abc',
  'product_name' => '网球',
  'product_cat' => '运动',
  'product_intro' => '林先生',
  'deal_price' => '10.90',
  'market_price' => '30',
  'cost_price' => '5',
  'mark' => '少林牌',
  'is_sale' => 'n',
  'product_detail' => '详情',
  'buy_url' => 'http://www.baidu.com',
)*/
		/*上传的图片信息在$_FILES，最多8张。
		 $_FILES: array (
  'p_img' => 
  array (
    'name' => 
    array (
      0 => '6c224f4a20a44623f3a4afc79a22720e0cf3d72d.jpg',
      1 => '',
      2 => '',
      3 => '50da81cb39dbb6fdbaa121b10b24ab18962b37ab.jpg',
      4 => '',
      5 => '',
      6 => '',
      7 => '23305037.JPG',
    ),
    'type' => 
    array (
      0 => 'image/jpeg',
      1 => '',
      2 => '',
      3 => 'image/jpeg',
      4 => '',
      5 => '',
      6 => '',
      7 => 'image/jpeg',
    ),
    'tmp_name' => 
    array (
      0 => 'C:\\Windows\\Temp\\phpE638.tmp',
      1 => '',
      2 => '',
      3 => 'C:\\Windows\\Temp\\phpE63B.tmp',
      4 => '',
      5 => '',
      6 => '',
      7 => 'C:\\Windows\\Temp\\phpE64F.tmp',
    ),
    'error' => 
    array (
      0 => 0,
      1 => 4,
      2 => 4,
      3 => 0,
      4 => 4,
      5 => 4,
      6 => 4,
      7 => 0,
    ),
    'size' => 
    array (
      0 => 248835,
      1 => 0,
      2 => 0,
      3 => 53045,
      4 => 0,
      5 => 0,
      6 => 0,
      7 => 554850,
    ),
  ),
) */
		//检查货号长度及格式。
		$product_bn_gpc = trim(getPG('product_bn'));//有可能带有反斜杠的。该值有可能为空
		if($product_bn_gpc)//传过来的product_bn不为空，不为0
		{
			$product_bn = stripslashes($product_bn_gpc);//所见即所得。
			$product_bn_len = mb_strlen($product_bn,'utf8');
			if($product_bn_len<1 || $product_bn_len>10)//检查长度
			{
				echo '<script type="text/javascript">';
				echo 'window.parent.afterupload("err","货号长度出错！");  ';
				echo '</script>';
				exit();
			}
			
			if(!ereg("^[0-9a-zA-Z_]{1,}$",$product_bn))//检查格式，数字、字母、下划线
			{
				echo '<script type="text/javascript">';
				echo 'window.parent.afterupload("err","货号格式出错！");  ';
				echo '</script>';
				exit();
			}
			
			//判断货号是否已被占用
			$sql = "select * from qkdb_product where bn = '".$product_bn_gpc."'";
			$query = $this->DatabaseHandler->Query($sql);
			
			if($this->DatabaseHandler->GetRow($query))
			{
				//$product_bn_gpc该货号已被占用。
				echo '<script type="text/javascript">';
				echo 'window.parent.afterupload("err","货号已被占用，请重填！");  ';
				echo '</script>';
				exit();
			}
			
		}
		
		
		//检查产品名称长度。产品名称的格式不用检查，任何字符都可。
		$product_name_gpc = trim(getPG('product_name'));
		if(mb_strlen($product_name_gpc,'utf8') > 0)//不用if($product_name_gpc)这种判断是因为$product_name_gpc为'0'的话，if为假。
		{
			$product_name = stripslashes($product_name_gpc);//所见即所得。
			$product_name_len = mb_strlen($product_name,'utf8');
			if($product_name_len<1 || $product_name_len>50)
			{
				echo '<script type="text/javascript">';
				echo 'window.parent.afterupload("err","产品名称长度出错！");  ';
				echo '</script>';
				exit();
			}
		}
		else //产品名称不能为空
		{
			echo '<script type="text/javascript">';
			echo 'window.parent.afterupload("err","产品名称不能为空！");  ';
			echo '</script>';
			exit();
		}
		
		//检查分类存不存在。用户可能选择未分类，即分类id为空。
		$product_cat_id = getPG('product_cat');
		if($product_cat_id)
		{
			$sql = "select * from qkdb_category where cat_id = '".$product_cat_id."'";
			$query = $this->DatabaseHandler->Query($sql);
			
			if(!$this->DatabaseHandler->GetRow($query))
			{
				echo '<script type="text/javascript">';
				echo 'window.parent.afterupload("err","产品分类不存在！");  ';
				echo '</script>';
				exit();
			}
		}
		
		//检查简介长度。简介可以为空，最多200字。
		$product_intro_gpc = trim(getPG('product_intro'));
		if($product_intro_gpc)
		{
			$product_intro = stripslashes($product_intro_gpc);
			$product_intro_len = mb_strlen($product_intro,'utf8');
			if($product_intro_len<1 || $product_intro_len>200)//检查长度
			{
				echo '<script type="text/javascript">';
				echo 'window.parent.afterupload("err","简介长度出错！");  ';
				echo '</script>';
				exit();
			}
		}
		
		
		//检查售价、市场价、成本价的格式。这三个价，可以为空。为空入库后就是0
		$deal_price = trim(getPG('deal_price'));
		if($deal_price<0)
		{
			echo '<script type="text/javascript">';
			echo 'window.parent.afterupload("err","售价不能是负数哦！");  ';
			echo '</script>';
			exit();
		}
		$market_price = trim(getPG('market_price'));
		
		$cost_price = trim(getPG('cost_price'));
		
		//检查备注的长度。可以为空，最多80字。
		$mark_gpc = trim(getPG('mark'));
		if($mark_gpc)
		{
			$mark = stripslashes($mark_gpc);//所见即所得。
			$mark_len = mb_strlen($mark,'utf8');
			if($mark_len<1 || $mark_len>80)
			{
				echo '<script type="text/javascript">';
				echo 'window.parent.afterupload("err","备注长度出错！");  ';
				echo '</script>';
				exit();
			}
		}
		
		//是否上架，0-不上架，1-上架
		$is_sale = getPG('is_sale');
		if($is_sale != 0 && $is_sale != 1)
		{
			echo '<script type="text/javascript">';
			echo 'window.parent.afterupload("err","是否上架出错！");  ';
			echo '</script>';
			exit();
		}
		
		
		//检查产品详情，可以为空，详情是富文本，没有字数限制。先不插入数据库表，得到产品id以后再update进去。
		$product_detail_gpc = getPG('product_detail');//富文本可能头尾有空格，故意的，就不trim了
		
		
		//检查购买链接。可以为空，最多280字
		$buy_url_gpc = trim(getPG('buy_url'));
		if($buy_url_gpc)
		{
			$buy_url = stripslashes($buy_url_gpc);
			$buy_url_len = mb_strlen($buy_url,'utf8');
			if($buy_url_len<1 || $buy_url_len>280)//检查长度
			{
				echo '<script type="text/javascript">';
				echo 'window.parent.afterupload("err","购买链接长度出错！");  ';
				echo '</script>';
				exit();
			}
		}
		
		//检查上传的产品主图，大小、格式，是否正确。
		$inputname = 'p_img';
		$ok_upload_img = array();//用于保存检查过关的上传图片序号。
		for($i=0;$i<8;$i++)
		{
			$files_error_code = $_FILES[$inputname]['error'][$i];
			//error code等于4，代表没有选择文件。
			if($files_error_code != '4')//代表肯定是上传图片了
			{
				if($files_error_code>0)
				{
					echo '<script type="text/javascript">';
					echo 'window.parent.afterupload("err","产品主图'.($i+1).'上传失败！");  ';
					echo '</script>';
					exit();
				}
				else //error code为0 ，代表上传成功。
				{
					//检查上传图片的大小，不能超出5M。
					$maxfilesize = 5*1024*1024;//5M换成字节。
					if($_FILES[$inputname]["size"][$i] > $maxfilesize)
					{
						echo '<script type="text/javascript">';
						echo 'window.parent.afterupload("err","图片'.($i+1).'大小不能超过5M，请重新上传。"); ';
						echo '</script>';
						exit();
					}
					
					//检查上传图片的格式，jpg、png、gif。
					$source_info = getimagesize($_FILES[$inputname]["tmp_name"][$i]);
					if($source_info[2] != 1 && $source_info[2] != 2 && $source_info[2] != 3)//1代表gif，2代表jpg，3代表png
					{
						echo '<script type="text/javascript">';
						echo 'window.parent.afterupload("err","图片格式出错，请重新选择图片。"); ';
						echo '</script>';
						exit();
					}
					
					//能走到这里，说明通过了各项检查。
					$ok_upload_img[] = $i;
				}
			}
		}
		
		//以上都通过了，插入数据库表，得到产品id。
		$insert_data = array(
				'bn' => $product_bn_gpc,//货号，可能为空
				'name' => $product_name_gpc,//产品名称，不能为空
				'deal_price' => $deal_price,//售价或成交价，可能为空
				'market_price' => $market_price,//市场价，可能为空
				'cost' => $cost_price,//成本价，可能为空
				'create_time' =>TIMESTAMP, //产品创建时间
				'lastmodify_time' => TIMESTAMP, //最后更新时间
				'mark' => $mark_gpc, //备注，可能为空
				'is_sale' => $is_sale, //0或1，1代表上架，0代表不上架。
				'introduction' => $product_intro_gpc, //简介，可以为空
				'buy_url' => $buy_url_gpc, //购买链接，可能为空
		);
		
		$new_product_id = $this->DatabaseHandler->insert('qkdb_product', $insert_data, true);
		
		if(!$new_product_id || $new_product_id<=0)
		{
			echo '<script type="text/javascript">';
			echo 'window.parent.afterupload("err","新产品创建失败，请刷新后再试。"); ';
			echo '</script>';
			exit();
		}
		
		//得到产品id以后，
		$update_data = array();
		//如果货号为空，则用产品id当作货号，update到产品记录里
		//如果货号已被占用，则取该id与一个3位随机数之和。
		if(!$product_bn_gpc)
		{
			$sql = "select * from qkdb_product where bn = '".$new_product_id."'";
			$query = $this->DatabaseHandler->Query($sql);
				
			if(!$this->DatabaseHandler->GetRow($query))
			{
				//新增的$new_product_id，没有被别的产品占用当作bn。所以这个$new_product_id可以当作bn使用。
				$update_data['bn'] = $new_product_id;
			}
			else
			{
				//$new_product_id，已被别的产品当作bn使用。$new_product_id就不能当作bn使用了。
				//需要将$new_product_id与一个随机的3位数相加，得到的和，当作bn。如果该bn也已被占用，只能创建产品失败
				$new_bn = $new_product_id+rand(100, 999);
				$sql = "select * from qkdb_product where bn = '".$new_bn."'";
				$query = $this->DatabaseHandler->Query($sql);
				
				if(!$this->DatabaseHandler->GetRow($query))
				{
					//新增的$new_product_id加一个随机三位数之和，该和$new_bn没有被别的产品占用当作bn。$new_bn可以当作bn使用。
					$update_data['bn'] = $new_bn;
				}
				else {
					//$new_bn也被已被别的产品占用，创建产品失败。删除已创建的产品记录。
					$this->DatabaseHandler->delete('qkdb_product', "product_id = '".$new_product_id."'");
					
					echo '<script type="text/javascript">';
					echo 'window.parent.afterupload("err","自动分配货号失败，新产品创建失败，请手动填写货号。"); ';
					echo '</script>';
					exit();
				}
			}
		}
		
		//排序号，用产品id给update
		$update_data['paixu_num'] = $new_product_id;
		
		
		//把产品主图片移到attachment文件夹中。
		$pimage_path = 'attachment/prod_'.$new_product_id.'/images/main_images/';
		
		if(!is_dir($pimage_path))
		{
			mkdir($pimage_path,0777,true);
		}
		if(!is_dir($pimage_path))
		{
			$this->DatabaseHandler->delete('qkdb_product', "product_id = '".$new_product_id."'");
			echo '<script type="text/javascript">';
			echo 'window.parent.afterupload("err","产品主图片目录创建失败，请重试！"); ';
			echo '</script>';
			exit();
		}
		//$ok_upload_img里保存的是检查合格的主图编号:0~7。也可能为空
		foreach($ok_upload_img as $oneimgbn)
		{
			//获得文件扩展名
			$temp_arr = explode(".", $_FILES[$inputname]["name"][$oneimgbn]);
			$file_ext = array_pop($temp_arr);
			$file_ext = trim($file_ext);
			$file_ext = strtolower($file_ext);
			
			$image_full_name = $pimage_path . $oneimgbn . '.' . $file_ext;
			
			move_uploaded_file($_FILES[$inputname]["tmp_name"][$oneimgbn],$image_full_name);
		}
		
		
		
		//转换产品详情中的img标签，并把产品详情中的图片移到attachment文件夹中。
		$this->new_product_id = $new_product_id;//函数_image_rename中会用到$this->new_product_id
		
		//参考http://www.jb51.net/article/55446.htm
		//http://www.zuimoban.com/php/php/1572.html
		//http://www.oschina.net/code/snippet_583625_20448，说明了正则表达式中如何匹配反斜杠。要用四个反斜杠
		$new_product_detail = preg_replace_callback(
				'/(<\s*img\s+src\s*=\s*\\\\")(\/templates\/admin\/js\/kindeditor-4\.1\.7\/attached\/image\/\d+\/)(.+?\\\\")/i',
				array($this, '_image_rename'),
				$product_detail_gpc);
		//下面这种用法，是只替换，不走函数
		//$product_detail_new = preg_replace('/(<\s*img\s+src\s*=\s*")(\/templates\/admin\/js\/kindeditor-4\.1\.7\/attached\/image\/\d+\/)(.+?")/i',"\${1}/prod_123/\${3}",$product_detail);
		
		$update_data['detail'] = $new_product_detail;
		
		
		//更新产品bn、detail、排序num
		$this->DatabaseHandler->update('qkdb_product', $update_data, "product_id = '".$new_product_id."'");
		
		
		//插入产品分类表。可能是“未分类”，未分类则$product_cat_id为空。
		if($product_cat_id && $product_cat_id>0)
		{
			$insert_data = array(
					'product_id' => $new_product_id,
					'cat_id' => $product_cat_id,
			);
			if(!$this->DatabaseHandler->insert('qkdb_product_cat', $insert_data))
			{
				//插入分类表失败
				$this->DatabaseHandler->delete('qkdb_product', "product_id = '".$new_product_id."'");
				deldir("attachment/prod_".$this->new_product_id."/");//删除该产品对应的附件目录。
					
				echo '<script type="text/javascript">';
				echo 'window.parent.afterupload("err","插入产品分类表失败，请重试！"); ';
				echo '</script>';
				exit();
			}
		}
		
		//新建一个产品成功。
	    echo '<script type="text/javascript">';
		echo 'window.parent.afterupload("succ","");  ';
		echo '</script>';
		exit();
		
		
	}
	
	//内部函数，对于提交的富文本时处理里面的img路径
	//preg_replace_callback函数正则匹配，每次匹配都会走一遍这个函数。
	//替换img的src，并将kindeditor组件中的图片，拷贝到产品相对应的图片目录下。
	function _image_rename($matches)
	{
		
		/*$matches形如：
		 * $matches: array (
  0 => '<img src=\"/templates/admin/js/kindeditor-4.1.7/attached/image/20150214/20150214112715_11186.jpg\"',
  1 => '<img src=\"',
  2 => '/templates/admin/js/kindeditor-4.1.7/attached/image/20150214/',
  3 => '20150214112715_11186.jpg\"',
)
		 */
		foreach($matches as $i => $onematch)
		{
			$matches[$i] = stripslashes($onematch);
		}
		$temp_image_name = rtrim($matches[3], '"');//去掉最右边的双引号
		$old_imge_path_abs = ROOT_PATH.".".$matches[2].$temp_image_name; //老的图片绝对路径
		$new_image_path_rel = "/attachment/prod_".$this->new_product_id."/images/detail_images/"; //新的图片相对路径
		$new_image_path_abs = ROOT_PATH.".".$new_image_path_rel; //新的图片绝对路径
		//创建文件夹
		if(!is_dir($new_image_path_abs)) //文件夹不存在，则创建
		{
			mkdir($new_image_path_abs,0777,true);
		}
		
		$new_image_path_abs = $new_image_path_abs.$temp_image_name;
		rename($old_imge_path_abs,$new_image_path_abs);//rename相当于剪切，rename函数不能覆盖同名文件。
		
		return addslashes($matches[1].$new_image_path_rel.$temp_image_name.'"');
	}
	
	
	//删除产品的一张主图。
	function Do_delete_one_main_img()
	{
		$product_id = getPG('product_id');//产品id
		$img_bn = getPG('img_bn');//主图片的编号，0~7.
		//删除主图，只需删除物理图片即可。数据库不用动，因为主图并没有在数据库中记录。
		$img_arr = get_product_main_img_path($product_id);
		if(!$img_arr)
		{
			//说明该产品没有任何主图片，相当于删除成功。
			json_result();
		}
		
		if( is_file( $img_arr[$img_bn] ) )
		{
			//说明要删除的这张主图存在。
			if(!unlink ($img_arr[$img_bn]))
			{
				json_error('对不起，删除主图片失败。');
			}
			else 
			{
				json_result();
			}
		}
		else 
		{
			//说明要删除的这张主图不存在。相当于删除成功。
			json_result();
		}
	}
	
	
	//保存对于产品的修改，也就是保存编辑。
	//跟Do_add_product函数类似。
	function Do_edit_product()
	{
		/*$_POST过来的内如例子如下：
		 * $_POST: array (
		 		'product_bn' => 'abc',
		 		'product_name' => '网球',
		 		'product_cat' => '运动',
		 		'product_intro' => '林先生',
		 		'deal_price' => '10.90',
		 		'market_price' => '30',
		 		'cost_price' => '5',
		 		'mark' => '少林牌',
		 		'is_sale' => 'n',
		 		'product_detail' => '详情',
		 		'buy_url' => 'http://www.baidu.com',
		 )*/
		/*上传的图片信息在$_FILES，最多8张。
		 $_FILES: array (
		 		'p_img' =>
		 		array (
		 				'name' =>
		 				array (
		 						0 => '6c224f4a20a44623f3a4afc79a22720e0cf3d72d.jpg',
		 						1 => '',
		 						2 => '',
		 						3 => '50da81cb39dbb6fdbaa121b10b24ab18962b37ab.jpg',
		 						4 => '',
		 						5 => '',
		 						6 => '',
		 						7 => '23305037.JPG',
		 				),
		 				'type' =>
		 				array (
		 						0 => 'image/jpeg',
		 						1 => '',
		 						2 => '',
		 						3 => 'image/jpeg',
		 						4 => '',
		 						5 => '',
		 						6 => '',
		 						7 => 'image/jpeg',
		 				),
		 				'tmp_name' =>
		 				array (
		 						0 => 'C:\\Windows\\Temp\\phpE638.tmp',
		 						1 => '',
		 						2 => '',
		 						3 => 'C:\\Windows\\Temp\\phpE63B.tmp',
		 						4 => '',
		 						5 => '',
		 						6 => '',
		 						7 => 'C:\\Windows\\Temp\\phpE64F.tmp',
		 				),
		 				'error' =>
		 				array (
		 						0 => 0,
		 						1 => 4,
		 						2 => 4,
		 						3 => 0,
		 						4 => 4,
		 						5 => 4,
		 						6 => 4,
		 						7 => 0,
		 				),
		 				'size' =>
		 				array (
		 						0 => 248835,
		 						1 => 0,
		 						2 => 0,
		 						3 => 53045,
		 						4 => 0,
		 						5 => 0,
		 						6 => 0,
		 						7 => 554850,
		 				),
		 		),
		 ) */
		
		//判断要修改的产品id，存不存在。
		$product_id = getPG('pid');
		$sql = "select product_id from qkdb_product where product_id = '".$product_id."'";
		$query = $this->DatabaseHandler->Query($sql);
		$prow = $this->DatabaseHandler->GetRow($query);
		if(!$prow)
		{
			echo '<script type="text/javascript">';
			echo 'window.parent.afterupload("err","要编辑的产品不存在，可能已被删除，请刷新产品列表后再试！");  ';
			echo '</script>';
			exit();
		}
		
		//检查货号长度及格式。
		$product_bn_gpc = trim(getPG('product_bn'));//有可能带有反斜杠的。该值有可能为空
		if($product_bn_gpc)//传过来的product_bn不为空，不为0
		{
			$product_bn = stripslashes($product_bn_gpc);//所见即所得。
			$product_bn_len = mb_strlen($product_bn,'utf8');
			if($product_bn_len<1 || $product_bn_len>10)//检查长度
			{
				echo '<script type="text/javascript">';
				echo 'window.parent.afterupload("err","货号长度出错！");  ';
				echo '</script>';
				exit();
			}
				
			if(!ereg("^[0-9a-zA-Z_]{1,}$",$product_bn))//检查格式，数字、字母、下划线
			{
				echo '<script type="text/javascript">';
				echo 'window.parent.afterupload("err","货号格式出错！");  ';
				echo '</script>';
				exit();
			}
				
			//判断货号是否已被占用，要排除自己
			$sql = "select * from qkdb_product where bn = '".$product_bn_gpc."' and product_id != '".$product_id."'";
			$query = $this->DatabaseHandler->Query($sql);
				
			if($this->DatabaseHandler->GetRow($query))
			{
				//$product_bn_gpc该货号已被占用。
				echo '<script type="text/javascript">';
				echo 'window.parent.afterupload("err","货号已被占用，请重填！");  ';
				echo '</script>';
				exit();
			}
				
		}
		
		
		//检查产品名称长度。产品名称的格式不用检查，任何字符都可。
		$product_name_gpc = trim(getPG('product_name'));
		if(mb_strlen($product_name_gpc,'utf8') > 0)//不用if($product_name_gpc)这种判断是因为$product_name_gpc为'0'的话，if为假。
		{
			$product_name = stripslashes($product_name_gpc);//所见即所得。
			$product_name_len = mb_strlen($product_name,'utf8');
			if($product_name_len<1 || $product_name_len>50)
			{
				echo '<script type="text/javascript">';
				echo 'window.parent.afterupload("err","产品名称长度出错！");  ';
				echo '</script>';
				exit();
			}
		}
		else //产品名称不能为空
		{
			echo '<script type="text/javascript">';
			echo 'window.parent.afterupload("err","产品名称不能为空！");  ';
			echo '</script>';
			exit();
		}
		
		//检查分类存不存在。用户可能选择未分类，即分类id为空。
		$product_cat_id = getPG('product_cat');
		if($product_cat_id)
		{
			$sql = "select * from qkdb_category where cat_id = '".$product_cat_id."'";
			$query = $this->DatabaseHandler->Query($sql);
				
			if(!$this->DatabaseHandler->GetRow($query))
			{
				echo '<script type="text/javascript">';
				echo 'window.parent.afterupload("err","产品分类不存在！");  ';
				echo '</script>';
				exit();
			}
		}
		
		//检查简介长度。简介可以为空，最多200字。
		$product_intro_gpc = trim(getPG('product_intro'));
		if($product_intro_gpc)
		{
			$product_intro = stripslashes($product_intro_gpc);
			$product_intro_len = mb_strlen($product_intro,'utf8');
			if($product_intro_len<1 || $product_intro_len>200)//检查长度
			{
				echo '<script type="text/javascript">';
				echo 'window.parent.afterupload("err","简介长度出错！");  ';
				echo '</script>';
				exit();
			}
		}
		
		
		//检查售价、市场价、成本价的格式。这三个价，可以为空。为空入库后就是0
		$deal_price = trim(getPG('deal_price'));
		if($deal_price<0)
		{
			echo '<script type="text/javascript">';
			echo 'window.parent.afterupload("err","售价不能是负数哦！");  ';
			echo '</script>';
			exit();
		}
		$market_price = trim(getPG('market_price'));
		
		$cost_price = trim(getPG('cost_price'));
		
		//检查备注的长度。可以为空，最多80字。
		$mark_gpc = trim(getPG('mark'));
		if($mark_gpc)
		{
			$mark = stripslashes($mark_gpc);//所见即所得。
			$mark_len = mb_strlen($mark,'utf8');
			if($mark_len<1 || $mark_len>80)
			{
				echo '<script type="text/javascript">';
				echo 'window.parent.afterupload("err","备注长度出错！");  ';
				echo '</script>';
				exit();
			}
		}
		
		//是否上架，0-不上架，1-上架
		$is_sale = getPG('is_sale');
		if($is_sale != 0 && $is_sale != 1)
		{
			echo '<script type="text/javascript">';
			echo 'window.parent.afterupload("err","是否上架出错！");  ';
			echo '</script>';
			exit();
		}
		
		
		//检查产品详情，可以为空，详情是富文本，没有字数限制。要正则匹配修改img的地址，并移动图片到产品对应的图片目录下以后，再写入库。
		$product_detail_gpc = getPG('product_detail');//富文本可能头尾有空格，故意的，就不trim了
		
		
		//检查购买链接。可以为空，最多280字
		$buy_url_gpc = trim(getPG('buy_url'));
		if($buy_url_gpc)
		{
			$buy_url = stripslashes($buy_url_gpc);
			$buy_url_len = mb_strlen($buy_url,'utf8');
			if($buy_url_len<1 || $buy_url_len>280)//检查长度
			{
				echo '<script type="text/javascript">';
				echo 'window.parent.afterupload("err","购买链接长度出错！");  ';
				echo '</script>';
				exit();
			}
		}
		
		//检查上传的产品主图，大小、格式，是否正确。
		$inputname = 'p_img';
		$ok_upload_img = array();//用于保存检查过关的上传图片序号。
		for($i=0;$i<8;$i++)
		{
			$files_error_code = $_FILES[$inputname]['error'][$i];
			//error code等于4，代表没有选择文件。
			if($files_error_code != '4')//代表肯定是上传图片了
			{
				if($files_error_code>0)
				{
					echo '<script type="text/javascript">';
					echo 'window.parent.afterupload("err","产品主图'.($i+1).'上传失败！");  ';
					echo '</script>';
					exit();
				}
				else //error code为0 ，代表上传成功。
				{
					//检查上传图片的大小，不能超出5M。
					$maxfilesize = 5*1024*1024;//5M换成字节。
					if($_FILES[$inputname]["size"][$i] > $maxfilesize)
					{
						echo '<script type="text/javascript">';
						echo 'window.parent.afterupload("err","图片'.($i+1).'大小不能超过5M，请重新上传。"); ';
						echo '</script>';
						exit();
					}
								
					//检查上传图片的格式，jpg、png、gif。
					$source_info = getimagesize($_FILES[$inputname]["tmp_name"][$i]);
					if($source_info[2] != 1 && $source_info[2] != 2 && $source_info[2] != 3)//1代表gif，2代表jpg，3代表png
					{
						echo '<script type="text/javascript">';
						echo 'window.parent.afterupload("err","图片格式出错，请重新选择图片。"); ';
						echo '</script>';
						exit();
					}
										
					//能走到这里，说明通过了各项检查。
					$ok_upload_img[] = $i;
				}
			}
		}
		
		//以上都通过了，插入数据库表，得到产品id。
// 		$insert_data = array(
// 			'bn' => $product_bn_gpc,//货号，可能为空
// 			'name' => $product_name_gpc,//产品名称，不能为空
// 			'deal_price' => $deal_price,//售价或成交价，可能为空
// 			'market_price' => $market_price,//市场价，可能为空
// 			'cost' => $cost_price,//成本价，可能为空
// 			'create_time' =>TIMESTAMP, //产品创建时间
// 			'lastmodify_time' => TIMESTAMP, //最后更新时间
// 			'mark' => $mark_gpc, //备注，可能为空
// 			'is_sale' => $is_sale, //0或1，1代表上架，0代表不上架。
// 			'introduction' => $product_intro_gpc, //简介，可以为空
// 			'buy_url' => $buy_url_gpc, //购买链接，可能为空
// 			);
		
// 		$new_product_id = $this->DatabaseHandler->insert('qkdb_product', $insert_data, true);

// 		if(!$new_product_id || $new_product_id<=0)
// 		{
// 			echo '<script type="text/javascript">';
// 			echo 'window.parent.afterupload("err","新产品创建失败，请刷新后再试。"); ';
// 			echo '</script>';
// 			exit();
// 		}
		
		
		$update_data = array(
			'name' => $product_name_gpc,//产品名称，不能为空
			'deal_price' => $deal_price,//售价或成交价，可能为空
			'market_price' => $market_price,//市场价，可能为空
			'cost' => $cost_price,//成本价，可能为空
			'lastmodify_time' => TIMESTAMP, //最后更新时间
			'mark' => $mark_gpc, //备注，可能为空
			'is_sale' => $is_sale, //0或1，1代表上架，0代表不上架。
			'introduction' => $product_intro_gpc, //简介，可以为空
			'buy_url' => $buy_url_gpc, //购买链接，可能为空
		);
		//如果货号为空，或为0，相当于货号不变
		if(!$product_bn_gpc)
		{
			
		}
		else
		{
			$update_data['bn'] = $product_bn_gpc;
		}
		
		//把产品主图片移到attachment文件夹中。
		$pimage_path = 'attachment/prod_'.$product_id.'/images/main_images/';
		if(!is_dir($pimage_path))
		{
			mkdir($pimage_path,0777,true);
		}
		if(!is_dir($pimage_path))
		{
			echo '<script type="text/javascript">';
			echo 'window.parent.afterupload("err","产品主图片目录创建失败，请重试！"); ';
			echo '</script>';
			exit();
		}
		//$ok_upload_img里保存的是检查合格的主图编号:0~7。也可能为空
		foreach($ok_upload_img as $oneimgbn)
		{
// 			//获得文件扩展名
// 			$temp_arr = explode(".", $_FILES[$inputname]["name"][$oneimgbn]);
// 			$file_ext = array_pop($temp_arr);
// 			$file_ext = trim($file_ext);
// 			$file_ext = strtolower($file_ext);
				
// 			$image_full_name = $pimage_path . $oneimgbn . '.' . $file_ext;
			$image_full_name = $pimage_path . $oneimgbn . '.' . 'jpg';//不管上传的是gif、png还是jpg，统统用jpg为后缀。只有这样才不会有1.jpg、1.png、1.gif同时存在于文件夹中。
				
			move_uploaded_file($_FILES[$inputname]["tmp_name"][$oneimgbn],$image_full_name);//已经存在的主图，会被覆盖掉。
		}
		
		//转换产品详情中的img标签，并把产品详情中的图片移到attachment文件夹中。
		$this->new_product_id = $product_id;//函数_image_rename中会用到$this->new_product_id
		
		//参考http://www.jb51.net/article/55446.htm
		//http://www.zuimoban.com/php/php/1572.html
		//http://www.oschina.net/code/snippet_583625_20448，说明了正则表达式中如何匹配反斜杠。要用四个反斜杠
		$new_product_detail = preg_replace_callback(
			'/(<\s*img\s+src\s*=\s*\\\\")(\/templates\/admin\/js\/kindeditor-4\.1\.7\/attached\/image\/\d+\/)(.+?\\\\")/i',
			array($this, '_image_rename'),
			$product_detail_gpc);
		//下面这种用法，是只替换，不走函数
		//$product_detail_new = preg_replace('/(<\s*img\s+src\s*=\s*")(\/templates\/admin\/js\/kindeditor-4\.1\.7\/attached\/image\/\d+\/)(.+?")/i',"\${1}/prod_123/\${3}",$product_detail);
		
		$update_data['detail'] = $new_product_detail;
		
		//更新产品
		$this->DatabaseHandler->update('qkdb_product', $update_data, "product_id = '".$product_id."'");
		
		
		//处理产品分类。可能是“未分类”，未分类则$product_cat_id为空。
		if($product_cat_id && $product_cat_id>0)
		{
			//先删除原有的分类
			$this->DatabaseHandler->delete('qkdb_product_cat', "product_id = '".$product_id."'");
			//再插入新分类
			$insert_data = array(
					'product_id' => $product_id,
					'cat_id' => $product_cat_id,
			);
			if(!$this->DatabaseHandler->insert('qkdb_product_cat', $insert_data))
			{
				//插入分类表失败
				echo '<script type="text/javascript">';
				echo 'window.parent.afterupload("err","插入产品分类表失败，请重试！"); ';
				echo '</script>';
				exit();
			}
		}
		else 
		{
			//用户选择“未分类”
			//把已存在的该产品的分类关系，给删掉。
			$this->DatabaseHandler->delete('qkdb_product_cat', "product_id = '".$product_id."'");
		}
		
		//修改一个产品成功。
		echo '<script type="text/javascript">';
		echo 'window.parent.afterupload("succ","");  ';
		echo '</script>';
		exit();
		
	}
	
	//删除一个或多个产品
	function Do_delete_products()
	{
		$product_ids = getPG('pids');
		if(!$product_ids)
		{
			json_error('请勾选要删除的产品！');
		}
		
		$product_ids = rtrim($product_ids, ','); //去除最右边的一个逗号
		
		$pidsarr = explode(',',$product_ids);//产品id数组
		$pidsarr = array_filter($pidsarr);
		
		//删除product表
		$this->DatabaseHandler->delete('qkdb_product', "product_id in (".$product_ids.")");
		
		//删除product_cat表
		$this->DatabaseHandler->delete('qkdb_product_cat', "product_id in (".$product_ids.")");
		
		//删除该产品对应的图片目录
		foreach($pidsarr as $onepid)
		{
			@deldir("attachment/prod_".$onepid."/");//删除该产品对应的附件目录。前面加@是因为，如果foreach循环5次或5次以上，json_result到前台的信息会有很多warning信息，导致前台判断失误。但5次以下没有warning信息，不明白为什么。
		}
		json_result();
	}
	
	
	//保存产品排序
	function Save_product_paixu()
	{

		$product_paixu = getPG('product_paixu');
		//一般json字符串在php中需要json_decode，才能得到数组，而这里的前端传过来的不是json字符串。
		//前端传过来的是js的数组。在php中直接可用当成数组使用。
		//前端页面json字符串---------ajax---------->后端php，需要json_decocd后赋给php变量，才能当成php对象使用。
		//前端页面js数组-------------ajax---------->后端php，无需json_decode，直接赋给php变量当成数组使用。
		
		//$product_paixu形如：
		/*array (
		 		0 =>
				array (
						'p_id' => '1',
						'paixu_num' => '1',
				),
				1 =>
				array (
						'p_id' => '2',
						'paixu_num' => '1',
				),
				2 =>
				array (
						'p_id' => '7',
						'paixu_num' => '1',
				),
				3 =>
				array (
						'p_id' => '8',
						'paixu_num' => '2',
				),
				4 =>
				array (
						'p_id' => '9',
						'paixu_num' => '3',
				),
				5 =>
				array (
						'p_id' => '3',
						'paixu_num' => '2',
				),
				6 =>
				array (
						'p_id' => '10',
						'paixu_num' => '1',
				),
				7 =>
				array (
						'p_id' => '11',
						'paixu_num' => '2',
				),
				8 =>
				array (
						'p_id' => '12',
						'paixu_num' => '3',
				),
				9 =>
				array (
						'p_id' => '4',
						'paixu_num' => '2',
				),
				10 =>
				array (
						'p_id' => '5',
						'paixu_num' => '1',
				),
				11 =>
				array (
						'p_id' => '6',
						'paixu_num' => '2',
				),
				)
		*/
		//更新qkdb_product表。
		foreach($product_paixu as $val)
		{
			$update_data = array(
				'paixu_num' => $val['paixu_num'],
			);
			$this->DatabaseHandler->update('qkdb_product', $update_data, "product_id = '".$val['p_id']."'");
		}
		
		json_result();
		
	}
	
	//保存产品对比的属性
	function Do_save_compare_property()
	{
		
		$product_compare_property = getPG('compare_property');//以@#分隔的字符串，比如'分辨率@#颜色@#电压'
		$product_compare_property_no_slash = stripslashes($product_compare_property);
		
		$compare_pro_arr = explode('@#', $product_compare_property_no_slash);
		$compare_pro_arr = array_filter($compare_pro_arr);
		
		
		
		$sql = 'truncate qkdb_product_compare_property';
		$this->DatabaseHandler->Query($sql);
		foreach($compare_pro_arr as $onepropertyname)
		{
			$this->DatabaseHandler->insert('qkdb_product_compare_property', array('property_name' => addslashes($onepropertyname)));
		}
		json_result();
	}
	
	//保存一个产品的产品对比的属性值
	function Do_save_compare_property_value()
	{
		$product_id = getPG('product_id'); //产品id
		$compare_property_data = getPG('compare_property_data'); //属性名称+属性值。
		/*
		 * $compare_property_data形如：array (
  1 => '800*900',
  2 => '1kg',
  3 => '', //空代表用户没填这个属性的值。
)
		 */
		
		if(!$product_id || $product_id<=0)
		{
			json_error('产品id不能为空！');
		}
		
		//判断产品id存不存在。
		$sql = "SELECT product_id, bn, name FROM `qkdb_product` where product_id = '".$product_id."' ; ";
		
		$query = $this->DatabaseHandler->Query($sql);
		$productrow = $this->DatabaseHandler->GetRow($query);
		if(!$productrow)
		{
			json_error('该产品不存在，可能已被删除！请重新确认后操作。');
		}
		
		//插入表qkdb_product_compare_property_value
		//插入之前先把老数据删掉。
		$this->DatabaseHandler->delete('qkdb_product_compare_property_value', "product_id = '".$product_id."'");
		foreach($compare_property_data as $property_id => $property_value)
		{
			$insert_data = array(
					'product_id' => $product_id,
					'property_id' => $property_id,
					'property_value' => $property_value,
			);
			$this->DatabaseHandler->insert('qkdb_product_compare_property_value', $insert_data);
		}
		
		json_result();
		
		
	}
	
	//删除一条搜索产品关键词的记录
	function Do_delete_searchlog()
	{
		$log_ids = getPG('aids');
		if(!$log_ids)
		{
			json_error('请勾选要删除的条目！');
		}
		
		$log_ids = rtrim($log_ids, ','); //去除最右边的一个逗号
		
		$aidsarr = explode(',',$log_ids);//id数组
		$aidsarr = array_filter($aidsarr);
		
		//在advidse表中进行删除
		$this->DatabaseHandler->delete('qkdb_searchlog_product', "id in (".$log_ids.")");
		
		json_result();
		
	}
	
}
?>