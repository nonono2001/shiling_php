<?php

function send_get_url($url)
{
	//初始化
	$ch = curl_init();

	//设置选项，包括发射目标url
	curl_setopt( $ch, CURLOPT_URL, $url);
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);  // 这一步，目前不懂其作用
	curl_setopt( $ch, CURLOPT_HEADER, 0);  // 这一步，目前不懂其作用

    $file_contents = curl_exec($ch);//发射，并获得返回值

	$info = curl_getinfo($ch);

    curl_close($ch); //关闭请求

	if (!$info['http_code'])
	{
		return 'die url';
	}
	else if( $info['http_code'] == 404 )
	{
		return 'not found url';
	}
	
    return $file_contents;

}

//$post_data是url带的参数，可以是数组，也可以是字符串。
//数组如$params  = array(
//'appid'=>$appid,
//			'secret'=>$secret,
//			'js_code'=>$xcx_code,
//			'grant_type'=>'authorization_code'
//
//		);
//字符串如：$params_str = 'appid='.$appid.'&secret='.$secret.'&js_code='.$xcx_code.'&grant_type=authorization_code';
function send_post_url( $url, $post_data )
{
	//初始化
	$ch = curl_init();

	//设置选项，包括发射目标url
	curl_setopt( $ch, CURLOPT_URL, $url);
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);  // 这一步，目前不懂其作用
	curl_setopt( $ch, CURLOPT_HEADER, 0);  // 这一步，目前不懂其作用

	curl_setopt($ch, CURLOPT_POST,true); //设置请求方式为POST方式
    curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);//发送的post值，即post的子弹

	$file_contents = curl_exec($ch);//发射，并获得返回值

	$info = curl_getinfo($ch);

    curl_close($ch); //关闭请求

	if (!$info['http_code'])
	{
		return 'die url';
	}
	else if( $info['http_code'] == 404 )
	{
		return 'not found url';
	}
	
    return $file_contents;



}

//参数$apiParam是个数组，指明本次调用需要的参数，从mod、code开始。
//method指示调用方法是post还是get。默认是post。
//$apiUrl用来指明服务器地址ip或域名。最末尾不要用'/'斜杠结尾。比如http://www.sgztest.com就可以了
function call_apiurl( $apiUrl, $apiParam, $method = 'POST' )
{
	$temp = '';
	if($apiParam != '')
	{
		foreach( $apiParam as $key => $value )
		{
			$temp = $temp."$key=$value&";
		}
		$apiParam = substr($temp,0,strlen($temp)-1);  //消除尾部的一个&号
	}
	
	if( "GET" == $method || "get" == $method )
	{
		return send_get_url($apiUrl.'/server.php?'.$apiParam);
	}
	
	if( "POST" == $method || "post" == $method )
	{
		return send_post_url($apiUrl.'/server.php', $apiParam);
	}
	
	
	
}








//serviceurl的老式写法
//对send_post_url和send_get_url更高一层的封装。该函数的功能是到search系统的apiUrl表里取出某Id的apiurl，并将args给它，再发送请求该apiurl。
//那么search系统必需提供一个服务，供别的系统去查询apiUrl表。这个服务的url必需在这里写死了。
//$data应是个数组
//function call_apiurl( $apiUrlId, $data='' )
//{
//	$search_server_url = "http://127.0.0.1:8011/server.php?act=fetch_url&apiId=$apiUrlId";
//	$result = send_get_url( $search_server_url );
//	/*$result是Json格式的字符串。形如本例
//	{"url":"http:\/\/127.0.0.1:8001\/server.php?act=GetIndustryInfo","method":"get"}
//	只有url和method信息，没有arg信息。
//    */
//	$obj = json_decode($result);
//
//	//将result分解成url、method两大部分
//	$url='';
//	$method='';
//	{
//		$url = $obj->url;
//		$method = $obj->method;
//	}
//	if( 'post' == $method || 'POST' == $method )
//	{
//		//将url分成两部分，一部分是?问号左边（如http://127.0.0.1:8008/server.php），一部分是?问号右边（如act=get_productinfo）
//		$sep_url = explode("?",$url);//$sep_url[0]是左边部分；$sep_url[1]右边部分
//		
//		$url = $sep_url[0];
//	}
//	
//	//组装apiUrl的参数，就是子弹$data，$data应是个数组
//	{
//		$temp = '';
//		if($data != '')
//		{
//			foreach( $data as $key => $value )
//			{
//				$temp = $temp."$key=$value&";
//			}
//			$data = substr($temp,0,strlen($temp)-1);  //消除尾部的一个&号
//		}
//	}
//	
//	//到这里，$data里是用一个用&连接的字符串，形如arg0=yy&arg1=99
//
//    //根据不同的method，来post、get、delet或put来发枪。目前只有get和post两种发枪方式。
//	if( $method == 'get' )
//	{
//		return send_get_url("$url&$data");
//	}
//	else if( $method == 'post' )
//	{
//		return send_post_url($url, $sep_url[1].'&'.$data);
//	}
//	else if( $method == 'put' )
//	{
//		return '';
//	}
//	else if( $method == 'delete' )
//	{
//		return '';
//	}
//	else
//	{
//		echo "apiUrl method[$method   $url    $data] wrong! Exit!<br/>";
//		exit();
//	}
//
//
//}


?>