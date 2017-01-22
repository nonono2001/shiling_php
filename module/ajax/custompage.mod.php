<?php

class ModuleObject extends MasterObject
{
	var $custompage_id;

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
			case 'do_add_custompage':  //新增一个自定义页面
				$this->Do_add_custompage();
				break;
				
			case 'do_edit_custompage':  //编辑一个自定义页面
				$this->Do_edit_custompage();
				break;
				
			case 'do_delete_custompage': //删除一个或多个自定义页面
				$this->Do_delete_custompage();
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

	//新增自定义页面
	function Do_add_custompage()
	{
		/*$_POST过来的内容如例子如下：
		2015-03-09 21:33:24 ModuleObject::Do_add_custompage @ $_POST: array (
  'cp_title' => '上面',
  'cp_content' => '上上眼睁睁',
)*/
		
		//检查标题长度。标题的格式不用检查，任何字符都可。
		$cp_title_gpc = trim(getPG('cp_title'));
		if(mb_strlen($cp_title_gpc,'utf8') > 0)//不用if($cp_title_gpc)这种判断是因为$cp_title_gpc为'0'的话，if为假。
		{
			$cp_title = stripslashes($cp_title_gpc);//所见即所得。
			$cp_title_len = mb_strlen($cp_title,'utf8');
			if($cp_title_len<1 || $cp_title_len>50)
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
		
		//自定义页面内容，可以为空，内容是富文本，没有字数限制。先不插入数据库表，得到产品id以后再update进去。
		$cp_content_gpc = getPG('cp_content');//富文本可能头尾有空格，故意的，就不trim了
		
		//以上都通过了，插入数据库表，得到文章id。
		$insert_data = array(
				'title' => $cp_title_gpc,//标题，不能为空
				'create_time' => TIMESTAMP,//文章创建时间
				'lastmodify_time' => TIMESTAMP,//最后更新时间
		);
		
		$new_custompage_id = $this->DatabaseHandler->insert('qkdb_custom_page', $insert_data, true);
		
		if(!$new_custompage_id || $new_custompage_id<=0)
		{
			echo '<script type="text/javascript">';
			echo 'window.parent.afterupload("err","自定义页面创建失败，请刷新后再试。"); ';
			echo '</script>';
			exit();
		}
		
		//得到自定义页面id以后，
		$update_data = array();
		
		//转换自定义页面的内容中的img标签，并把内容中的图片移到attachment文件夹中。
		$this->custompage_id = $new_custompage_id;//函数_image_rename中会用到$this->custompage_id
		
		//参考http://www.jb51.net/article/55446.htm
		//http://www.zuimoban.com/php/php/1572.html
		//http://www.oschina.net/code/snippet_583625_20448，说明了正则表达式中如何匹配反斜杠。要用四个反斜杠
		$new_cp_content = preg_replace_callback(
				'/(<\s*img\s+src\s*=\s*\\\\")(\/templates\/admin\/js\/kindeditor-4\.1\.7\/attached\/image\/\d+\/)(.+?\\\\")/i',
				array($this, '_image_rename'),
				$cp_content_gpc);
		//下面这种用法，是只替换，不走函数
		//$product_detail_new = preg_replace('/(<\s*img\s+src\s*=\s*")(\/templates\/admin\/js\/kindeditor-4\.1\.7\/attached\/image\/\d+\/)(.+?")/i',"\${1}/prod_123/\${3}",$product_detail);
		
		$update_data['content'] = $new_cp_content;
		
		
		//更新产品content
		$this->DatabaseHandler->update('qkdb_custom_page', $update_data, "custompage_id = '".$new_custompage_id."'");
		
		//新建一个自定义页面成功。
	    echo '<script type="text/javascript">';
		echo 'window.parent.afterupload("succ","");  ';
		echo '</script>';
		exit();
		
	}
	
	//内部函数，对于提交的富文本时处理里面的img路径
	//preg_replace_callback函数正则匹配，每次匹配都会走一遍这个函数。
	//替换img的src，并将kindeditor组件中的图片，剪切到产品相对应的图片目录下。
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
		$new_image_path_rel = "/attachment_custompage/custompage_".$this->custompage_id."/images/content_images/"; //新的图片相对路径
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
	
	
	//保存对于自定义页面的修改，也就是保存编辑。
	//跟Do_add_custompage函数类似。
	function Do_edit_custompage()
	{
		/*$_POST过来的内容如例子如下：
		 2015-03-09 21:33:24 ModuleObject::Do_add_custompage @ $_POST: array (
		 		'cpid' => '3',
		 		'cp_title' => '上面',
		 		'cp_content' => '上上眼睁睁',
		 )*/
		$custompage_id = getPG(cpid);
		//判断自定义页面的id存不存在。
		$sql = "SELECT * FROM `qkdb_custom_page` where custompage_id = '".$custompage_id."' ; ";
		
		$query = $this->DatabaseHandler->Query($sql);
		$custompagerow = $this->DatabaseHandler->GetRow($query);
		if(!$custompagerow)
		{
			echo '<script type="text/javascript">';
			echo 'window.parent.afterupload("err","要编辑的自定义页面不存在，可能已被删除！");  ';
			echo '</script>';
			exit();
		}
		
		
		//检查标题长度。标题的格式不用检查，任何字符都可。
		$cp_title_gpc = trim(getPG('cp_title'));
		if(mb_strlen($cp_title_gpc,'utf8') > 0)//不用if($cp_title_gpc)这种判断是因为$cp_title_gpc为'0'的话，if为假。
		{
			$cp_title = stripslashes($cp_title_gpc);//所见即所得。
			$cp_title_len = mb_strlen($cp_title,'utf8');
			if($cp_title_len<1 || $cp_title_len>50)
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

		//检查文章内容，可以为空，内容是富文本，没有字数限制。先不插入数据库表，得到id以后再update进去。
		$cp_content_gpc = getPG('cp_content');//富文本可能头尾有空格，故意的，就不trim了
		
		//要更新的字段
		$update_data = array(
				'title' => $cp_title_gpc,//标题，不能为空
				'lastmodify_time' => TIMESTAMP,//最后更新时间
		);
		
		//转换内容中的img标签，并把内容中的图片移到attachment文件夹中。
		$this->custompage_id = $custompage_id;//函数_image_rename中会用到$this->article_id
		
		//参考http://www.jb51.net/article/55446.htm
		//http://www.zuimoban.com/php/php/1572.html
		//http://www.oschina.net/code/snippet_583625_20448，说明了正则表达式中如何匹配反斜杠。要用四个反斜杠
		$new_cp_content = preg_replace_callback(
				'/(<\s*img\s+src\s*=\s*\\\\")(\/templates\/admin\/js\/kindeditor-4\.1\.7\/attached\/image\/\d+\/)(.+?\\\\")/i',
				array($this, '_image_rename'),
				$cp_content_gpc);
		//下面这种用法，是只替换，不走函数
		//$product_detail_new = preg_replace('/(<\s*img\s+src\s*=\s*")(\/templates\/admin\/js\/kindeditor-4\.1\.7\/attached\/image\/\d+\/)(.+?")/i',"\${1}/prod_123/\${3}",$product_detail);
		
		$update_data['content'] = $new_cp_content;
		
		//更新自定义页面信息
		$this->DatabaseHandler->update('qkdb_custom_page', $update_data, "custompage_id = '".$custompage_id."'");
		
		//修改一个自定义页面成功。
	    echo '<script type="text/javascript">';
		echo 'window.parent.afterupload("succ","");  ';
		echo '</script>';
		exit();
		
		
	}
	
	//删除一个或多个自定义页面
	function Do_delete_custompage()
	{
		$custompage_ids = getPG('item_ids');
		if(!$custompage_ids)
		{
			json_error('请勾选要删除的自定义页面！');
		}
		
		$custompage_ids = rtrim($custompage_ids, ','); //去除最右边的一个逗号
		
		$aidsarr = explode(',',$custompage_ids);//id数组
		$aidsarr = array_filter($aidsarr);
		
		//删除qkdb_custom_page表
		$this->DatabaseHandler->delete('qkdb_custom_page', "custompage_id in (".$custompage_ids.")");

		//删除该自定义页对应的图片目录
		foreach($aidsarr as $oneaid)
		{
			@deldir("attachment_custompage/custompage_".$oneaid."/");//删除该自定义页对应的附件目录。前面加@是因为，如果foreach循环5次或5次以上，json_result到前台的信息会有很多warning信息，导致前台判断失误。但5次以下没有warning信息，不明白为什么。
		}
		json_result();
	}
	
}
?>