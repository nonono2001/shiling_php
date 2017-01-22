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
			exit('请先登录。');
		}
		if(!IS_MEMBER_ADMIN || IS_MEMBER_ADMIN <= 0)//虽然登录了，但不是管理员
		{
			exit('只有管理员才可以操作。');
		}
		
		switch($this->Act)
		{
			case 'do_delete_advises': //删除一个或多个留言
				$this->Do_delete_advises();
				break;
				
			case 'show_one_advise_dialog': //显示一个留言的对话框
				$this->Show_one_advise_dialog();
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
	
	//显示一个留言的对话框
	function Show_one_advise_dialog()
	{
		$advise_id = getPG('advise_id');
		
		$sql = "select *, from_unixtime(create_time) as createtime from qkdb_advise where advise_id = '".$advise_id."'";
		$query = $this->DatabaseHandler->Query($sql);
		$adviserow = $this->DatabaseHandler->GetRow($query);
		
		$adviserow['nickname'] = htmlspecialchars($adviserow['nickname']);
		$adviserow['email'] = htmlspecialchars($adviserow['email']);
		$adviserow['phone'] = htmlspecialchars($adviserow['phone']);
		$adviserow['content'] = htmlspecialchars($adviserow['content']);
		
		include(template('dialog_show_advise','admin'));
	}
	
	//删除一个或多个留言
	function Do_delete_advises()
	{
		$advidse_ids = getPG('aids');
		if(!$advidse_ids)
		{
			json_error('请勾选要删除的条目！');
		}
		
		$advidse_ids = rtrim($advidse_ids, ','); //去除最右边的一个逗号
		
		$aidsarr = explode(',',$advidse_ids);//留言id数组
		$aidsarr = array_filter($aidsarr);
		
		//在advidse表中进行删除
		$this->DatabaseHandler->delete('qkdb_advise', "advise_id in (".$advidse_ids.")");
		
		json_result();
		
	}
	
}
?>