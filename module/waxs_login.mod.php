<?php

class ModuleObject extends MasterObject
{
	function ModuleObject()//构造函数
	{
		$this->MasterObject('db_on');

		$this->Execute();

	}


	function Execute()
	{
		switch ($this->Act) {
			case 'do_member_login':
				$this->Do_member_login();
				break;

			case 'member_reg': //会员注册页
				$this->Member_reg();
				break;

			case 'do_member_reg':
				$this->Do_member_reg();
				break;

			case 'hongbao':
				$this->Hongbao();  //红包展示页
				break;

			default:
				$this->Member_login();  //用户输入手机号+密码登录。
				break;
		}

	}

	//会员输入手机号+密码登录。
	//会员在该界面登录后，并不需要生成session或cookie。系统只需记录一下该用户最后一次登录时间。
	//因为会员登录后就跳转到微商城。而微商城自己会用微信授权登录。
	//这里登录，相当于引导会员到微商城。
	function Member_login()
	{
		$page_title = '会员登录';
		include(template('member_login'));
	}

	//提交登录信息
	function Do_member_login()
	{
		$cellphone = trim(getPG('cellphone'));
		$password = getPG('password');//密码不可以trim，空格也是密码的一部分

		if (!$cellphone || !$password) {
			$tips_message = '手机号或密码不能为空，请重新输入。';
			include(template('tips_message'));
			exit();
		}

		$sql = "select * from qkdb_member where cellphone = '" . $cellphone . "' and password = '" . md5($password) . "'";//md5默认是32位小写
		$query = $this->DatabaseHandler->Query($sql);
		$onemember = $this->DatabaseHandler->GetRow($query);
		if (!$onemember) {
			$tips_message = '手机号或密码输入不正确，请重新输入。';
			include(template('tips_message'));
			exit();
		}


		//登录成功，更新qkdb_member表的lastlogin_dateline字段。
		$update_data = array(
			'lastlogin_dateline' => TIMESTAMP,
		);
		$this->DatabaseHandler->update('qkdb_member',$update_data,"member_id = '".$onemember['member_id']."'");
		//跳转到微商城。这里微商城链接暂时不知，先用提示页代替
//		$tips_message = '恭喜！登录成功。';
//		include(template('tips_message'));
		//跳转到微商城。
		header('Location: http://wap.koudaitong.com/v2/feature/p7g7xopx');
		exit();
	}

	//新老会员注册页。直接录入数据库手机号码，是老会员（但只有手机号码这个信息）；从微信注册的，并且数据库中没有手机号的，叫新会员。
	function Member_reg()
	{
		$membertype = getPG('mtype'); //new或old
		$page_title = '会员注册';
		if($membertype == 'new')
		{
			include(template('member_reg_new'));
		}
		else
		{
			include(template('member_reg_old'));
		}

	}


	function Do_member_reg()
	{
		$page_title = '会员注册';

		$real_name = trim(getPG('real_name'));
		$cellphone = trim(getPG('cellphone'));
		$password = getPG('password'); //密码不要trim，空格是密码的一部分
		$password_confirm = getPG('password_confirm'); //密码不要trim，空格是密码的一部分

		if(!$real_name || !$cellphone || !$password || !$password_confirm)
		{
			$tips_message = '姓名、手机号、密码都不能为空。';
			include(template('tips_message'));
			exit();
		}
		if($password != $password_confirm)
		{
			$tips_message = '两次密码输入不相同，请重新输入。';
			include(template('tips_message'));
			exit();
		}

		//判断是新会员还是老会员。
		$sql = "select * from qkdb_member where cellphone = '".$cellphone."'";
		$query = $this->DatabaseHandler->Query($sql);
		$onemember = $this->DatabaseHandler->GetRow($query);
		//手机号码不存在，肯定是以新会员身份，进行注册，insert DB，最后跳转到新会员红包页。
		if(!$onemember)
		{
			$insert_data = array(
				'cellphone' => $cellphone,
				'real_name' => $real_name,
				'password' => md5($password),
				'is_new_old_member' => 'new',
				'reg_dateline' => TIMESTAMP,
			);
			$newid = $this->DatabaseHandler->insert('qkdb_member',$insert_data,1);
			if(!$newid)
			{
				//插入失败
				$tips_message = '对不起，注册失败，请重新注册。';
				include(template('tips_message'));
				exit();
			}
			else
			{
				//插入成功
				include(template('red_after_reg'));
				//跳转到微商城。
//				header('Location: http://wap.koudaitong.com/v2/feature/p7g7xopx');
				exit();
			}
		}
		else
		{
			//手机号码已存在,并且该会员是new，说明新会员已经注册过了。无需再次注册。
			if($onemember['is_new_old_member'] == 'new')
			{
				//$tips_message = '对不起，您已注册过，无需重复注册。点击领取<a href="index.php?mod=waxs_login&act=hongbao&type=new" target="_self">新会员专享红包</a>';
				$tips_message = '对不起，您已注册过，无需重复注册。';
				include(template('tips_message'));
				exit();
			}
			else
			{
				//手机号码已存在，并且该会员是old，
				if(!$onemember['password'])
				{
					//尚未有密码，说明该old会员，只是手机号人工录入DB，本次是首次注册补全信息，update DB，最后跳转到老会员红包页。
					$update_data = array(
						'real_name' => $real_name,
						'password' => md5($password),
						'reg_dateline' => TIMESTAMP,
					);
					$this->DatabaseHandler->update('qkdb_member',$update_data, "member_id = '".$onemember['member_id']."'");
					include(template('red_after_reg'));
					//跳转到微商城。
//					header('Location: http://wap.koudaitong.com/v2/feature/p7g7xopx');
					exit();
				}
				else
				{
					//已经有密码了，则该old会员已经补全了信息，无需再次注册。
					//$tips_message = '对不起，您已注册过，无需重复注册。点击领取<a href="index.php?mod=waxs_login&act=hongbao&type=old" target="_self">老会员专享红包</a>';
					$tips_message = '对不起，您已注册过，无需重复注册。';
					include(template('tips_message'));
					exit();
				}
			}
		}

	}

	//红包展示页
	function Hongbao()
	{
		$hongbao_type = getPG('type'); //new代表新会员红包；old代表老会员红包。
		if($hongbao_type == 'new')
		{
			include(template('red_new'));
			exit();
		}
		else if($hongbao_type == 'old')
		{
			include(template('red_old'));
			exit();
		}
		else
		{
			$tips_message = '页面不存在。参数错误。';
			include(template('tips_message'));
			exit();
		}

	}




}

?>