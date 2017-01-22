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
			case 'error_tip': //也是登录界面，不过带有错误提示
				$this->Error_tip();
				break;
				
			case 'dologin':
				$this->Dologin();
				break;
				
			case 'dologout':
				$this->DoLogout();
				break;
					
			default:
				$this->Login();
				break;
		}

	}
	
	
	function Login()
	{
		//用户未登录后台。
		$page_title="后台管理登录";
		
		if(MEMBER_ID>0 && IS_MEMBER_ADMIN>0)//说明用户已登录，并且是管理员身份，这时不能进入登录页
		{
			//跳回到后台管理系统首页
			header('Location: admin.php');
			exit();
		}
		
		//店铺名称在page_header.html中引用。在分配器中宏定义过了。
		

		$redirect = stripslashes(trim(getPG("redirect")));//登录成功后重定向的目标url
		
		include(template('login','admin'));
	}
	
	function Error_tip()
	{
		$page_title="后台管理登录";
		
		if(MEMBER_ID>0 && IS_MEMBER_ADMIN>0)//说明用户已登录，并且是管理员身份，这时不能进入登录页
		{
			//跳回到后台管理系统首页
			header('Location: admin.php');
			exit();
		}
		
		$errortype = getPG('errortype');
		$ua = stripslashes(getPG('ua')); //user account账户（可以是邮箱、手机、昵称）
		$errortip='';
		
		
		
		if($errortype == 1)//账号为空
		{
			$errortip = '账号不能为空';
		}
		elseif($errortype == 2)//密码为空
		{
			$errortip = '密码不能为空';
		}
		elseif($errortype == 3)//账号、密码验证失败
		{
			$errortip = '账号或密码错误';
		}
		elseif($errortype == 4)//该账号不是管理员身份
		{
			$errortip = '账号不是管理员';
		}
		
		include(template('login','admin'));	
	}
	
	function DoLogin()
	{
		if(MEMBER_ID>0 && IS_MEMBER_ADMIN>0)//说明用户已登录，并且是管理员身份，这时不能进入登录页
		{
			//跳回到后台管理系统首页
			header('Location: admin.php');
			exit();
		}
		
		$user_account = $_POST['ua'];//账号，可能是：邮箱、手机号、昵称
		//$user_account_noslashes = stripslashes($user_account);//这一步在这里其实没用，因为邮箱、手机号、昵称都不可能带反斜杠。
		$pwd = $_POST['pwd'];//密码千万不要trim，因为空格也是密码
		$autologin = $_POST['autologin']; //是否自动登录
		$redirec = stripslashes(trim($_POST['redirect']));//登录后重定向的目标url
		
		$errortype=''; //1代表账号为空，2代表密码为空，3代表邮箱或密码出错。
		if(!$user_account) //账号为空
		{
			$errortype = 1;
			header('Location: admin.php?mod=login&act=error_tip&errortype='.$errortype);
			exit();
		}
		else if(!$pwd) //密码为空
		{
			$errortype = 2;
			header('Location: admin.php?mod=login&act=error_tip&errortype='.$errortype.'&ua='.$user_account);
			exit();
		}
		
		//判断用户登录账号用的是哪一种，邮箱？手机号？昵称？
		if(is_numeric($user_account))
		{
			//纯数字，手机号
			$check_sql = " cellphone = '".$user_account."'"; 
		}
		else if(strpos($user_account,'@') != false)
		{
			//账号里包含@，说明是邮箱
			$check_sql = " email = '".$user_account."'";
		}
		else
		{
			//昵称
			$check_sql = " nickname = '".$user_account."'";
		}
		
		$sql = "select * from qkdb_user where ".$check_sql." and password='".md5($pwd)."'";
		$query = $this->DatabaseHandler->Query($sql);
		$row = $this->DatabaseHandler->GetRow($query);
		
		if($row)//账号，密码验证成功
		{
			if(!is_admin($row['uid']))//不是管理员身份
			{
				$errortype = 4; //账号密码正确，但用户不是管理员。
				header('Location: admin.php?mod=login&act=error_tip&errortype='.$errortype);
				exit();
			}
			
			//后台管理系统登录成功，写cookie
			write_cookie($row,$autologin);

			//登录成功后要更新user表的lastlogin_dateline字段
			$update_data = array(
					'lastlogin_dateline' => TIMESTAMP,
			);
			$this->DatabaseHandler->update('qkdb_user', $update_data, "uid = '".$row['uid']."'");
			
			//登录成功后跳转
			if($redirec)//如果有重定向的目标url
			{
				$redirec = 'Location: '.$redirec;
				//header('Location: http://www.baidu.com');
				header( $redirec );
				exit();
			}
			else //没有重定向，跳到后台管理首页
			{
				header( 'Location: admin.php' );
				exit();
			}
		}
		else
		{
			$errortype = 3; //账号或密码错误。
			header('Location: admin.php?mod=login&act=error_tip&errortype='.$errortype);
			exit();
		}
		
	}
	
	function DoLogout()
	{
		//参考http://blog.sina.com.cn/s/blog_865ef5610101ehht.html上删除cookie的方法，将value设为空，将有效期设为当前时间减1。
		setcookie("cookiesecret1", "", time()-1);
		setcookie("cookiesecret2", "", time()-1);
	
		//删除一切session，在管理员登录后台时有可能会产生session
		session_start();
		session_unset();
		session_destroy();
	
		//退出后跳转到首页
		//$conf = getSetting("sys_setting");
		//header( 'Location: '.$conf['site_url']);
		header( 'Location: admin.php');
		exit();
	}
}
?>