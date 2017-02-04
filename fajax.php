<?php 
/**
 *
 * front ajax入口 ，即前端客户端（包括小程序、H5等）请求ajax的入口
 */
define('ROOT_PATH', dirname(__FILE__).'/');
define('DATA_PATH', dirname(__FILE__).'/data/');
define('PIC_SERVER_PATH', 'http://114.215.202.184/pictures/');
include_once './include/function/global.func.php';
include_once './include/function/httpclient.php';
include_once './include/function/sqlclass.php';
include_once './module/master.mod.php';

//获取全局上下文变量。GLX意为GLobal Context。
$context = Load::logic('base');
function GLX()
{
    return $GLOBALS['context'];
}

date_default_timezone_set('PRC');//设置时区为北京时间，参考http://blog.163.com/tfz_0611_go/blog/static/208497084201322812943907/
error_reporting(0); //关闭错误提示
//ini_set("magic_quotes_runtime", 'off');//关掉该设置，让GPC不要自动加反斜杠，所见即所得


main();

function main()
{
	$mod = isset($_POST['mod'])?$_POST['mod']:$_GET['mod'];
	
	//检查登录信息
	login_info_check();

	if(!defined('MEMBER_ID'))
	{
		define('MEMBER_ID', '');
	}
	if(!defined('MEMBER_EMAIL'))
	{
		define('MEMBER_EMAIL', '');
	}
	if(!defined('MEMBER_NICKNAME'))
	{
		define('MEMBER_NICKNAME', '');
	}
	if(!defined('MEMBER_NUMURL'))
	{
		define('MEMBER_NUMURL', '');
	}
	if(!defined('MEMBER_FACE_M'))
	{
		define('MEMBER_FACE_M', '');
	}
	if(!defined('IS_MEMBER_ADMIN'))
	{
		define('IS_MEMBER_ADMIN', '');
	}
    if(!defined('MEMBER_CELLPHONE'))
    {
        define('MEMBER_CELLPHONE', '');
    }
	if(!defined('IS_CLIENT_XCX'))
	{
		defined('IS_CLIENT_XCX', ''); //客户端是否为小程序
	}
	
	define('TIMESTAMP', time());
	
	//得到店铺名称
	define('SHOP_NAME',getConfKV('shop_name'));//getShopName()可能为空
	//得到前台模板名称
	define('TEMPLATE_NAME',getConfKV('template_name'));
	
	if(!$mod)
	{
		$mod = 'index';
	}
	if( $mod )
	{
		if(is_file('./module/fajax/'.$mod.'.mod.php'))
		{
			include('./module/fajax/'.$mod.'.mod.php');
			$ModuleObject = new ModuleObject();
		}
		else
		{
			echo '';
			exit();
		}
	
	}

}

//登录信息检查，根据是否登录，初始化一些宏定义
function login_info_check()
{
	//fajax是专为小程序写的入口，所以不用判断是否从web browser进入。
	//判断是否带有xcx_session_id 这个变量，如果有，说明是小程序客户端打来的请求。

	define('IS_CLIENT_XCX', 1);
	login_info_check_xcx(); //小程序客户端，检查登录
	return;

//    else
//    {
//        login_info_check_wb(); //浏览器客户端，检查登录
//        return;
//    }
}

//浏览器web browser客户端，检查登录情况
function login_info_check_wb()
{
    //读取cookie，并对cookie解密
    $uidsecret = $_COOKIE["cookiesecret1"];
    $numurlsecret = $_COOKIE["cookiesecret2"];

    if(!$uidsecret || !$numurlsecret)
    {
        //cookie值不完整，说明有的cookie已过期或不存在，即状态是未登录
        return;
    }

    //解密
    $uid = (base64_decode($uidsecret)-2013)/1.5;
    $num_url = base64_decode($numurlsecret);
    $endpos = strrpos($num_url, 'qinke.com');
    if($endpos > 0)
    {
        $num_url = substr($num_url, 0, $endpos);
    }
    else//cookie值不完整，即状态是未登录
    {
        return;
    }

    //从数据库中读出用户的信息，包括uid和昵称
//	$dbhandle = new SqlClass();
//	$dbhandle->connect();

    $sql = "select * from qkdb_member where member_id='".$uid."' and num_url='".$num_url."'";
    $query = GLX()->db->Query($sql);
    $row = GLX()->db->GetRow($query);


    if($row) //用户信息读取成功，也就是说cookie的信息正确
    {
        define('MEMBER_ID', $row['member_id']);
        define('MEMBER_EMAIL', $row['email']);
        define('MEMBER_NICKNAME', $row['nickname']);
        define('MEMBER_NUMURL', $row['num_url']);
        //$row['face_m_img'] = face_path_m($row['num_url']);
        if(!is_file($row['face_m_img']))
        {
            $row['face_m_img'] = 'templates/images/noavatar.gif';
        }
        define('MEMBER_FACE_M', $row['face_m_img']);//用于整站页头上的小头像
        
    }
	else//cookie值不满足条件，即状态是未登录
    {
        return;
    }
}

//小程序客户端，检查登录情况
function login_info_check_xcx()
{
	$xcx_session_id = getPG('xcx_session_id');

	error_log(date('Y-m-d H:i:s ') . __CLASS__ . '::' . __FUNCTION__ . ' @ '.
		'$xcx_session_id: ' . var_export($xcx_session_id, 1) . "\r\n", 3, "data/chutest/CHUTEST-XX.log");
	if(!$xcx_session_id)
	{
		return;
	}

	session_id($xcx_session_id);
	session_start();
	$session_key = $_SESSION['session_key']; //$_SESSION['session_key']微信服务器提供给api调用者的token;
	$openid = $_SESSION['openid']; //$_SESSION['openid']是微信用户唯一标识
	error_log(date('Y-m-d H:i:s ') . __CLASS__ . '::' . __FUNCTION__ . ' @ '.
		'$_SESSION: ' . var_export($_SESSION, 1) . "\r\n", 3, "data/chutest/CHUTEST-XX.log");

	//在小程序客户端登录成功时，php就会存储$_SESSION['client_type']='xcx', $_SESSION['session_key']，$_SESSION['openid'], $_SESSION['member_id']=登录用户的id
	if($_SESSION['member_id'] > 0)
	{
		$sql = "select * from qkdb_member where member_id='".$_SESSION['member_id']."' ";
		$query = GLX()->db->Query($sql);
		$row = GLX()->db->GetRow($query);


		if($row) //会员信息读取成功
		{
			define('MEMBER_ID', $row['member_id']);
			define('MEMBER_CELLPHONE', $row['cellphone']);

			//$row['face_m_img'] = face_path_m($row['num_url']);
			if(!is_file($row['face_m_img']))
			{
				$row['face_m_img'] = 'templates/images/noavatar.gif';
			}
			define('MEMBER_FACE_M', $row['face_m_img']);//用于整站页头上的小头像
			error_log(date('Y-m-d H:i:s ') . __CLASS__ . '::' . __FUNCTION__ . ' @ '.
				'111111: ' . var_export('', 1) . "\r\n", 3, "data/chutest/CHUTEST-XX.log");

            return;
		}
		else//session读取失败，即状态是未登录
		{
			error_log(date('Y-m-d H:i:s ') . __CLASS__ . '::' . __FUNCTION__ . ' @ '.
				'222222: ' . var_export('', 1) . "\r\n", 3, "data/chutest/CHUTEST-XX.log");

			return;
		}
	}
    else //session读取失败。
    {
		error_log(date('Y-m-d H:i:s ') . __CLASS__ . '::' . __FUNCTION__ . ' @ '.
			'333333: ' . var_export('', 1) . "\r\n", 3, "data/chutest/CHUTEST-XX.log");

		return;
    }

	
}
?>
