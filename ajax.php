<?php 
/**
 *
 * ajax入口
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
//error_reporting(0); //关闭错误提示,不管参数是几，0也好，1也好，都起到关闭作用。
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
		define('IS_CLIENT_XCX', ''); //客户端是否为小程序
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
		if(is_file('./module/ajax/'.$mod.'.mod.php'))
		{
			include('./module/ajax/'.$mod.'.mod.php');
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
	login_info_check_wb(); //浏览器客户端，检查登录。index.php永远是浏览器登录。
	return;
}

//浏览器web browser客户端，检查登录情况
function login_info_check_wb()
{
	//读取cookie，并对cookie解密
	$uidsecret = $_COOKIE["cookiesecret1"];
//	$numurlsecret = $_COOKIE["cookiesecret2"];

//	if(!$uidsecret || !$numurlsecret)
	if(!$uidsecret)
	{
		//cookie值不完整，说明有的cookie已过期或不存在，即状态是未登录
		return;
	}

	//解密
	$uid = (base64_decode($uidsecret)-2013)/1.5;
//	$num_url = base64_decode($numurlsecret);
//	$endpos = strrpos($num_url, 'qinke.com');
//	if($endpos > 0)
//	{
//		$num_url = substr($num_url, 0, $endpos);
//	}
//	else//cookie值不完整，即状态是未登录
//	{
//		return;
//	}

	//从数据库中读出用户的信息，包括uid和昵称
//	$dbhandle = new SqlClass();
//	$dbhandle->connect();

	$sql = "select * from qkdb_user where uid='".$uid."'";
	$query = GLX()->db->Query($sql);
	$row = GLX()->db->GetRow($query);


	if($row) //用户信息读取成功，也就是说cookie的信息正确
	{
		define('MEMBER_ID', $row['member_id']);
		define('MEMBER_CELLPHONE', $row['cellphone']);
		define('MEMBER_EMAIL', $row['email']);
		define('MEMBER_NICKNAME', $row['nickname']);
//		define('MEMBER_NUMURL', $row['num_url']);
		//$row['face_m_img'] = face_path_m($row['num_url']);
		if(!is_file($row['face_m_img']))
		{
			$row['face_m_img'] = 'templates/images/noavatar.gif';
		}
		define('MEMBER_FACE_M', $row['face_m_img']);//用于整站页头上的小头像

		if(is_admin($row['uid']))//用于整站判断是否是管理员。
		{
			define('IS_MEMBER_ADMIN', 1);
		}
		else
		{
			define('IS_MEMBER_ADMIN', '');
		}
	}
	else//cookie值不满足条件，即状态是未登录
	{
		return;
	}
}

?>
