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
			case 'login':
				$this->Login(); //登录页面
				break;

			case 'dologin':
				$this->DoLogin();  //登录动作
				break;
				
			case 'dologout':
				$this->DoLogout(); //退出登录动作
				break;

			default:
				$this->Login(); //登录的页面
				break;
		}

	}


	//登录的页面
	function Login()
	{
		if(MEMBER_ID>0)//说明用户已登录，已登录时不能进入登录页
		{
		 	//跳回到首页。本项目就是提货申请页
		 	header('Location: index.php?mod=tihuo_apply');
		 	exit();
		}

		//未登录，才显示登录页
        $page_title = '会员登录';
		include(template('login'));
	}


	//退出登录动作。
	function DoLogout()
	{
		//参考http://blog.sina.com.cn/s/blog_865ef5610101ehht.html上删除cookie的方法，将value设为空，将有效期设为当前时间减1。
		setcookie("cookiesecret1", "", time()-1);
//		setcookie("cookiesecret2", "", time()-1);

		//删除一切session，在管理员登录后台时有可能会产生session
		session_start();
		session_unset();
		session_destroy();

		//退出后跳转到首页
		//$conf = getSetting("sys_setting");
		//header( 'Location: '.$conf['site_url']);
		header( 'Location: index.php?mod=login');
		exit();
	}

	//登录的动作。返回json
	function DoLogin()
	{
		if(MEMBER_ID>0)//说明用户已登录，已登录时不能再登录。
		{
			//跳回到首页。本项目就是提货申请页
			header('Location: index.php?mod=tihuo_apply');
			exit();
		}

		//前端传来手机号+密码（还有可能传来 是否自动登录、登录成功后的重定向url）
		$cellphone = getPG('mobile');
		$password = getPG('password');

        $redirec = getPG('redirec');
        $autologin = getPG('autologin');

		if(!$cellphone || !$password)
		{
			json_error('手机号、密码不能为空','40013');
		}

		//判断该手机号+密码是否存在数据库中
		$sql = "select * from qkdb_member where cellphone = '".addslashes($cellphone)."' and password = '".md5($password)."' "; //因为$cellphone是用户填的，所以需要防卡sql注入，加反斜杠。
		$query = $this->DatabaseHandler->Query($sql);
		$onemember = $this->DatabaseHandler->GetRow($query);
		if($onemember)
		{
			//手机号+密码验证成功
			//后台管理系统登录成功，写cookie。$autologin代表是否自动登录。
			write_cookie($onemember,$autologin);

			//登录成功后要更新member表的lastlogin_dateline字段
			$update_data = array(
				'lastlogin_dateline' => TIMESTAMP,
			);
			$this->DatabaseHandler->update('qkdb_member', $update_data, "member_id = '".$onemember['member_id']."'");

			//登录成功后跳转
			if($redirec)//如果有重定向的目标url
			{
                json_result($redirec);
			}
			else //没有重定向，跳到提货卡页面
			{
				json_result();
			}

		}
        else
        {
            //登录失败，用户名或密码错误
            json_error('手机号或密码错误，请重新输入','40013');
        }


	}








	//忘记密码
	function Forget_password()
	{
		$this->Messager('找回密码，请联系食令客服: 021-52912877');
	}
	
	
	
	
}
?>