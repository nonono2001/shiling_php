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
			case 'address_info':
				$this->Address_info();
				break;

			case 'do_send_cardinfo':
				$this->Do_send_cardinfo();   //用户发送卡券号+密码，这里检查是否正确。
				break;

			case 'do_send_shouhuoinfo':    //用户发送收货地址等收货信息
				$this->Do_send_shouhuoinfo();
				break;

			case 'tihuo_apply_success':
				$this->Tihuo_apply_success(); //提货申请成功的一个提示页
				break;

			default:
				$this->Card_info(); //输入卡号密码的页面
				break;
		}

	}

	//输入卡号密码的页面
	function Card_info()
	{
		//必需要已登录
		if(!MEMBER_ID || MEMBER_ID <= 0)
		{
			//未登录，直接跳转登录页
			header('Location: index.php?mod=login');
			exit();
		}

		$page_title = '提货卡信息';
		include(template('ticket'));
	}

	//输入收货地址的页面
	function Address_info()
	{
		//必需要已登录
		if(!MEMBER_ID || MEMBER_ID <= 0)
		{
			//未登录，直接跳转登录页
			header('Location: index.php?mod=login');
			exit();
		}

		//前台会传来提货卡号+密码。
		$tihuo_card_no = getPG('ticket');
		$tihuo_password = getPG('ticketpassword');

		$page_title = '收货人信息';
		include(template('address'));
	}

	//提货申请成功的一个提示页
    function Tihuo_apply_success()
	{
		$page_title = '提货申请成功';
		include(template('success'));
	}

	//客户发送 卡号+密码，这里检查是否正确。
	function Do_send_cardinfo()
	{
		//必需要已登录
		if(!MEMBER_ID || MEMBER_ID <= 0)
		{
			json_error('未登录，需要登录','40010');
		}

		$tihuo_card_no = getPG('ticket');//提货卡号
		$tihuo_password = getPG('ticketpassword');//提货密码


		//检查提货券号和提货码的正确性。字段tihuoquanhao代表卡号，tihuoma代表密码。
		$sql = "select * from qkdb_tihuo_card where tihuoquanhao= '".addslashes($tihuo_card_no)."' and tihuoma= '".addslashes($tihuo_password)."'";
		$query = $this->DatabaseHandler->Query($sql);
		$onecardinfo = $this->DatabaseHandler->GetRow($query);

		if(!$onecardinfo)
		{
			//提货券号+提货码，不存在。
			$tips_message = '卡号或密码不正确，请重新输入。';
			json_error($tips_message,'40013');
		}

		//判断该提货卡，是否已发货。如果已发货，则提示他已发货，不能再输入收货信息。
		if($onecardinfo['ship_status'] == 'shipped')
		{
			$tips_message = '该卡号礼品已发货，不能再次申请提货。';
			json_error($tips_message,'40013');

		}

		//到这里，说明提货码正确且未发货。可以输入收货地址等信息。
		json_result();


	}

	//用户发送收货地址等收货信息。
	function Do_send_shouhuoinfo()
	{
		//必需要已登录
		if(!MEMBER_ID || MEMBER_ID <= 0)
		{
			json_error('未登录，需要登录','40010');
		}
		
		$tihuoquanhao = getPG('tihuo_card_no');
		$tihuoma = getPG('tihuo_password');

		$address_city = trim(getPG('address_city')); //收货地址，省市区
		$address_street = trim(getPG('address_street')); //收货地址，街道门牌号
		$lianxidianhua = trim(getPG('lianxidianhua')); //联系电话
		$shuohuoren = getPG('shuohuoren'); //收货人

		if(!$shuohuoren || !$lianxidianhua || !$address_city || !$address_street)
		{
			$tips_message = '收货人、联系电话、收货地址，都不能为空。';
			json_error($tips_message,'40013');
		}

		//判断提货码+提货券号，是否正确
		$sql = "select * from qkdb_tihuo_card where tihuoquanhao= '".addslashes($tihuoquanhao)."' and tihuoma= '".addslashes($tihuoma)."'";
		$query = $this->DatabaseHandler->Query($sql);
		$onecardinfo = $this->DatabaseHandler->GetRow($query);
		if(!$onecardinfo)
		{
			//提货券号+提货码，不存在。
			$tips_message = '对不起，您的提货卡号或者密码输入不正确，请重新输入。';
			json_error($tips_message,'40013');
		}

		if($onecardinfo['ship_status'] == 'shipped')
		{
			$tips_message = '该卡号礼品已发货，不能再次输入收货信息。';
			json_error($tips_message,'40013');

		}

		//将收货信息，update到DB。
		$update_data = array(
			'is_ask_tihuo' => '1',
			'front_type' => 'wx',
			'member_id' => MEMBER_ID,
			'last_ask_tihuo_time' => TIMESTAMP,
			'shouhuoren_name' => addslashes($shuohuoren),
			'shouhuoren_phone' => addslashes($lianxidianhua),
			'shouhuoren_address' => addslashes($address_city.$address_street),
		);
		$this->DatabaseHandler->update('qkdb_tihuo_card',$update_data," id = '".$onecardinfo['id']."'");


		$tips_message = '恭喜！您的收货信息录入成功。请耐心等待您的礼品。';
		json_result($tips_message);

	}
	
	

}
?>