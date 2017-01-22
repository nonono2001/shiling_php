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
			case 'save_cat_paixu':
				$this->Save_cat_paixu();  //保存文章分类排序
				break;
			
			case 'dialog_cat_edit':
				$this->Dialog_cat_edit();  //文章分类编辑对话框
				break;
				
			case 'dialog_cat_new':
				$this->Dialog_cat_new();  //文章分类新增对话框
				break;
				
			case 'do_save_cat':
				$this->Do_save_cat();  //对产品分类编辑或新增的保存
				break;
				
			case 'delete_cat':
				$this->Delete_cat();  //删除一个文章分类
				break;
				
			case 'do_add_article':  //新增一个文章
				$this->Do_add_article();
				break;
				
			case 'do_edit_article':  //编辑一个文章
				$this->Do_edit_article();
				break;
				
			case 'do_delete_articles': //删除一个或多个文章
				$this->Do_delete_articles();
				break;
				
			case 'save_article_paixu': //保存文章排序
				$this->Save_article_paixu();
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
			$sql = "UPDATE qkdb_artcategory SET paixu_num = '".$val['paixu_num']."' " .
				   " WHERE cat_id = '".$val['cat_id']."'" ;
			$query = $this->DatabaseHandler->Query($sql);
		}
		
		json_result();
	}
	
	function Dialog_cat_edit()
	{
		$cat_id = getPG('cat_id');//该值为要编辑的分类id。
		
		$sql = "select * from qkdb_artcategory where cat_id = '".$cat_id."'";
		$query = $this->DatabaseHandler->Query($sql);
		$catrow = $this->DatabaseHandler->GetRow($query);
		if(!$catrow)
		{
			//说明$cat_id错误。
			echo '<div style="float:left;width:100%;height:100px;line-height:100px;margin-top:20px;font-family:microsoft yahei;text-align:center;font-size:18px;">该分类不存在，无法编辑！请重新选择。</div>';
			return;
		}
		
		$sql2 = "select * from qkdb_artcategory where cat_id = '".$catrow['parent_id']."'";
		$query2 = $this->DatabaseHandler->Query($sql2);
		$parent_catrow = $this->DatabaseHandler->GetRow($query2);
		
		$optype = 'edit';//用于区别是新增还是编辑
		$catname_tobe_edit = $catrow['name'];
		$catname_parent = $parent_catrow['name'];
		include(template('dialog_art_cat_edit','admin'));
	}
	
	//文章分类新建对话框
	function Dialog_cat_new()
	{
		$p_cat_id = getPG('p_cat_id');//该值为父分类id，新增的分类为它的子分类。
		
		$sql = "select * from qkdb_artcategory where cat_id = '".$p_cat_id."'";
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

		$catname_parent = $pcatrow['name'];//也许是空。
		include(template('dialog_art_cat_edit','admin'));
		
	}
	
	//文章分类编辑或新增后保存。
	function Do_save_cat()
	{
		$optype = getPG('optype');
		if($optype == 'edit')
		{
			$cat_id = getPG('cat_id');//要编辑的cat_id
			$cat_name = trim(getPG('cat_name'));//可能有反斜杠的。
			$cat_name_len = mb_strlen(stripslashes($cat_name),'utf8');//中文、英文、数字、符号，都算一个字符。并不是中文算2个或3个。
			if($cat_name_len<1 || $cat_name_len>10)
			{
				json_error('对不起，分类名称长度出错。');
			}
			
			//该cat_id可能不存在。
			$sql = "select * from qkdb_artcategory where cat_id = '".$cat_id."'";
			$query = $this->DatabaseHandler->Query($sql);
			$catrow = $this->DatabaseHandler->GetRow($query);
			if(!$catrow)
			{
				json_error('对不起，分类不存在，请重新选择。');
			}
			
			$sql = "UPDATE qkdb_artcategory set name = '".$cat_name."' where cat_id = '".$cat_id."' ";
			$res = $this->DatabaseHandler->Query($sql);
			if($res)
			{
				json_result();
			}
			else
			{
				json_error('更新qkdb_artcategory表失败！');
			}
		}
		else if($optype == 'new')
		{
			$p_cat_id = getPG('cat_id');//父分类cat_id，往它下面新添加子分类
			$cat_name = trim(getPG('cat_name'));//可能有反斜杠的。
			$cat_name_len = mb_strlen(stripslashes($cat_name),'utf8');//中文、英文、数字、符号，都算一个字符。并不是中文算2个或3个。
			if($cat_name_len<1 || $cat_name_len>10)
			{
				json_error('对不起，分类名称长度出错。');
			}
				
			//该cat_id可能不存在。
			$sql = "select * from qkdb_artcategory where cat_id = '".$p_cat_id."'";
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
			
			$sql2 = "SELECT MAX( paixu_num ) as maxpaixu FROM  `qkdb_artcategory` WHERE parent_id =  '".$p_cat_id."'";
			$query2 = $this->DatabaseHandler->Query($sql2);
			$row = $this->DatabaseHandler->GetRow($query2);//$row[maxpaixu]就是孩子分类中最大的排序号
			$new_paixu_num = $row['maxpaixu']+1;
			
			$insertdata = array(
					'name' => $cat_name,
					'level' => $new_level,
					'parent_id' => $p_cat_id,
					'paixu_num' => $new_paixu_num,
			);
			$res = $this->DatabaseHandler->insert('qkdb_artcategory', $insertdata, true);
			
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
				$this->DatabaseHandler->update('qkdb_artcategory', $update_data, "cat_id = '".$res."'");
				
				json_result();
			}
			else
			{
				json_error('新增分类失败！');
			}
		}
		
	}
	
	//删除一个文章分类
	function Delete_cat()
	{
		$cat_id = getPG('cat_id'); //要删除的catid
		
		$sql = "select * from qkdb_artcategory where full_id like '%,".$cat_id.",%'";
		$query = $this->DatabaseHandler->Query($sql);//得到cat_id的分类以及它的子分类，这些都是需要删的。
		$catid_to_delete = array();//要删除的catid
		while($row = $this->DatabaseHandler->GetRow($query))
		{
			$catid_to_delete[$row['cat_id']] = $row['cat_id'];
		}
		if($catid_to_delete)
		{
			$catid_str = jimplode($catid_to_delete);//得到用逗号隔开的catid字符串。
			//先删除qkdb_artcategory表，
			$this->DatabaseHandler->delete('qkdb_artcategory', "cat_id in (".$catid_str.")");
			
			//再删除qkdb_article_cat表
			$this->DatabaseHandler->delete('qkdb_article_cat', "cat_id in (".$catid_str.")");
		}
		
		json_result();
	}
	
	
	//添加一个文章（新增文章）
	function Do_add_article()
	{
		/*$_POST过来的内容如例子如下：
		2015-03-06 16:24:45 ModuleObject::Do_add_article @ $_POST: array (
  'art_title' => '年度计划',
  'article_cat' => '10',
  'author_name' => '小王',
  'from_url' => 'jd.com',
  'abstract' => 'asdfsadf',
  'atricle_content' => '<p>

	asdf

</p>

<p>

	33f3

</p>',
)*/
		//检查标题长度。标题的格式不用检查，任何字符都可。
		$art_title_gpc = trim(getPG('art_title'));
		if(mb_strlen($art_title_gpc,'utf8') > 0)//不用if($art_title_gpc)这种判断是因为$product_name_gpc为'0'的话，if为假。
		{
			$art_title = stripslashes($art_title_gpc);//所见即所得。
			$art_title_len = mb_strlen($art_title,'utf8');
			if($art_title_len<1 || $art_title_len>50)
			{
				echo '<script type="text/javascript">';
				echo 'window.parent.afterupload("err","标题长度出错！");  ';
				echo '</script>';
				exit();
			}
		}
		else //标题不能为空
		{
			echo '<script type="text/javascript">';
			echo 'window.parent.afterupload("err","标题不能为空！");  ';
			echo '</script>';
			exit();
		}
		
		//检查分类存不存在。用户可能选择未分类，即分类id为空。
		$article_cat_id = getPG('article_cat');
		if($article_cat_id)
		{
			$sql = "select * from qkdb_artcategory where cat_id = '".$article_cat_id."'";
			$query = $this->DatabaseHandler->Query($sql);
			
			if(!$this->DatabaseHandler->GetRow($query))
			{
				echo '<script type="text/javascript">';
				echo 'window.parent.afterupload("err","文章分类不存在！");  ';
				echo '</script>';
				exit();
			}
		}
		
		//检查作者长度。作者可以为空，最多20字。
		$author_name_gpc = trim(getPG('author_name'));
		if($author_name_gpc)
		{
			$author_name = stripslashes($author_name_gpc);
			$author_name_len = mb_strlen($author_name,'utf8');
			if($author_name_len<1 || $author_name_len>20)//检查长度
			{
				echo '<script type="text/javascript">';
				echo 'window.parent.afterupload("err","作者长度出错！");  ';
				echo '</script>';
				exit();
			}
		}
		
		//检查来源的长度。可以为空，最多280字。
		$from_url_gpc = trim(getPG('from_url'));
		if($from_url_gpc)
		{
			$from_url = stripslashes($from_url_gpc);
			$from_url_len = mb_strlen($from_url,'utf8');
			if($author_name_len<1 || $author_name_len>280)//检查长度
			{
				echo '<script type="text/javascript">';
				echo 'window.parent.afterupload("err","来源长度出错！");  ';
				echo '</script>';
				exit();
			}
		}
		
		//检查摘要的长度。可以为空，最多200字。
		$abstract_gpc = trim(getPG('abstract'));
		if($abstract_gpc)
		{
			$abstract = stripslashes($abstract_gpc);//所见即所得。
			$abstract_len = mb_strlen($abstract,'utf8');
			if($abstract_len<1 || $abstract_len>200)
			{
				echo '<script type="text/javascript">';
				echo 'window.parent.afterupload("err","摘要长度出错！");  ';
				echo '</script>';
				exit();
			}
		}
		
		//检查文章内容，可以为空，内容是富文本，没有字数限制。先不插入数据库表，得到产品id以后再update进去。
		$atricle_content_gpc = getPG('atricle_content');//富文本可能头尾有空格，故意的，就不trim了
		
		//以上都通过了，插入数据库表，得到文章id。
		$insert_data = array(
				'title' => $art_title_gpc,//标题，不能为空
				'author' => $author_name_gpc,//作者，可以为空
				'create_time' => TIMESTAMP,//文章创建时间
				'lastmodify_time' => TIMESTAMP,//最后更新时间
				'from_where' => $from_url_gpc,//来源url，可能为空
				'abstract' => $abstract_gpc, //摘要
		);
		
		$new_article_id = $this->DatabaseHandler->insert('qkdb_article', $insert_data, true);
		
		if(!$new_article_id || $new_article_id<=0)
		{
			echo '<script type="text/javascript">';
			echo 'window.parent.afterupload("err","新文章创建失败，请刷新后再试。"); ';
			echo '</script>';
			exit();
		}
		
		//得到文章id以后，
		$update_data = array();
		
		//排序号，用文章id给update
		$update_data['paixu_num'] = $new_article_id;
		
		//转换文章内容中的img标签，并把文章内容中的图片移到attachment文件夹中。
		$this->article_id = $new_article_id;//函数_image_rename中会用到$this->article_id
		
		//参考http://www.jb51.net/article/55446.htm
		//http://www.zuimoban.com/php/php/1572.html
		//http://www.oschina.net/code/snippet_583625_20448，说明了正则表达式中如何匹配反斜杠。要用四个反斜杠
		$new_article_content = preg_replace_callback(
				'/(<\s*img\s+src\s*=\s*\\\\")(\/templates\/admin\/js\/kindeditor-4\.1\.7\/attached\/image\/\d+\/)(.+?\\\\")/i',
				array($this, '_image_rename'),
				$atricle_content_gpc);
		//下面这种用法，是只替换，不走函数
		//$product_detail_new = preg_replace('/(<\s*img\s+src\s*=\s*")(\/templates\/admin\/js\/kindeditor-4\.1\.7\/attached\/image\/\d+\/)(.+?")/i',"\${1}/prod_123/\${3}",$product_detail);
		
		$update_data['content'] = $new_article_content;
		
		
		//更新产品content、排序num
		$this->DatabaseHandler->update('qkdb_article', $update_data, "article_id = '".$new_article_id."'");
		
		
		//插入文章分类关系表。可能是“未分类”，未分类则$article_cat_id为空。
		if($article_cat_id && $article_cat_id>0)
		{
			$insert_data = array(
					'cat_id' => $article_cat_id,
					'article_id' => $new_article_id,
			);
			if(!$this->DatabaseHandler->insert('qkdb_article_cat', $insert_data))
			{
				//插入分类表失败
				$this->DatabaseHandler->delete('qkdb_article', "article_id = '".$new_article_id."'");
				deldir("attachment_article/article_".$new_article_id."/");//删除该文章对应的附件目录。
					
				echo '<script type="text/javascript">';
				echo 'window.parent.afterupload("err","插入文章分类关系表失败，请重试！"); ';
				echo '</script>';
				exit();
			}
		}
		
		//新建一个文章成功。
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
		$new_image_path_rel = "/attachment_article/article_".$this->article_id."/images/content_images/"; //新的图片相对路径
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
	

	
	
	//保存对于文章的修改，也就是保存编辑。
	//跟Do_add_article函数类似。
	function Do_edit_article()
	{
		/*$_POST过来的内容如例子如下：
		 2015-03-06 16:24:45 ModuleObject::Do_add_article @ $_POST: array (
		        'aid' => '2',
		 		'art_title' => '年度计划',
		 		'article_cat' => '10',
		 		'author_name' => '小王',
		 		'from_url' => 'jd.com',
		 		'abstract' => 'asdfsadf',
		 		'atricle_content' => '<p>
		
		 		asdf
		
		 		</p>
		
		 		<p>
		
		 		33f3
		
		 		</p>',
		 )*/
		
		//判断要修改的产品id，存不存在。
		$article_id = getPG('aid');
		$sql = "select article_id from qkdb_article where article_id = '".$article_id."'";
		$query = $this->DatabaseHandler->Query($sql);
		$arow = $this->DatabaseHandler->GetRow($query);
		if(!$arow)
		{
			echo '<script type="text/javascript">';
			echo 'window.parent.afterupload("err","要编辑的文章不存在，可能已被删除！");  ';
			echo '</script>';
			exit();
		}
		
		//检查标题长度。标题的格式不用检查，任何字符都可。
		$art_title_gpc = trim(getPG('art_title'));
		if(mb_strlen($art_title_gpc,'utf8') > 0)//不用if($art_title_gpc)这种判断是因为$product_name_gpc为'0'的话，if为假。
		{
			$art_title = stripslashes($art_title_gpc);//所见即所得。
			$art_title_len = mb_strlen($art_title,'utf8');
			if($art_title_len<1 || $art_title_len>50)
			{
				echo '<script type="text/javascript">';
				echo 'window.parent.afterupload("err","标题长度出错！");  ';
				echo '</script>';
				exit();
			}
		}
		else //标题不能为空
		{
			echo '<script type="text/javascript">';
			echo 'window.parent.afterupload("err","标题不能为空！");  ';
			echo '</script>';
			exit();
		}
		
		//检查分类存不存在。用户可能选择未分类，即分类id为空。
		$article_cat_id = getPG('article_cat');
		if($article_cat_id)
		{
			$sql = "select * from qkdb_artcategory where cat_id = '".$article_cat_id."'";
			$query = $this->DatabaseHandler->Query($sql);
			
			if(!$this->DatabaseHandler->GetRow($query))
			{
				echo '<script type="text/javascript">';
				echo 'window.parent.afterupload("err","文章分类不存在！");  ';
				echo '</script>';
				exit();
			}
		}
		
		//检查作者长度。作者可以为空，最多20字。
		$author_name_gpc = trim(getPG('author_name'));
		if($author_name_gpc)
		{
			$author_name = stripslashes($author_name_gpc);
			$author_name_len = mb_strlen($author_name,'utf8');
			if($author_name_len<1 || $author_name_len>20)//检查长度
			{
				echo '<script type="text/javascript">';
				echo 'window.parent.afterupload("err","作者长度出错！");  ';
				echo '</script>';
				exit();
			}
		}
		
		//检查来源的长度。可以为空，最多280字。
		$from_url_gpc = trim(getPG('from_url'));
		if($from_url_gpc)
		{
			$from_url = stripslashes($from_url_gpc);
			$from_url_len = mb_strlen($from_url,'utf8');
			if($author_name_len<1 || $author_name_len>280)//检查长度
			{
				echo '<script type="text/javascript">';
				echo 'window.parent.afterupload("err","来源长度出错！");  ';
				echo '</script>';
				exit();
			}
		}
		
		//检查摘要的长度。可以为空，最多200字。
		$abstract_gpc = trim(getPG('abstract'));
		if($abstract_gpc)
		{
			$abstract = stripslashes($abstract_gpc);//所见即所得。
			$abstract_len = mb_strlen($abstract,'utf8');
			if($abstract_len<1 || $abstract_len>200)
			{
				echo '<script type="text/javascript">';
				echo 'window.parent.afterupload("err","摘要长度出错！");  ';
				echo '</script>';
				exit();
			}
		}
		
		//检查文章内容，可以为空，内容是富文本，没有字数限制。先不插入数据库表，得到产品id以后再update进去。
		$atricle_content_gpc = getPG('atricle_content');//富文本可能头尾有空格，故意的，就不trim了
		
		//要更新文章的字段
		$update_data = array(
				'title' => $art_title_gpc,//标题，不能为空
				'author' => $author_name_gpc,//作者，可以为空
				'lastmodify_time' => TIMESTAMP,//最后更新时间
				'from_where' => $from_url_gpc,//来源url，可能为空
				'abstract' => $abstract_gpc, //摘要
		);
		
		//转换文章内容中的img标签，并把文章内容中的图片移到attachment文件夹中。
		$this->article_id = $article_id;//函数_image_rename中会用到$this->article_id
		
		//参考http://www.jb51.net/article/55446.htm
		//http://www.zuimoban.com/php/php/1572.html
		//http://www.oschina.net/code/snippet_583625_20448，说明了正则表达式中如何匹配反斜杠。要用四个反斜杠
		$new_article_content = preg_replace_callback(
				'/(<\s*img\s+src\s*=\s*\\\\")(\/templates\/admin\/js\/kindeditor-4\.1\.7\/attached\/image\/\d+\/)(.+?\\\\")/i',
				array($this, '_image_rename'),
				$atricle_content_gpc);
		//下面这种用法，是只替换，不走函数
		//$product_detail_new = preg_replace('/(<\s*img\s+src\s*=\s*")(\/templates\/admin\/js\/kindeditor-4\.1\.7\/attached\/image\/\d+\/)(.+?")/i',"\${1}/prod_123/\${3}",$product_detail);
		
		$update_data['content'] = $new_article_content;
		
		
		//更新产品信息
		$this->DatabaseHandler->update('qkdb_article', $update_data, "article_id = '".$article_id."'");
		
		
		//插入或修改文章分类关系表。可能是“未分类”，未分类则$article_cat_id为空。
		if($article_cat_id && $article_cat_id>0)
		{
			//先删除原有的分类
			$this->DatabaseHandler->delete('qkdb_article_cat', "article_id = '".$article_id."'");
			//再插入新分类
			$insert_data = array(
					'article_id' => $article_id,
					'cat_id' => $article_cat_id,
			);
			if(!$this->DatabaseHandler->insert('qkdb_article_cat', $insert_data))
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
			//把已存在的该文章的分类关系，给删掉。
			$this->DatabaseHandler->delete('qkdb_article_cat', "article_id = '".$article_id."'");
		}
		
		//修改一个文章成功。
	    echo '<script type="text/javascript">';
		echo 'window.parent.afterupload("succ","");  ';
		echo '</script>';
		exit();
		
		
	}
	
	//删除一个或多个产品
	function Do_delete_articles()
	{
		$article_ids = getPG('aids');
		if(!$article_ids)
		{
			json_error('请勾选要删除的文章！');
		}
		
		$article_ids = rtrim($article_ids, ','); //去除最右边的一个逗号
		
		$aidsarr = explode(',',$article_ids);//产品id数组
		$aidsarr = array_filter($aidsarr);
		
		//删除article表
		$this->DatabaseHandler->delete('qkdb_article', "article_id in (".$article_ids.")");

		//删除article_cat表
		$this->DatabaseHandler->delete('qkdb_article_cat', "article_id in (".$article_ids.")");

		//删除该产品对应的图片目录
		foreach($aidsarr as $oneaid)
		{
			@deldir("attachment_article/article_".$oneaid."/");//删除该文章对应的附件目录。前面加@是因为，如果foreach循环5次或5次以上，json_result到前台的信息会有很多warning信息，导致前台判断失误。但5次以下没有warning信息，不明白为什么。
		}
		json_result();
	}
	
	
	//保存文章排序
	function Save_article_paixu()
	{

		$article_paixu = getPG('article_paixu');
		//一般json字符串在php中需要json_decode，才能得到数组，而这里的前端传过来的不是json字符串。
		//前端传过来的是js的数组。在php中直接可用当成数组使用。
		//前端页面json字符串---------ajax---------->后端php，需要json_decocd后赋给php变量，才能当成php对象使用。
		//前端页面js数组-------------ajax---------->后端php，无需json_decode，直接赋给php变量当成数组使用。
		
		//$product_paixu形如：
		/*array (
		 		0 =>
				array (
						'a_id' => '1',
						'paixu_num' => '1',
				),
				1 =>
				array (
						'a_id' => '2',
						'paixu_num' => '1',
				),
				2 =>
				array (
						'a_id' => '7',
						'paixu_num' => '1',
				),
				3 =>
				array (
						'a_id' => '8',
						'paixu_num' => '2',
				),
				4 =>
				array (
						'a_id' => '9',
						'paixu_num' => '3',
				),
				5 =>
				array (
						'a_id' => '3',
						'paixu_num' => '2',
				),
				6 =>
				array (
						'a_id' => '10',
						'paixu_num' => '1',
				),
				7 =>
				array (
						'a_id' => '11',
						'paixu_num' => '2',
				),
				8 =>
				array (
						'a_id' => '12',
						'paixu_num' => '3',
				),
				9 =>
				array (
						'a_id' => '4',
						'paixu_num' => '2',
				),
				10 =>
				array (
						'a_id' => '5',
						'paixu_num' => '1',
				),
				11 =>
				array (
						'a_id' => '6',
						'paixu_num' => '2',
				),
				)
		*/
		//更新qkdb_article表。
		foreach($article_paixu as $val)
		{
			$update_data = array(
				'paixu_num' => $val['paixu_num'],
			);
			$this->DatabaseHandler->update('qkdb_article', $update_data, "article_id = '".$val['a_id']."'");
		}
		
		json_result();
		
	}
}
?>