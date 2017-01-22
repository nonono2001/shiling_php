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
			case 'download_new':
				$this->Download_new();  //新添加一个下载的对话框。
				break;
				
		 	case 'download_edit':
				$this->Download_edit();  //编辑一个下载的对话框。
				break;
				
			
			case 'do_save_download':
				$this->Do_save_download(); //保存新增，或者保存编辑
				break;
				
			case 'do_delete_items':
				$this->Do_delete_items();  //删除一个或多个下载条目。
				break;
			
			case 'save_download_paixu':
				$this->Save_download_paixu();  //保存下载条目的排序
				break;
			
			case 'save_download_info':
				$this->Save_download_info(); //保存下载人的信息
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
	
	
	//新增一个下载的对话框
	function Download_new()
	{
		$optype = 'new';//用于区别是新增还是编辑
		
		include(template('dialog_down_edit','admin'));
		
	}
	
	//编辑一个下载的对话框
	function Download_edit()
	{
		$optype = 'edit';//用于区别是新增还是编辑
		$download_id = getPG('download_id');//要编辑的download_id
		//需要得到要编辑的下载的当前内容
		$sql = "select * from qkdb_download_url where id = '".$download_id."'";
		$query = $this->DatabaseHandler->Query($sql);
		$iteminfo = $this->DatabaseHandler->GetRow($query);
		include(template('dialog_down_edit','admin'));
	
	}
	
	//保存新增，或者保存编辑
	function Do_save_download()
	{
		$optype = getPG('optype');
		
		if($optype == 'edit')
		{
			$download_id = getPG('download_id');//要编辑的download_id
			$download_title = trim(getPG('download_title')); //要编辑的下载标题，最长50字符
			$download_des = trim(getPG('download_des')); //要编辑的下载描述，最长200字符
			$download_url = trim(getPG('download_url'));//可能有反斜杠的。最长200字符
			$download_pass = getPG('download_pass');//download_pass不要trim
			
			//中文、英文、数字、符号，都算一个字符。并不是中文算2个或3个。
			$download_title_len = mb_strlen(stripslashes($download_title),'utf8');
			if($download_title_len<1 || $download_title_len>50)
			{
				json_error('对不起，下载标题长度出错。');
			}
			
			$download_des_len = mb_strlen(stripslashes($download_des),'utf8');
			if($download_des_len>200)//描述可以不填。长度可以为0。
			{
				json_error('对不起，下载描述长度出错。');
			}
			
			$download_url_len = mb_strlen(stripslashes($download_url),'utf8');
			if($download_url_len<1 || $download_url_len>200)
			{
				json_error('对不起，下载链接长度出错。');
			}
			
			//该download_id可能不存在。
			$sql = "select * from qkdb_download_url where id = '".$download_id."'";
			$query = $this->DatabaseHandler->Query($sql);
			$downrow = $this->DatabaseHandler->GetRow($query);
			if(!$downrow)
			{
				json_error('对不起，下载不存在，请重新选择。');
			}
				
			$sql = "UPDATE qkdb_download_url set download_title = '".$download_title."', download_des = '".$download_des."', url = '".$download_url."', password = '".$download_pass."' where id = '".$download_id."' ";
			$res = $this->DatabaseHandler->Query($sql);
			if($res)
			{
				json_result();
			}
			else
			{
				json_error('更新qkdb_download_url表失败！');
			}
		}
		else if($optype == 'new')
		{
			$download_title = trim(getPG('download_title')); //要编辑的下载标题，最长50字符
			$download_des = trim(getPG('download_des')); //要编辑的下载描述，最长200字符
			$download_url = trim(getPG('download_url'));//可能有反斜杠的。最长200字符
			$download_pass = getPG('download_pass');//download_pass不要trim
			
			//中文、英文、数字、符号，都算一个字符。并不是中文算2个或3个。
			$download_title_len = mb_strlen(stripslashes($download_title),'utf8');
			if($download_title_len<1 || $download_title_len>50)
			{
				json_error('对不起，下载标题长度出错。');
			}
				
			$download_des_len = mb_strlen(stripslashes($download_des),'utf8');
			if($download_des_len>200)//描述可以不填。长度可以为0。
			{
				json_error('对不起，下载描述长度出错。');
			}
				
			$download_url_len = mb_strlen(stripslashes($download_url),'utf8');
			if($download_url_len<1 || $download_url_len>200)
			{
				json_error('对不起，下载链接长度出错。');
			}
				
			$insertdata = array(
					'download_title' => $download_title,
					'download_des' => $download_des,
					'url' => $download_url,
					'password' => $download_pass,
					'paixu_num' => 0,
			);
			$res = $this->DatabaseHandler->insert('qkdb_download_url', $insertdata, true);
			
			if($res)
			{
				//$res就是新插入记录的id
				//对刚才新生成的下载，update它的paixu_num
				$update_data = array(
						'paixu_num' => $res,
				);
				$this->DatabaseHandler->update('qkdb_download_url', $update_data, "id = '".$res."'");
			
				json_result();
			}
			else
			{
				json_error('新增下载失败！');
			}
		}
		
	}
	
	//删除一个或多个下载条目。
	function Do_delete_items()
	{
		$download_ids = getPG('aids'); //要删除的下载id，用逗号隔开的字符串。
		
		if(!$download_ids)
		{
			json_error('请勾选要删除的条目！');
		}
		
		$download_ids = rtrim($download_ids, ','); //去除最右边的一个逗号
		
		$idsarr = explode(',',$download_ids);//留言id数组
		$idsarr = array_filter($idsarr);
		
		//在qkdb_download_url表中进行删除
		$this->DatabaseHandler->delete('qkdb_download_url', "id in (".$download_ids.")");
		
		//同时删除下载者信息，即qkdb_download_info表中进行删除。
		$this->DatabaseHandler->delete('qkdb_download_info', "download_id in (".$download_ids.")");
		
		json_result();
		
		
	}
	
	//保存下载条目的排序
	function Save_download_paixu()
	{
		$download_paixu = getPG('download_paixu');//前端传过来该变量是json格式。放到php变量中直接变成数组使用。
		//一般json字符串在php中需要json_decode，才能得到数组，而这里的前端传过来的不是json字符串。
		//前端传过来的是js的数组。在php中直接可用当成数组使用。
		//前端页面json字符串---------ajax---------->后端php，需要json_decocd后赋给php变量，才能当成php对象使用。
		//前端页面js数组-------------ajax---------->后端php，无需json_decode，直接赋给php变量当成数组使用。		
		
		//$download_paixu形如：
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
		//更新qkdb_download_url表。
		foreach($download_paixu as $val)
		{
			$sql = "UPDATE qkdb_download_url SET paixu_num = '".$val['paixu_num']."' " .
				   " WHERE id = '".$val['a_id']."'" ;
			$query = $this->DatabaseHandler->Query($sql);
		}
		
		json_result();
	}
	
	//保存下载人的信息
	function Save_download_info()
	{
		$download_id = getPG('download_id'); //下载链接的id
		
		$yourname = trim(getPG('yourname')); //下载人的姓名
		
		$yourjob = trim(getPG('yourjob')); //下载人的职务
		
		$yourphone = trim(getPG('yourphone')); //下载人的联系方式
		
		$yourcompany = trim(getPG('yourcompany')); //下载人的工作单位
		
		//字段长度和必填性，都在前端检查过了。因为这个信息不是太重要，所以不在服务端再次检查了。
		
		//检查download_id这个下载链接存不存在。
		if(!$download_id)
		{
			json_error('下载链接的id不能为空！');
		}
		$sql = "select * from qkdb_download_url where id = '".$download_id."'";
		$query = $this->DatabaseHandler->Query($sql);
		$downrow = $this->DatabaseHandler->GetRow($query);
		if(!$downrow)
		{
			json_error('对不起，下载不存在，请重新选择。');
		}
		
		//新插入qkdb_download_info表
		$insert_data = array(
				'download_id' => $download_id,
				'download_time' => time(),
				'name' => $yourname,
				'job' => $yourjob,
				'contactinfo' => $yourphone,
				'company' => $yourcompany,
		);
		$res = $this->DatabaseHandler->insert('qkdb_download_info', $insert_data, true);
			
		if($res)
		{
			json_result();
		}
		else
		{
			json_error('新增下载人信息失败！');
		}
		
	}
	
}
?>