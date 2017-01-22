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
			case 'do_add_friendlink':  //新增一个友情链接
				$this->Do_add_friendlink();
				break;
				
			case 'do_edit_friendlink':  //编辑一个友情链接
				$this->Do_edit_friendlink();
				break;
				
			case 'do_delete_frindlink': //删除一个或多个友链
				$this->Do_delete_frindlink();
				break;
				
			case 'save_friendlink_paixu': //保存文章排序
				$this->Save_friendlink_paixu();
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
	
	//新建一个友情链接
	function Do_add_friendlink()
	{
		$friendlink_name_gpc = trim(getPG('friendlink_name'));
		$friendlink_url_gpc = trim(getPG('friendlink_url'));
		
		$friendlink_name = stripslashes($friendlink_name_gpc);//所见即所得
		$friendlink_name_len = mb_strlen($friendlink_name,'utf8');//中文、英文、数字、符号，都算一个字符。并不是中文算2个或3个。
		
		if($friendlink_name_len<1 || $friendlink_name_len>20)
		{
			echo '<script type="text/javascript">';
			echo 'window.parent.afterupload("err","对不起，友链名称长度出错。");  ';
			echo '</script>';
			exit();
		}
		
		$friendlink_url = stripslashes($friendlink_url_gpc);//所见即所得
		$friendlink_url_len = mb_strlen($friendlink_url,'utf8');
		if($friendlink_url_len<1 || $friendlink_url_len>90)
		{
			echo '<script type="text/javascript">';
			echo 'window.parent.afterupload("err","对不起，链接地址长度出错。");  ';
			echo '</script>';
			exit();
		}
		
		$insert_data = array(
			'friendlink_name' => $friendlink_name_gpc,
			'friendlink_url' => $friendlink_url_gpc,
		);
				
		$new_fl_id = $this->DatabaseHandler->insert('qkdb_friendlink', $insert_data,true);
		if( $new_fl_id > 0)
		{
			$update_data = array(
				'paixu_num' => $new_fl_id,
			);
			$this->DatabaseHandler->update('qkdb_friendlink', $update_data, "friendlink_id = '".$new_fl_id."'");
			echo '<script type="text/javascript">';
			echo 'window.parent.afterupload("succ","");  ';
			echo '</script>';
			exit();
		}
		else 
		{
			echo '<script type="text/javascript">';
			echo 'window.parent.afterupload("err","对不起，插入友链表失败。");  ';
			echo '</script>';
			exit();
		}
			
	}
	
	//编辑一个已经存在的友情链接
	function Do_edit_friendlink()
	{
		$friendlink_id = getPG('flid');//要编辑的友链id
		
		if(!$friendlink_id || $friendlink_id <= 0)
		{
			echo '<script type="text/javascript">';
			echo 'window.parent.afterupload("err","友链id不能为空。");  ';
			echo '</script>';
			exit();
		}
		
		$sql = "select * from qkdb_friendlink where friendlink_id = '".$friendlink_id."'";
		
		$qurey = $this->DatabaseHandler->Query($sql);
		
		$friendlinkrow = $this->DatabaseHandler->GetRow($qurey);
		
		if(!$friendlinkrow)
		{
			echo '<script type="text/javascript">';
			echo 'window.parent.afterupload("err","要编辑的友链不存在！");  ';
			echo '</script>';
			exit();
		}
		
		$friendlink_name_gpc = trim(getPG('friendlink_name'));
		$friendlink_url_gpc = trim(getPG('friendlink_url'));
		
		$friendlink_name = stripslashes($friendlink_name_gpc);//所见即所得
		$friendlink_name_len = mb_strlen($friendlink_name,'utf8');//中文、英文、数字、符号，都算一个字符。并不是中文算2个或3个。
		
		if($friendlink_name_len<1 || $friendlink_name_len>20)
		{
			echo '<script type="text/javascript">';
			echo 'window.parent.afterupload("err","对不起，友链名称长度出错。");  ';
			echo '</script>';
			exit();
		}
		
		$friendlink_url = stripslashes($friendlink_url_gpc);//所见即所得
		$friendlink_url_len = mb_strlen($friendlink_url,'utf8');
		if($friendlink_url_len<1 || $friendlink_url_len>90)
		{
			echo '<script type="text/javascript">';
			echo 'window.parent.afterupload("err","对不起，链接地址长度出错。");  ';
			echo '</script>';
			exit();
		}
		
		$update_data = array(
				'friendlink_name' => $friendlink_name_gpc,
				'friendlink_url' => $friendlink_url_gpc,
		);
		
		$this->DatabaseHandler->update('qkdb_friendlink', $update_data, "friendlink_id = '".$friendlink_id."'");
		
		//编辑友链成功
		echo '<script type="text/javascript">';
		echo 'window.parent.afterupload("succ","");  ';
		echo '</script>';
		exit();
		
	}
	
	function Do_delete_frindlink()
	{
		$fl_ids = getPG('aids');
		if(!$fl_ids)
		{
			json_error('请勾选要删除的条目！');
		}
		
		$fl_ids = rtrim($fl_ids, ','); //去除最右边的一个逗号
		
		//删除friendlink表
		$this->DatabaseHandler->delete('qkdb_friendlink', "friendlink_id in (".$fl_ids.")");

		json_result();
	}
	
	
	function Save_friendlink_paixu()
	{
		$friendlink_paixu = getPG('friendlink_paixu');
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
		//更新qkdb_friendlink表。
		foreach($friendlink_paixu as $val)
		{
			$update_data = array(
					'paixu_num' => $val['paixu_num'],
			);
			$this->DatabaseHandler->update('qkdb_friendlink', $update_data, "friendlink_id = '".$val['a_id']."'");
		}
		
		json_result();
		
		
	}
	
}
?>