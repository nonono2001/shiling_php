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

			case 'do_tihuo_apply':    //用户输入提货券号和提货码，处理该提交。
				$this->Do_tihuo_apply();
				break;

			case 'do_send_shouhuoinfo':    //用户发送收货地址等收货信息
				$this->Do_send_shouhuoinfo();
				break;
				
			default:
				$this->Tihuo_apply();  //用户输入提货券号和提货码的界面。
				break;
		}

	}


	//提货申请页
	function Tihuo_apply()
	{
		$page_title = '提货申请';
		include(template('tihuo_apply'));

	}

	//提货动作发生，post提货号和卡券号过来。
	function Do_tihuo_apply()
	{
		$tihuoquanhao = getPG('tihuoquanhao');
		$tihuoma = getPG('tihuoma');

		//检查提货券号和提货码的正确性
		$sql = "select * from qkdb_tihuo_card where tihuoquanhao= '".$tihuoquanhao."' and tihuoma= '".$tihuoma."'";
		$query = $this->DatabaseHandler->Query($sql);
		$onecardinfo = $this->DatabaseHandler->GetRow($query);

		if(!$onecardinfo)
		{
			//提货券号+提货码，不存在。
			$tips_message = '提货券号或提货码输入不正确，请返回重新输入。';
			include(template('tips_message'));
			return;
		}

		//判断该提货卡，是否已发货。如果已发货，则提示他已发货，不能再输入收货信息。
		if($onecardinfo['ship_status'] == 'shipped')
		{
			$tips_message = '您的货品已经发货，不能再次输入收货信息。';
			include(template('tips_message'));
			return;

		}

		//到这里，说明提货码正确且未发货。可以输入收货地址等信息。
		$page_title = '收货联系方式';
		include(template('shouhuo_address'));



	}

	//用户发送收货地址等收货信息。
	function Do_send_shouhuoinfo()
	{
		$tihuoquanhao = getPG('tihuoquanhao');
		$tihuoma = getPG('tihuoma');
		$shuohuoren = trim(getPG('shuohuoren'));
		$lianxidianhua = trim(getPG('lianxidianhua'));
		$shouhuodizhi = trim(getPG('shouhuodizhi'));

		if(!$shuohuoren || !$lianxidianhua || !$shouhuodizhi)
		{
			$tips_message = '收货人、联系电话、收货地址，都不能为空。';
			include(template('tips_message'));
			exit;
		}

		//判断提货码+提货券号，是否正确
		$sql = "select * from qkdb_tihuo_card where tihuoquanhao= '".$tihuoquanhao."' and tihuoma= '".$tihuoma."'";
		$query = $this->DatabaseHandler->Query($sql);
		$onecardinfo = $this->DatabaseHandler->GetRow($query);
		if(!$onecardinfo)
		{
			//提货券号+提货码，不存在。
			$tips_message = '对不起，您的提货券号或者提货码输入不正确，请重新输入。';
			include(template('tips_message'));
			exit;
		}

		//将收货信息，update到DB。
		$update_data = array(
			'is_ask_tihuo' => '1',
			'last_ask_tihuo_time' => TIMESTAMP,
			'shouhuoren_name' => $shuohuoren,
			'shouhuoren_phone' => $lianxidianhua,
			'shouhuoren_address' => $shouhuodizhi,
		);
		$this->DatabaseHandler->update('qkdb_tihuo_card',$update_data," id = '".$onecardinfo['id']."'");


		$tips_message = '恭喜！您的收货信息录入成功。请耐心等待您的礼品。';
		include(template('tips_message'));
		exit;

	}
	

	
}

?>