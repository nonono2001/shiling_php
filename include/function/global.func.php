<?php 

function template_old($tpl_name = null)
{
	if(empty($tpl_name)) {
	//	Messager('未指定模板名称');
		echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">未指定模板名称';
		exit();
	}
	$filepath = './templates/'.$tpl_name.'.html';
	if( file_exists($filepath) )
	{
		return $filepath;
	}
	else 
	{
	//	Messager('指定的模板'.$filepath.'不存在，无法渲染网页');
		echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">指定的模板'.$filepath.'不存在，无法渲染网页';
		exit();
	}
}

//得到配置文件内的数据。
function getSetting($setname='sys_setting')
{
	if(!$setname)
	{
		$setname = 'sys_setting';
	}
	return require('./include/setting/'.$setname.'.php');

}

//判断一个uid是否是管理员
function is_admin($uid)
{
	$sysconf = getSetting();//得到系统配置，里面有管理员的id
	$adminuidarr = $sysconf['admin_uid'];
	if(in_array($uid, $adminuidarr))
	{
		return true;
	}
	else 
	{
		return false;
	}
}

//$method的值有三种：G（代表GET）、P（代表POST）、GP（代表两种）
function getPG($key,$method='GP')
{
	if( $method == 'P')
	{
		return $_POST[$key];
	}
	elseif( $method == 'G')
	{
		return $_GET[$key];
	}
	else
	{
		return isset($_POST[$key])?$_POST[$key]:$_GET[$key];
	}
}

function worklog($log)
{	
 	error_log($log."\r\n", 3, "WORKLOG.log");
}

function worklog2($data) {
    $flag = 0;
    if (is_array($data)) {
    	foreach ($data as $key=>$value) { 
			if ($flag!=0) { 
				$params .= "&"; 
				$flag = 1; 
			} 
			$params.= $key."="; $params.=$value;
			$flag = 1; 
		} 
    }else{
    	$params = $data;
    }
    
    $fn = ROOT_PATH ."sms.log"; 
    $fp = fopen($fn, "a"); 
    $lock = flock($fp, LOCK_EX);
	if ($lock) { 
		 fseek($fp, 0, SEEK_END); 
		 fwrite($fp, $params.";\r\n"); 
		 flock($fp, LOCK_UN); 
	}
	fclose($fp); 
}

//异常记录日志。代码中有些分支一般情况下是走不到的，为以防万一的，除非黑客修改前台或者特殊手段等。
//记录下这种情况，以防万一。
function exception_log($log)
{
	$time = date("Y-m-d H:i:s",time());
	error_log($time."   ".$log."\r\n\r\n", 3, "EXCEPTIONLOG.log");
}

//$type：模板类型，admin代表后台管理系统模板；shop代表前台店铺模板
//$name: 模板名称
function template($name,$type = 'shop')
{
	if($type == 'admin')
	{
		$tpl = "templates/admin/$name";
	}
	else {
		//得到当前模板名称
		$current_shop_template = getConfKV('template_name');
		if(!$current_shop_template)
		{
			exit("current shop template name is empty!");
		}
		$tpl = 'templates/'.$current_shop_template.'/'.$name;
	}
	
	$objfile = ROOT_PATH . './data/cache/tpl_cache/' . str_replace('/', '_', $tpl) . '.php';
	
	
	if (!file_exists($objfile)) {
		include_once(ROOT_PATH . './include/function/function_template.php');
		parse_template($tpl,$type);
	}
	
	return $objfile;
}

//sqlclass.php中会用到该函数
function jimplode($array)
{
	if(!empty($array)) {
		return "'".implode("','", is_array($array) ? $array : array($array))."'";
	} else {
		return 0;
	}
}

//计算limit，用于sql分页
//返回值是一个字符串，类似于“limit 10,10”
function page_limit($total_record, $per_page_num, $topage, $query_link)
{
	
	$retarr = array();
	
	if($topage<=0 || !$topage)
	{
		$topage = 1;
	}
	
	$firstnum = ($topage-1)*$per_page_num;
	$limit = " LIMIT ".$firstnum." , ".$per_page_num;
	$retarr['limit'] = $limit;
	
	//下面计算页码信息
	if($total_record <= 0 || !$total_record)
	{
		//说明一个记录都没有。
		return $retarr;
	}
	$lastpage =  ceil($total_record/$per_page_num);//最后一页的页码
	$firstpage = 1;
	
	if($topage > $lastpage || $topage < $firstpage)
	{
		//说明一个记录都没有。
		return $retarr;
	}

	$hasleftetc = true;
	
	
	
	//上一页
	if($topage == 1) //当前页是第一页，上一页按钮灰掉
	{
		$retarr['page']['prepage']['href'] = '';
		$retarr['page']['prepage']['type'] = 'prepage';
		$retarr['page']['prepage']['page_num'] = '';
	
		$hasleftetc = false;//不需要左省略号
	}
	else//上一页按钮亮起
	{
		$retarr['page']['prepage']['href'] = $query_link."&page=".($topage-1);
		$retarr['page']['prepage']['type'] = 'prepage';
		$retarr['page']['prepage']['page_num'] = $topage-1;
	}
	if($topage-2<=1)
	{
		//不需要左省略号
		$hasleftetc = false;
	}
	//“上一页”右边的1...
	if($hasleftetc) //左省略号
	{
		$retarr['page']['1']['href'] = $query_link."&page=1";
		$retarr['page']['1']['type'] = 'pagenum';
		$retarr['page']['1']['page_num'] = 1;
	
		$retarr['page']['leftetc']['href'] = '';
		$retarr['page']['leftetc']['type'] = 'etc';
		$retarr['page']['leftetc']['page_num'] = '';
	}
	
	//当前页左边的页码
	for($i=2; $i>=1; $i--)
	{
		$leftpage = $topage-$i;
		if( $leftpage <= 0 )
		{
			continue;
		}
		
		$retarr['page'][$leftpage]['href'] = $query_link."&page=".$leftpage;
		$retarr['page'][$leftpage]['type'] = 'pagenum';
		$retarr['page'][$leftpage]['page_num'] = $leftpage;
	}
	
	$retarr['page'][$topage]['href'] = '';//当前页，链接为空。
	$retarr['page'][$topage]['type'] = 'currentpage';
	$retarr['page'][$topage]['page_num'] = $topage;
	
	//当前页右边的页码
	for($i=1; $i<=2; $i++)
	{
		$rightpage = $topage+$i;
		if( $rightpage > $lastpage )
		{
			break;
		}
		
		$retarr['page'][$rightpage]['href'] = $query_link."&page=".$rightpage;
		$retarr['page'][$rightpage]['type'] = 'pagenum';
		$retarr['page'][$rightpage]['page_num'] = $rightpage;
	}
	
	$hasrightetc = true;
	
	if($topage+2 >= $lastpage)
	{
		$hasrightetc = false;
	}
	
	//“下一页”左边的...32（32只是个例子，代表最后一页的页数）
	if($hasrightetc) //右省略号
	{
		$retarr['page']['rightetc']['href'] = '';
		$retarr['page']['rightetc']['type'] = 'etc';
		$retarr['page']['rightetc']['page_num'] = '';
		
		$retarr['page'][$lastpage]['href'] = $query_link."&page=".$lastpage;
		$retarr['page'][$lastpage]['type'] = 'pagenum';
		$retarr['page'][$lastpage]['page_num'] = $lastpage;
	}
	//下一页
	if($topage == $lastpage) //当前页是最后一页，下一页灰掉
	{
		$retarr['page']['nextpage']['href'] = '';
		$retarr['page']['nextpage']['type'] = 'nextpage';
		$retarr['page']['nextpage']['page_num'] = '';
	}
	else
	{
		$retarr['page']['nextpage']['href'] = $query_link."&page=".($topage+1);
		$retarr['page']['nextpage']['type'] = 'nextpage';
		$retarr['page']['nextpage']['page_num'] = $topage+1;
	}

	return $retarr;
}


//随机产生一个数字域名编号，数字域名编号，就是一个用户注册后会给TA分配一个编号，这串编号通常出现在个人的域名中，称为数字域名。
function generate_num_url()
{
//	include_once(ROOT_PATH . './include/function/sqlclass.php');
//	$db = new SqlClass();
//
//	$db->connect();

	$db = GLX()->db;
	
	$maxtry = 500;//试验最大次数，超过这个次数，那么这次的生成随机数就会失败。
	$trytimes = 0;//试着去得到随机数的次数。能用的随机数是需要满足一定条件的，所以不是每次得到的随机数都合法。
	while(1)
	{
		$r = rand(50000000,59999999);//一千万个数字中随机选一个
		//这个随机数需满足两个条件：1未被占有；2不属于保留的靓号
		
		//检查随机数是否属于保留靓号
		$sql = "select * from baoliu_num_url where num_url = '".$r."'";
		$query = $db->Query($sql);
		$row = $db->GetRow($query);
		if($row)//说明$r属于保留靓号。
		{
			$trytimes++;
			if($trytimes>=500)
			{
				
				return 0;//返回0表示随机数产生失败。
			}
			continue;
			
		}
		
		//说明在保留靓号里没有找到这个$r。
		//再检查随机数是否已经被使用。
		$sql2 = "select * from user where num_url = '".$r."'";
		$query2 = $db->Query($sql2);
		$row2 = $db->GetRow($query2);
		if($row2)//说明$r已经被占用
		{
			$trytimes++;
			if($trytimes>=500)
			{
				
				return 0;//返回0表示随机数产生失败。
			}
			continue;
		}
		
		
		return (string)$r;
	}
	
}

//加载类，用于加载特定的类文件，并构造一个实例对象。
class Load {
	static function functions($name) {
		return @include_once(ROOT_PATH . 'include/function/' .$name.'.func.php');
	}
	static function logic($name) {
		
		static $S_logics = array();
		if(is_null($S_logics[$name])) {
			$class_name = ucfirst($name);
			
			$class_name .= 'Logic';
			if(!(@include_once ROOT_PATH . 'include/logic/' . $name . '.logic.php') && !class_exists($class_name)) {
				exit('logic ' . $name . ' is not exists');
			}
			$S_logics[$name] = new $class_name();
		}
		return $S_logics[$name];
		
	}
	
	
	
}

//整站唯一的写cookie的代码
//读cookie的地方较多，比如：index.php里login_info_check函数
//参数：$row，是一个user信息的数组，$autologin代表是否“下次自动登录”
function write_cookie($row,$autologin)
{
	//网站整体主要用cookie保存登录信息。
	//setcookie()中，如果不写时间，那么它的到期时间就是在浏览器关闭之后
	//cookie的加密，当然，这个公式越复杂就越难破解
	$fac = $row['uid']*1.5+2013;
	$uidsecret = base64_encode($fac);
		
	$fac = $row['num_url']."qinke.com";
	$numurlsecret = base64_encode($fac);
	
	if($autologin == 1)
	{
		//自动登录，写cookie 的有效期都要设为30天。
		setcookie("cookiesecret1", $uidsecret, TIMESTAMP+2592000);//30天内自动登录，30天cookie过期，2592000单位是秒
		setcookie("cookiesecret2", $numurlsecret, TIMESTAMP+2592000);
	}
	else
	{
		setcookie("cookiesecret1", $uidsecret);
		setcookie("cookiesecret2", $numurlsecret);
	}
	
}


//生成第三方session。第三方是相对于微信服务器来说的。
//全站唯一为小程序写session的函数。调用它，一般是在登录功能。
function gen_3rd_session($session_value)
{
	session_start();
	$_SESSION['xcx_session_key']=$session_value['session_key'];
	$_SESSION['openid'] = $session_value['openid'];
	$_SESSION['member_id'] = $session_value['member_id'];
	$_SESSION['client_type'] = 'xcx';
	return session_id();
}

//用小程序客户端的code换取session_key和openid
function code_to_sessionkey_openid($xcx_code)
{
	$config = getSetting( 'sys_setting' );

	$appid = $config['appid']; //微信服务器提供给调用者的appid和appsecret。
	$secret = $config['appsecret'];

	$url = 'https://api.weixin.qq.com/sns/jscode2session';
	$params  = array(
		'appid'=>$appid,
		'secret'=>$secret,
		'js_code'=>$xcx_code,
		'grant_type'=>'authorization_code'

	);

	$sessionkey_openid = send_post_url($url,$params);
	/*$sessionkey_openid
    正常返回的JSON数据包
    {
        "openid": "OPENID",
          "session_key": "SESSIONKEY"
    }
    错误时返回JSON数据包(示例为Code无效)
    {
        "errcode": 40029,
        "errmsg": "invalid code"
    }
    */

	return $sessionkey_openid;
}

//得到一个商品图片的路径，若路径不存在，则创建它。
//成功返回路径，失败返回空。
function proimg_path($image_id)
{
	if($image_id<=0 || !$image_id)
	{
		return '';
	}
	
	$path = 'images/product/' . 'product_'.$image_id.'/';
	
	if(!is_dir($path))
	{
		mkdir($path,0777,true);
	}
	if(!is_dir($path))
	{
		return '';
	}
	else 
	{
		return $path;
	}
	
}

//得到一个商品大图的文件路径（注意是文件路径，不是文件夹路径）
function proimg_path_b($image_id)
{	
	return proimg_path($image_id).$image_id.'_b.jpg';
}
//得到一个商品中图的文件路径（注意是文件路径，不是文件夹路径）
function proimg_path_m($image_id)
{	
	return proimg_path($image_id).$image_id.'_m.jpg';
}
//得到一个商品小图的文件路径（注意是文件路径，不是文件夹路径）
function proimg_path_s($image_id)
{
	return proimg_path($image_id).$image_id.'_s.jpg';
}
//得到一个商品等比缩放图的文件路径（注意是文件路径，不是文件夹路径）
function proimg_path_w($image_id)
{
	return proimg_path($image_id).$image_id.'_w.jpg';
}

//得到一个网站的logo路径
function webimg_logo_path($wid)
{
	$path = 'images/webimg/logo/'.$wid.'.jpg';
	return $path;
}

//得到一个网站的ico路径
function webimg_ico_path($wid)
{
	$path = 'images/webimg/ico/'.$wid.'.ico';
	return $path;
}


//删除一个商品图片所在的文件夹。
//成功返回true，失败返回false
function proimg_del_dir($image_id)
{
	if($image_id<=0 || !$image_id)
	{
		return false;
	}
	
	$path = proimg_path($image_id);
	
	return deldir($path);
}

//删除一个文件夹（空或非空都可以）。参考：http://www.nowamagic.net/librarys/veda/detail/1432
function deldir($dir)
{
    //先删除目录下的文件：
    $dh=opendir($dir);
    $file = '';
    while ($file=readdir($dh))
    {
	    if($file!="." && $file!="..")
	    {
	        $fullpath=$dir."/".$file;
	        if(!is_dir($fullpath))
	        {
	            unlink($fullpath);
	        }
	        else
	        {
	            deldir($fullpath);
	        }
	    }
    }
  
    closedir($dh);
    //删除当前文件夹：
    if(rmdir($dir)) {
      return true;
    } else {
      return false;
    }
}

//得到一个普通贴子（追加心得、网友讨论、买后点评）的图片的路径，若路径不存在，则创建它。
//成功返回路径，失败返回空。
function topicimg_path($image_id)
{
	if($image_id<=0 || !$image_id)
	{
		return '';
	}

	$path = 'images/topic/' . 'topic_'.$image_id.'/';

	if(!is_dir($path))
	{
		mkdir($path,0777,true);
	}
	if(!is_dir($path))
	{
		return '';
	}
	else
	{
		return $path;
	}

}

//得到一个普通贴子大图的文件路径（注意是文件路径，不是文件夹路径）
function topicimg_path_b($image_id)
{
	return topicimg_path($image_id).$image_id.'_b.jpg';
}
//得到一个普通贴子中图的文件路径（注意是文件路径，不是文件夹路径）
function topicimg_path_m($image_id)
{
	return topicimg_path($image_id).$image_id.'_m.jpg';
}
//得到一个普通贴子小图的文件路径（注意是文件路径，不是文件夹路径）
function topicimg_path_s($image_id)
{
	return topicimg_path($image_id).$image_id.'_s.jpg';
}
//得到一个普通贴子等比缩放图的文件路径（注意是文件路径，不是文件夹路径）
function topicimg_path_w($image_id)
{
	return topicimg_path($image_id).$image_id.'_w.jpg';
}

//删除一个普通贴子的图片所在的文件夹。
//成功返回true，失败返回false
function topicimg_del_dir($image_id)
{
	if($image_id<=0 || !$image_id)
	{
		return false;
	}

	$path = topicimg_path($image_id);

	return deldir($path);
}

//头像图片路径，若路径不存在，则创建它。（只是文件夹路径，不是具体文件）
function face_path($numurl)
{
	if($numurl<=0 || !$numurl)
	{
		return false;
	}
	
	$path = 'images/user/' . 'user_'.$numurl.'/face/';
	
	if(!is_dir($path))
	{
		mkdir($path,0777,true);
	}
	if(!is_dir($path))
	{
		return '';
	}
	else
	{
		return $path;
	}
}

//得到头像图片路径，150*150的大图。
function face_path_b($numurl)
{
	return face_path($numurl).'face_'.$numurl.'_b.jpg';
	
}
//得到头像图片路径，50*50的中图。
function face_path_m($numurl)
{
	return face_path($numurl).'face_'.$numurl.'_m.jpg';
}

//海报图片路径，若路径不存在，则创建它。（只是文件夹路径，不是具体文件）
function poster_path($numurl)
{
	if($numurl<=0 || !$numurl)
	{
		return false;
	}

	$path = 'images/user/' . 'user_'.$numurl.'/poster/';

	if(!is_dir($path))
	{
		mkdir($path,0777,true);
	}
	if(!is_dir($path))
	{
		return '';
	}
	else
	{
		return $path;
	}
}

//得到海报图片路径。
function poster_path_b($numurl)
{
	return poster_path($numurl).'poster_'.$numurl.'_b.jpg';
	
}

function poster_path_s($numurl)
{
	return poster_path($numurl).'poster_'.$numurl.'_s.jpg';
}

//服务器端响应ajax时返回json格式的数据
function json_header()
{
	ob_clean();

	@header("Cache-Control: no-cache, must-revalidate");
	@header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
}

function json_result($msg = '', $retval = '')
{
	json_header();
	$json = json_encode(array("done" => true , "msg" => $msg , "retval" => $retval));
	echo $json;
	exit;
}

function json_error($msg = '', $retval = '')
{
	json_header();
	$json = json_encode(array("done" => false , "msg" => $msg , "retval" => $retval));
	
	echo $json;
	exit;
}


/**
 * 根据url（可以不包含http前缀）获取域名信息
 * 【0】完整域名；【1】一级域名；【2】二级域名
 * 三者可能相同，视情况而定
 * @param $url
 */
function getdomain($url){
	if(!$url){
		return false;
	}
	$url = strtolower($url);
	$url = str_replace('http://','',$url);
	$url = str_replace('https://','',$url);
	$domains = explode('/',$url);

	$result = array();
	$result[0] = $domains[0];

	$domains2 = explode('.',$domains[0]);
	$num = count($domains2);
	if($num == 1){
		$result[1] = $domains2[0];
		$result[2] = $domains2[0];
	}else if($num == 2){
		$result[1] = $domains2[0].'.'.$domains2[1];
		$result[2] = $domains2[0].'.'.$domains2[1];
	}else{
		$validate = $domains2[$num-2].'.'.$domains2[$num-1];
		if($validate == 'com.cn' || $validate == 'gov.cn' || $validate == 'net.cn' || $validate == 'org.cn'){
			$result[1] = $domains2[$num-3].'.'.$domains2[$num-2].'.'.$domains2[$num-1];
			if($num == 3){
				$result[2] = $domains2[$num-3].'.'.$domains2[$num-2].'.'.$domains2[$num-1];
			}else{
				$result[2] = $domains2[$num-4].'.'.$domains2[$num-3].'.'.$domains2[$num-2].'.'.$domains2[$num-1];
			}
		}else{
			$result[1] = $domains2[$num-2].'.'.$domains2[$num-1];
			$result[2] = $domains2[$num-3].'.'.$domains2[$num-2].'.'.$domains2[$num-1];
		}
	}

	return $result;
}

//根据一个url得到web_domain表相应的记录，找到这个url属于哪个域名，同时得到wid。
//参数url不需要带有反斜杠。本函数里会加反斜杠。
//成功返回sgzdb_web_domain表的一条记录，失败返回false。
function getdomaininfobyurl($url)
{
	if(!$url){
		return false;
	}
	$url = strtolower($url);
	$domains = getdomain($url);
	
//	$dbhandle = new SqlClass();
//	$dbhandle->connect();

	$dbhandle = GLX()->db;
	
	if($domains[1] == $domains[2]){
		$sql = "select * from sgzdb_web_domain where domain = '".addslashes($domains[1])."'";
		$query = $dbhandle->Query($sql);
		$info = $dbhandle->GetRow($query);
		if($info){
			return $info;
		}
		else {
			return false;
		}
	}else{
		$sql = "select * from sgzdb_web_domain where domain = '".addslashes($domains[2])."'";
		$query = $dbhandle->Query($sql);
		$info = $dbhandle->GetRow($query);
		if($info){
			return $info;
		}else{
			$sql = "select * from sgzdb_web_domain where domain = '".addslashes($domains[1])."'";
			$query = $dbhandle->Query($sql);
			$info = $dbhandle->GetRow($query);
			if($info){
				return $info;
			}else{
				return false;
			}
		}
	}
}


/**
 * 普通链接转换成推广链接。
 * 参数$origin_url不需要加反斜杠
 * 单品链接和整站链接都可以转换。当单品所属网站是淘宝、天猫和聚划算时方法有所不同，单独查询，单品由推广链接表中查询
 * 如果成功，则返回推广链接（对于淘宝、天猫、聚划算的最终推广链接可能就是原始链接，因为这个产品没有参加淘宝客）；如果失败则返回空。
 */
function _normalurl_to_tgurl($gzid,$origin_url,$logid)
{
	if(!$origin_url)
	{
		return '';
	}
	//判断普通链接是哪个网站的，比如拍拍、淘宝、京东、亚马逊等等，看它适用于哪种转换规则。
	$webinfo = getdomaininfobyurl($origin_url);
// 	$domain = getdomain($origin_url);
	
	//规则一：拍拍的商品详细http://item.wanggou.com/或http://auction1.paipai.com/
// 	if( $domain[2] == "item.wanggou.com" || $domain[2] == "auction1.paipai.com")
// 	{
// 		$ret = _paipai_convert($origin_url,$logid);
// 		return $ret?$ret:'';
// 	}
	
	//规则二：如果是“天猫171”、“淘宝170”、“聚划算256”，到表sgzdb_taobao_originurl_to_tgurl中获取推广链接
	if($webinfo['wid'] == "170" || $webinfo['wid'] == "171" || $webinfo['wid'] == "256")
	{
		$tg_url = _taobao_convert($gzid);
		
		return $tg_url?$tg_url:'';
	}
	
	
	
	//规则三：属于那些走独立推广接口的网站，包括：一号店、乐蜂、国美、梦芭莎、唯品会、携程、苏宁、当当、美团。
// 	$siteinfo = getsiteinfobyurl($origin_url);
// 	require(ROOT_PATH . "operatedata/webfencheng.php");
// 	if(in_array($siteinfo['wid'],$dandu_jiekou_web))//如果原始链接属于这些独立接口网站中的一个。
// 	{
// 		//每个网站都有自己的推广链接组装方法
// 		if($siteinfo['wid'] == 2)//一号店
// 		{
// 			$wenhao_pos = strpos($origin_url, '?');
// 			if($wenhao_pos === false)//原始链接中没有问号
// 			{
// 				$ret = $origin_url."?tracker_u=103047682&uid=".$logid;
// 				return $ret;
// 			}
// 			else//原始链接中有问号
// 			{
// 				$ret = $origin_url."&tracker_u=103047682&uid=".$logid;
// 				return $ret;
// 			}
// 		}
// 		elseif($siteinfo['wid'] == 145)//乐蜂
// 		{
// 			$ret = "http://track.lefeng.com/track.jsp?aid=29084&cid2=".$logid."&unionparameter=".$logid."&url=".$origin_url;
// 			return $ret;
// 		}
// 		elseif($siteinfo['wid'] == 97)//国美
// 		{
// 			$ret = "http://cps.gome.com.cn/home/JoinUnion?sid=2009&wid=2063&feedback=".$logid."&to=".$origin_url;
// 			return $ret;
// 		}
// 		elseif($siteinfo['wid'] == 11)//梦芭莎
// 		{
// 			$ret = "http://union.moonbasa.com/rd/rd.aspx?e=-999&adtype=0&unionid=twelvei&subunionid=".$logid."&other=".$logid."&url=".$origin_url;
// 			return $ret;
// 		}
// 		elseif($siteinfo['wid'] == 56)//唯品会
// 		{
// 			$ret = "http://click.union.vip.com/redirect.php?url=eyJ1Y29kZSI6IjU2Yzg4YzViIiwic2NoZW1lY29kZSI6IjRjYmM3YzI3In0=&desturl=".$origin_url."&chan=".$logid;
// 			return $ret;
// 		}
// 		elseif($siteinfo['wid'] == 17)//携程
// 		{
// 			$ret = "http://u.ctrip.com/union/CtripRedirect.aspx?TypeID=2&Allianceid=14122&sid=397595&OUID=".$logid."&jumpUrl=".$origin_url;
// 			return $ret;
// 		}
// 		elseif($siteinfo['wid'] == 18)//苏宁
// 		{
// 			$ret = "http://union.suning.com/aas/open/vistorAd.action?userId=15724&webSiteId=0&adInfoId=00&adBookId=0&subUserEx=".$logid."&vistURL=".$origin_url;
// 			return $ret;
// 		}
// 		elseif($siteinfo['wid'] == 6)//当当
// 		{
// 			$ret = "http://union.dangdang.com/transfer/transfer.aspx?from=P-317414-".$logid."&backurl=".$origin_url;
// 			return $ret;
// 		}
// 		elseif($siteinfo['wid'] == 43)//美团
// 		{
// 			$ret = "http://r.union.meituan.com/url/visit/?a=1&key=bEIY7DPC4ARdoTBMcgGJaFxwnOft6i3u&sid=".$logid."&url=".$origin_url;
// 			return $ret;
// 		}
// 		else
// 		{
// 			return '';
// 		}
		
// 	}
	
	//规则四：如果属于多麦网合作的网站，按照多麦的规则拼接成推广链接，推广链接中要有多麦网的aid。
//	$dbhandle = new SqlClass();
//	$dbhandle->connect();

	$dbhandle = GLX()->db;
	
	$sql = "select * from sgzdb_duomai_info where web_id = '".$webinfo['wid']."' and duomai_aid > 0";
	$query = $dbhandle->Query($sql);
	$duomairow = $dbhandle->GetRow($query);
	if($duomairow)//说明该$webinfo['wid']属于多麦推广网站，拥有多麦的活动id
	{
		$tg_url = _duomai_convert($duomairow['duomai_aid'],$origin_url,$logid);
		return $tg_url?$tg_url:'';
	}
	
	//以上都不符合，无法进行推广，转换不成功。
	return '';
}

//多麦网的广告主，普通链接转成推广链接。参数$origin_url不需要特意加反斜杠。
function _duomai_convert($duomai_aid,$origin_url,$logid)
{
	$duomai = 'http://c.duomai.com/track.php?site_id=119880&aid='.$duomai_aid;
	$euid = '&euid='.$logid;
	$t = '&t='.urlencode($origin_url);
	return $duomai.$euid.$t;
}

//淘宝、天猫、聚划算，将原始链接转换成推广链接。
function _taobao_convert($gzid)
{
	if(!$gzid || $gzid<=0)
	{
		return '';
	}
	
//	$dbhandle = new SqlClass();
//	$dbhandle->connect();

	$dbhandle = GLX()->db;
	
	$sql = "select * from sgzdb_taobao_originurl_to_tgurl where mtid = '".$gzid."'";
	$query = $dbhandle->Query($sql);
	$taobaolinkrow = $dbhandle->GetRow($query);
	if($taobaolinkrow)
	{
		return $taobaolinkrow['tg_url'];//这个tg_url可能为空（待转），也可能等于原始链接origin_url（已转但没参加淘宝客），也可能是以s.click.taobao.com开头的推广链接（已转并参加淘宝客）。
	}
	else {
		return '';
	}
}

//参考http://www.wzsky.net/html/article/php/php2/14054.html
//检查email地址格式的代码。这是一个完全符合RFC2822和RFC2821的代码。只检查单个email地址。 
function check_email_address($email) {
	// First, we check that there's one @ symbol, and that the lengths are right
	if (!ereg("[^@]{1,64}@[^@]{1,255}", $email)) {
		// Email invalid because wrong number of characters in one section, or wrong number of @ symbols.
		return false;
	}
	// Split it into sections to make life easier
	$email_array = explode("@", $email);
	$local_array = explode(".", $email_array[0]);
	for ($i = 0; $i < sizeof($local_array); $i++) {
		if (!ereg("^(([A-Za-z0-9!#$%&'*+/=?^_`{|}~-][A-Za-z0-9!#$%&'*+/=?^_`{|}~\.-]{0,63})|(\"[^(\\|\")]{0,62}\"))$", $local_array[$i])) {
			return false;
		}
	}
	if (!ereg("^\[?[0-9\.]+\]?$", $email_array[1])) { // Check if domain is IP. If not, it should be valid domain name
		$domain_array = explode(".", $email_array[1]);
		if (sizeof($domain_array) < 2) {
			return false; // Not enough parts to domain
		}
		for ($i = 0; $i < sizeof($domain_array); $i++) {
			if (!ereg("^(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|([A-Za-z0-9]+))$", $domain_array[$i])) {
				return false;
			}
		}
	}
	return true;
}


//产生随机验证码，参数是长度。参考http://www.enet.com.cn/article/2011/0321/A20110321841172.shtml
function generate_valicode($length = 8)
{
	// 密码字符集，可任意添加你需要的字符
	$chars = 'aIJKL67MbcdefijklZ012stpx^&*}<rhoE89!@FGyzABCDuvwn-_ [SgmqHNOPQR45#$%>~()TUVWXY3]{`+=,.;:/?';
	$chars_len = strlen($chars);
	$password = '';
	for ( $i = 0; $i < $length; $i++ )
	{
		// 这里提供两种字符获取方式
		// 第一种是使用 substr 截取$chars中的任意一位字符；
		// 第二种是取字符数组 $chars 的任意元素
		// $password .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
		$password .= $chars[ mt_rand(0, $chars_len - 1) ];
	}
	
	return $password;
}

//用户退出
function dologout()
{
	//参考http://blog.sina.com.cn/s/blog_865ef5610101ehht.html上删除cookie的方法，将value设为空，将有效期设为当前时间减1。
	setcookie("cookiesecret1", "", time()-1);
	setcookie("cookiesecret2", "", time()-1);
}

//检查敏感词
//如果有敏感词，则返回第一个匹配到的敏感词；如果无敏感词，则返回空
function check_sensitive_words($checkstr)
{
	$sw = getSetting('sw');
	//$sw就是敏感词的数组
	$strLen = mb_strlen($checkstr, 'utf8');
	
	for ($i = 0; $i < $strLen; $i++) {
		if (($char = (string)mb_substr($checkstr, 0, 1, 'utf8')) && isset($sw[$char])) {
			foreach ($sw[$char] as $one_sensitiveword) {
				if (stripos($checkstr, (string)$one_sensitiveword) !== false)
					return $one_sensitiveword;
			}
		}
		$checkstr = (string)mb_substr($checkstr, 1, $strLen-($i+1), 'utf8');
	}
	
	return '';
}

//检查文本中的url链接，可以带http或者不带（比如：http://www.taobao.com或者item.jd.com/846560.html或者更复杂的http://www.miui.com/forum.php?mod=viewthread&tid=58621&extra=&highlight=nexus%2Bone%2B%E5%88%B7%2BMIUI&page=1等等）
//返回值：一个主域名--表示匹配到的一个合作域名；空--表示没有匹配到任何的域名。
function check_url_link($checkstr)
{
	$urls = '';
	if (preg_match_all('~(?:(https|http|ftp)\:\/\/|www\.)?(?:[A-Za-z0-9\_\-]+\.)+[A-Za-z0-9]{1,4}(?:\:\d{1,6})?(?:\/[\w\d\/=\?%\-\&\;_\~\`\:\+\#\.\@\[\]]*(?:[^\<\>\'\"\n\r\t\s\x7f-\xff])*)?~i',
			$checkstr, $match))
	{
		foreach ($match[0] as $v)
		{
			$urls[$v] = $v;
		}
	}
	
	if($urls)//urls里保存了文本里所有的链接。
	{
		foreach($urls as $urldata)
		{
			//每一个$urldata都是一个url链接
			
			$domaininfo = getdomaininfobyurl($urldata);
			if($domaininfo)
			{
				//说明这个url匹配到了sgzdb_web_domain表中的某个域名，说明这个链接是合作网站的链接。
				return $domaininfo['domain'];
			}
		}
	}
	return '';
}

//将字符串百分比（例如13.45%），转化成float数字（例如0.1345）
function percentage_to_float($percentage)
{
	$percentage_str = rtrim($percentage,'%');
	$percentage_float = (float)$percentage_str;
	
	return $percentage_float/100.0;
}



///////////////////////////////////////////亲客新加///////////////////////////////////////////////////////////////
//$k要么是一个字符串，要么是一个数组或者为空。
//返回值，要么是一个数组（如果$k是一个数组或者为空，就算结果只有一项，也返回数组），要么是一个字符串（$k只是一个字符串）
function getConfKV($k)
{
//	$dbhandler =  GLX()->db;
	$where = '';
	$return_type = '';
	if($k == '')
	{
		$where = '';
		$return_type = 'array';
	}
	else if(is_array($k))
	{
		$k_str = jimplode($k);
		$where = ' where confkey in ('.$k_str.') ';
		$return_type = 'array';
	}
	else 
	{
		$where = " where confkey = '".$k."'";
		$return_type = 'string';
	}
	
	$sql = "select * from qkdb_conf_kv ".$where;
	$query = GLX()->db->Query($sql);
	
	$temp = array();
	while($onevalue = GLX()->db->GetRow($query))
	{
		$temp[$onevalue['confkey']] = $onevalue['confvalue'];
	}

	
	if($return_type == 'array')
	{
		return $temp;
	}
	else if($return_type == 'string')
	{
		return $temp[$k];
	}
	else 
	{
		return '';
	}
}

//往conf_kv表写入值。$k指明是哪一行，$v是要写入的值。
function setConfKV($k, $v)
{
//	$dbhandle = new SqlClass();
//	$dbhandle->connect();

	$dbhandle = GLX()->db;
	
	$sql = "update qkdb_conf_kv set confvalue = '".$v."' where confkey = '".$k."'";
	
	$dbhandle->Query($sql);
	
	return true;
}

//得到一个产品，它的所有主图的路径。
//成功返回路径数组(注意，是图片的相对路径)，失败返回空。
//适用于亲客项目
function get_product_main_img_path($product_id)
{
	if($product_id<=0 || !$product_id)
	{
		return '';
	}

	$dir = 'attachment/prod_'.$product_id.'/images/main_images/';

	if(!is_dir($dir))
	{
		//说明该产品没有主图
		return '';
	}
	
	$main_images_arr = array();
	
	//参考http://www.chinaz.com/program/2008/1024/41962.shtml，得到一个文件夹下的所有文件名
	if ($dh = opendir($dir))
	{
    	while (($file = readdir($dh)) !== false)
        {
        	if('file' == filetype($dir . $file)) //filetype可能为file或者dir。
        	{
        		//获得文件扩展名
        		$temp_arr = explode(".", $file);
        		$main_images_arr[$temp_arr[0]] = $dir . $file;
        	}
        }
        closedir($dh);
	}
	else {
		return '';//主图所在文件夹打不开
	}
	
	if(!$main_images_arr)
	{
		return '';
	}
	else 
	{
		return $main_images_arr;
	}
	

}

//得到店铺的logo地址。(注意，是图片的相对路径)
//失败返回空。
function get_logo_img_path()
{
	//得到当前模板的名称。	
	$templatename = getConfKV('template_name');
	
	$dir = 'templates/'.$templatename.'/images/attachment/logo_pic/';
	
//	$dir = 'attachment_frontweb/logo_pic/';
	
	if(!is_dir($dir))
	{
		//说明没有logo图的目录
		return '';
	}
	
	$logo_pic = '';
	
	//参考http://www.chinaz.com/program/2008/1024/41962.shtml，得到一个文件夹下的所有文件名
	if ($dh = opendir($dir)) //打开目录
	{
    	while (($file = readdir($dh)) !== false)
        {
        	if('file' == filetype($dir . $file)) //filetype可能为file或者dir。
        	{
        		$logo_pic = $dir . $file;
        	}
        }
        closedir($dh);
	}
	else {
		return '';//主图所在文件夹打不开
	}
	
	if(!$logo_pic)
	{
		return '';
	}
	else 
	{
		return $logo_pic; 
	}
}

//得到店铺的轮播大图地址。(注意，是图片的相对路径)
//成功返回路径数组（因为可能有多张图片），失败返回空。
function get_boardpic_path()
{
	//得到当前模板的名称。
	$templatename = getConfKV('template_name');
	
	$dir = 'templates/'.$templatename.'/images/attachment/board_pic/';
	
// 	$dir = 'attachment_frontweb/board_pic/';
	
	if(!is_dir($dir))
	{
		//说明轮播大图的目录都不存在
		return '';
	}
	
	$main_images_arr = array();
	
	//参考http://www.chinaz.com/program/2008/1024/41962.shtml，得到一个文件夹下的所有文件名
	if ($dh = opendir($dir))
	{
		while (($file = readdir($dh)) !== false)
		{
			if('file' == filetype($dir . $file)) //filetype可能为file或者dir。
			{
				$temp_arr = explode(".", $file);
				$main_images_arr[$temp_arr[0]] = $dir . $file;
			}
		}
		closedir($dh);
	}
	else {
		return '';//轮播图所在文件夹打不开
	}
	
	if(!$main_images_arr)
	{
		return '';
	}
	else
	{
	return $main_images_arr;
	}
}



?>