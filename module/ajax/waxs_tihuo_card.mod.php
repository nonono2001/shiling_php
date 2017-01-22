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
			case 'do_delete_card': //删除一个或多个卡券
				$this->Do_delete_card();
				break;

			case 'do_edit_card': //编辑一个卡券
				$this->Do_edit_card();
				break;

			case 'do_delete_member': //删除一个或多个会员
				$this->Do_delete_member();
				break;

			case 'do_edit_member':
				$this->Do_edit_member();
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
	

	
	//删除一个或多个卡券信息
	function Do_delete_card()
	{
		$item_ids = getPG('aids');
		if(!$item_ids)
		{
			json_error('请勾选要删除的条目！');
		}

		$item_ids = rtrim($item_ids, ','); //去除最右边的一个逗号
		
//		$aidsarr = explode(',',$item_ids);//id数组
//		$aidsarr = array_filter($aidsarr);
		
		//在qkdb_tihuo_card表中进行删除
		$this->DatabaseHandler->delete('qkdb_tihuo_card', "id in (".$item_ids.")");
		
		json_result();
		
	}

	//编辑一个卡券
	function Do_edit_card()
	{
		$thcard_id = getPG('thcard_id');
		$is_shipped = getPG('is_shipped'); //shipped或unshipped，发货状态。
		$wuliu_company = trim(getPG('wuliu_company'));
		$wuliu_danhao = trim(getPG('wuliu_danhao'));
		$ship_time = trim(getPG('ship_time'));
		$remarks = trim(getPG('remarks'));

		if($is_shipped != 'shipped' && $is_shipped != 'unshipped')
		{
			//失败
			echo '<script type="text/javascript">';
			echo 'window.parent.afterupload("err","发货状态出错，请重填！");  ';
			echo '</script>';
			exit();
		}

		if(!$ship_time)
		{
			$ship_timestamp = 0;
		}
		else
		{
			$ship_timestamp = strtotime($ship_time);
			if(!$ship_timestamp)
			{
				//说明前端传过来的时间格式不正确。
				//失败
				echo '<script type="text/javascript">';
				echo 'window.parent.afterupload("err","发货时间格式错误，请重填！");  ';
				echo '</script>';
				exit();
			}
		}


		//更新qkdb_tihuo_card表
		$update_data = array(
			'ship_status' => $is_shipped,
			'wuliu_company' => $wuliu_company,
			'wuliu_danhao' => $wuliu_danhao,
			'ship_time' => $ship_timestamp,
			'remarks' => $remarks,
		);

		$this->DatabaseHandler->update('qkdb_tihuo_card',$update_data,"id = '".$thcard_id."'");

		//成功。
		echo '<script type="text/javascript">';
		echo 'window.parent.afterupload("succ","");  ';
		echo '</script>';
		exit();
	}

	//删除一个或多个会员
	function Do_delete_member()
	{
		$item_ids = getPG('aids');

		if(!$item_ids)
		{
			json_error('请勾选要删除的条目！');
		}

		$item_ids = rtrim($item_ids, ','); //去除最右边的一个逗号

//		$aidsarr = explode(',',$item_ids);//id数组
//		$aidsarr = array_filter($aidsarr);

		//在qkdb_tihuo_card表中进行删除
		$this->DatabaseHandler->delete('qkdb_member', "member_id in (".$item_ids.")");

		json_result();
	}

	function Do_edit_member()
	{
		$member_id = getPG('member_id');
		$cellphone = trim(getPG('cellphone'));
		$real_name = trim(getPG('real_name'));

		if(!$cellphone || !$real_name)
		{
			//失败
			echo '<script type="text/javascript">';
			echo 'window.parent.afterupload("err","手机号码和真实姓名不能为空，请重填！");  ';
			echo '</script>';
			exit();
		}

		$update_data = array(
			'cellphone' => $cellphone,
			'real_name' => $real_name,

		);
		$this->DatabaseHandler->update('qkdb_member',$update_data,"member_id = '".$member_id."'");

		//成功。
		echo '<script type="text/javascript">';
		echo 'window.parent.afterupload("succ","");  ';
		echo '</script>';
		exit();
	}
	
}
?>