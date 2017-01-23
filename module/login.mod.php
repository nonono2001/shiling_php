<?php

class ModuleObject extends MasterObject
{
	public $appid;
	public $secret;
	function ModuleObject()//构造函数
	{
		$config = getSetting( 'sys_setting' );

		$this->appid = $config['appid'];
		$this->secret = $config['appsecret'];
		$this->MasterObject( 'db_on' );
		
		
	
		$this->Execute();
		
	}
	
	
	function Execute()
	{
		switch($this->Act)
		{
			case 'login': //透明附盖层，可以页面上实现一步一步的教程。参考http://www.paishi.com/，并且实现网页背景图片固定，不随着滚动条下拉而移动。
				$this->Login();
				break;
				
			case 'error_tip': //也是登录界面，不过带有错误提示
				$this->Error_tip();
				break;
		
			case 'dologin':
				$this->DoLogin();
				break;
				
			case 'dologout':
				$this->DoLogout();
				break;
				
			case 'reg':
				$this->Reg();
				break;
				
			case 'doreg':
				$this->Doreg();
				break;
				
			case 'dialog':
				$this->Dialog();
				break;
			
			case 'forget_password':  //已废
				$this->Forget_password();
				break;
					
			default:
				$this->Login();
				break;
		}

	}
	
	function Common()
	{
		echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">缺少参数......';
	}
	
	function Login()
	{
//		$query = $this->DatabaseHandler->Query("select * from jishigou_xyz where id = 81");
//		$row = mysql_fetch_array($query);
//		echo $row['name'];
//		include(template('login'));
		
		
		// if(MEMBER_ID>0)//说明用户已登录，已登录时不能进入登录页
		// {
		// 	//跳回到首页
		// 	header('Location: index.php');
		// 	exit();
		// }
		// $page_title="登录";
		// $redirect = getPG("redirect");//登录成功后重定向的目标url
		// include(template('login'));
		$code = $_POST['code'];
		$session = $this->get_key($code);
		var_dump($session);
	}
	
	function get_key($code){
		$url = 'https://api.weixin.qq.com/sns/jscode2session';
    	$params  = array(
    		'appid'=>$this->appid,
    		'secret'=>$this->secret,
    		'js_code'=>$code,
    		'grant_type'=>'authorization_code'

    		);
    	return $this->http($url,$params,'POST');
	}

	function Error_tip()
	{
		$page_title="登录";
		$errortype = getPG('errortype');
		$email = stripslashes(getPG('email')); 
		$errortip='';
		
		if($errortype == 1)//邮箱为空
		{
			$errortip = '账户不能为空';
		}
		elseif($errortype == 2)//密码为空
		{
			$errortip = '密码不能为空';
		}
		elseif($errortype == 3)//邮箱、密码验证成功
		{
			$errortip = '账户或密码错误';
		}
		include(template('login'));	
	}
	
	function DoLogin()
	{
		$email = $_POST['email'];
		$email_noslashes = stripslashes($email);
		$pwd = $_POST['pwd'];//密码千万不要trim，空格也是密码字符
		$autologin = $_POST['autologin']; //自动登录
		$redirec = stripslashes(trim($_POST['redirect']));//登录后重定向的目标url
		
		$errortype='';
		if(!$email) //邮箱为空
		{
			$errortype = 1;
			header('Location: index.php?mod=login&code=error_tip&errortype='.$errortype);
			exit();
		}
		elseif(!$pwd) //密码为空
		{
			$errortype = 2;
			header('Location: index.php?mod=login&code=error_tip&errortype='.$errortype.'&email='.$email_noslashes);
			exit();
		}
		
		$sql = "select * from user where email='".$email."' and password='".md5($pwd)."'";
		$query = $this->DatabaseHandler->Query($sql);
		$row = $this->DatabaseHandler->GetRow($query);
		
		if($row)//邮箱、密码验证成功，登录成功
		{
			write_cookie($row,$autologin);
			
			//登录成功后要更新user表的lastlogin_dateline字段
			$update_data = array(
					'lastlogin_dateline' => TIMESTAMP,
			);
			$this->DatabaseHandler->update('user', $update_data, "uid = '".$row['uid']."'");
			
			
// 			//写session信息
// 			session_start();
// 			$_SESSION['email_account'] = $email;
// 			$_SESSION['isLoginOk'] = 'OK';
// 			$_SESSION['lastLoginTime'] = time();
			
			
			//登录成功后跳转
			if($redirec)//如果有重定向的目标url
			{
				$redirec = 'Location: '.$redirec;
				//header('Location: http://www.baidu.com');
				header( $redirec );
				exit();
			}
			else //没有重定向，跳到我的格子铺
			{
				
				header( 'Location: index.php?mod=gzp&code='.$row['num_url']);
				exit();
			}
			
			
		}
		else
		{
			$errortype = 3; //邮箱或密码错误。
			header('Location: index.php?mod=login&code=error_tip&errortype='.$errortype.'&email='.$email_noslashes);
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
		header( 'Location: index.php');
		exit();
	}
	
	function Reg()
	{
		if(MEMBER_ID>0)//说明用户已登录，已登录时不能进入注册页
		{
			//跳回到首页
			header('Location: index.php');
			exit();
		}
		
		$page_title="注册";
	
		include(template('reg'));
	}
	
	//实际上是用ajax注册的，所以这里没内容
	function Doreg()
	{
	
	}
	
	//快速登录弹出层。
	function Dialog()
	{
		
		
		include(template('login_dialog'));		
	}
	
	//忘记密码
	function Forget_password()
	{
		$this->Messager('找回密码，请联系管理员！email: admin@sgezi.com');
	}
	
	
	
	
}
?>