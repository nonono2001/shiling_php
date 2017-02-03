<?php

class ModuleObject extends MasterObject
{
	
	function ModuleObject()//构造函数
	{
		$this->MasterObject( 'db_on' );
		
		
		//用户可以不登录，游客亦可
//		if( MEMBER_ID<1 ) //用户未登录
//		{
//			$this->Messager("请先<a href='index.php?mod=login'>点此登录</a>或者<a href='index.php?mod=member'>点此注册</a>一个帐号",'index.php?mod=login');
//		}
		
//	    if( MEMBER_ID>0 && MEMBER_TYPE == "" ) //这里MEMBER_TYPE可能为空，是因为用户可能是在喂喂网注册的，在喂喂注册时不需要选“个人”还是“商户”。
//		{
//			$this->Title = "完善基本信息";
//			$this->Messager("您还没有设置推啊基本信息，请先设置基本信息后再进行操作","index.php?mod=member&code=confirmbase");
//		}
		
		$this->Execute();
		
	}
	
	
	function Execute()
	{
		switch($this->Act)
		{
			case 'test1': //透明附盖层，可以页面上实现一步一步的教程。参考http://www.paishi.com/，并且实现网页背景图片固定，不随着滚动条下拉而移动。
				$this->test1();
				break;
		
			
			case 'test2':
				$this->test2();
				break;
				
			case 'test3':
				$this->test3();
				break;
				
			case 'test4':
				$this->test4();
				break;
			
				case 'test5':
					$this->test5();
					break;
					
				case 'test6':
					$this->test6();
					break;
					
				case 'test7':
					$this->test7();
					break;
					
				case 'test8':
					$this->test8();
					break;
					
				case 'testcalltaobaoapi':
					$this->testcalltaobaoapi();
					break;
					
				case 'call_offical_taobao_api':
					$this->call_offical_taobao_api();
					break;
					
				case 'testjson':
					$this->testjson();
					break;
					
				case 'testjson2':
					$this->testjson2();
					break;
					
				case 'testecho':
					$this->testecho();
					break;
					
				case 'testjimploade':
					$this->testjimploade();
					break;
					
				case 'testhtmlQQ':
					$this->testhtmlQQ();
					break;
				
					
				case 'testaaa':
					$this->testaaa();
					break;
					
				case 'testpost':
					$this->testpost();
					break;
					
				case 'testinserttemp':
					$this->testinserttemp();
					break;
					
				case 'gencode':
					$this->gen_code();
					break;

			case 'testhong':
				$this->testhong();
				break;

			case 'test555':
				$this->test555();
				break;
					
			default:
				$this->Common();
				break;
		}

	}
	
	function Common()
	{
		echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">缺少参数......';
		
	}

	function test555()
	{
		$r = json_decode('{
    "errcode": 40029,
    "errmsg": "invalid code"
}',true);

		var_dump($r);

	}
	
	function test1()
	{
		//$this->Messager('dfdfdfdd');
		$abc['Boating/ Diving Surf'] = 'ab3';
		print_r($abc['Boating/ Diving Surf']);
		include(template('test1'));
	}
	
	function test2()
	{
		$str = '<h2 style=\"font-size:18px;text-align:left;font-family:微软雅黑, 黑体;font-weight:normal;color:#666666;\">
	<img src=\"/templates/admin/js/plugin_kindeditor-4.1.2_js/attached/image/20150201/20150201155115_16392.jpg\" alt=\"\" /><img src=\"/js/plugin_kindeditor-4.1.2_js/attached/image/20150201/20150201155115_333333.jpg\" alt=\"\" /><br />
</h2>';
		
		$str = stripslashes($str);
		$pos = strpos($str, '<img src="');
		
		$len = strlen($str);
		
		//echo ereg_replace('<img src=','<aaa aaa=',$str);
		
		//echo $pos;
		$num = '4';
		$string = "<img src = This string has four words.";
		$string = ereg_replace('<img src =', '<aaa aaa', $string);
		//echo '<code>'.$string."</code>";   /* Output: 'This string has 4 words.' */
		
		//http://www.jb51.net/article/39117.htm
		//http://www.baidu.com/s?ie=UTF-8&wd=php%E6%AD%A3%E5%88%99+%E6%89%BE%E5%88%B0%E6%89%80%E6%9C%89img
		//http://tuzwu.iteye.com/blog/1181022
		
		preg_match_all('/<\s*img\s+[^>]*?src\s*=\s*(\'|\")(.*?)\\1[^>]*?\/?\s*>/i',$str,$match);
		error_log(date('Y-m-d H:i:s ') . __CLASS__ . '::' . __FUNCTION__ . ' @ '.
				'rs: ' . var_export($match, 1) . "\r\n", 3, DATA_PATH."chutest/CHUTEST.log");
		
		worklog($match[0]);worklog($match[1]);
		//echo $match[0];
		
		
		//http://zhidao.baidu.com/link?url=MLJdfS30nqkU2X6vSWeuRwtbbDtvjkHXoTH_eCBLp1OaTRaAoi5dAJ9RG3KZ3abRyuzI3w7ncjN-VHv3qkaIr4Y_G8dkYNjVOAwdt9WWRXW
		$url ='<img width="197" height="253" alt=" " src="/case/clxy/page/files/newspic/20090928084704364888.jpg" border="0" />';
		$url6 = '<h2 style="font-size:18px;text-align:left;font-family:微软雅黑, 黑体;font-weight:normal;color:#666666;">
	<img src="/templates/admin/js/plugin_kindeditor-4.1.2_js/attached/image/20150201/20150201155115_16392.jpg" alt="" /><br />
    <img src="/templates/admin/js/plugin_kindeditor-4.1.2_js/attached/image/20150201/20150201155115_16392.jpg" alt="" /><br />
</h2>';
		$ok=preg_replace('/(<\s*img\s+src\s*=\s*\")(\/templates\/admin\/js\/plugin_kindeditor-4\.1\.2_js\/attached\/image\/\d+\/)(.+?\")/i',"\${1}/prod_123/\${3}",$url6);
		echo "ok is : ".$ok;
		error_log(date('Y-m-d H:i:s ') . __CLASS__ . '::' . __FUNCTION__ . ' @ '.
				'$ok: ' . var_export($ok, 1) . "\r\n", 3, DATA_PATH."chutest/CHUTEST.log");
		include(template('test','admin'));
		
	}
	
	function test3()
	{
	$test= 0;
	
	echo 'strlen is '.mb_strlen($test,'utf8');

	if(0 === '0')
	{
		echo '<br />在php中，数字0 is 字符0';
	}
	if(!$test)
	{
		echo '<br />在php中，非0为真'; //被输出
	}
if($test==''){

    echo '<br />在php中，0即为空'; //被输出

}

 

if($test===''){

    echo '<br />在php中，0全等于空'; //不被输出

}

 

if($test==NULL){

    echo '<br />在php中，0即为NULL'; //被输出

}


if($test===NULL){
    echo '<br />在php中，0全等于NULL'; //不被输出
}
 
if($test==false){
    echo '<br />在php中，0即假'; //被输出
}

 

if($test===false){

    echo '<br />在php中，0全等于假'; //不被输出

}
		
		
		include(template('test','admin'));
		
	}
	
	
	function test4()
	{
		$a = array('a','b','c');
		echo current($a);
		
		foreach($a as $v)
		{
			echo $v;
		}
	}
	
	
	function test5()
	{
		$auto_split_time = '1，2，3';//$auto_split_time是个字符串，比如：1,2,22,23
    	$autosplittime_arr = explode(",", $auto_split_time);
    	
    	$autosplittime_arr = array_filter($autosplittime_arr);
    	
    	$abc = 10;var_dump($abc);
    	$bbb = (int)$abc;var_dump($bbb);
    	if($abc === $bbb)
    	{
    		echo 'yes ===';
    	}
    	else 
    	{
    		echo 'no';
    	}
    	exit();
    	
    	if(!$autosplittime_arr) //不填，或只填0。
    	{
    		$autosplittime_arr = array('0');
    	}
    	
    	foreach($autosplittime_arr as $onetime)
    	{
    		
    	}
    	
    	$hour = intval(date('H'));
    	if (!in_array('0', $autosplittime_arr)) 
    	{
    		echo '22222222';
    		//当前时间不处于执行时间点，不执行
    		return false;
    	}
    	
    	echo '33333333333';
		
	}
	
	function test6()
	{
		echo microtime().'<br />';
		echo microtime(1).'<br />';
		echo time();
		include(template('test6','admin'));
	}
	
	function test7()
	{
		header( 'Location: '.'index.php');
	}
	function test8()
	{
		$x = array('a','b','c',array('d'=>'fdfd'));
// 		next($x);next($x);
// 		$cur = current($x);
// 		echo $cur;
		echo 'start:  ';
		$c = count($x);
		echo $c.'<br />';
		foreach($x as $v)
		{
			if(current($x) === end($x)){
			
				echo  'this is the last column';
			}
			echo $v.'<br />';
		}
	}
	
	public function testcalltaobaoapi()
	{
		/**
		 *http://gw.api.tbsandbox.com/router/rest?sign=EAF44CBDB54E2B988B2D406607829CCC&timestamp=2015-05-05+15%3A18%3A13&v=2.0&app_key=1012129701&method=taobao.inventory.adjust.trade&partner_id=top-apitools&session=61019241b62a7bbb4dc8ac87631b9bb216a110d9a2cbeaf2074082787&format=json&tb_order_type=1&operate_time=2&biz_unique_code=3&items=4
		 *
		 */
// 		$url = 'http://gw.api.tbsandbox.com/router/rest?sign=EAF44CBDB54E2B988B2D406607829CCC&timestamp=2015-05-05+15%3A18%3A13&v=2.0&app_key=1012129701&method=taobao.inventory.adjust.trade&partner_id=top-apitools&session=61019241b62a7bbb4dc8ac87631b9bb216a110d9a2cbeaf2074082787&format=json';
// 		$post_data = 'tb_order_type=1&operate_time=2&biz_unique_code=3&items=4';
		
// // 		$url = 'http://gw.api.tbsandbox.com/router/rest?app_key=1012129701&v=2.0&format=xml&sign_method=md5&method=taobao.inventory.adjust.trade&timestamp=2015-05-05+15%3A00%3A40&partner_id=top-sdk-php-20150308&session=61019241b62a7bbb4dc8ac87631b9bb216a110d9a2cbeaf2074082787&sign=9D7B988EA574130F1BCF98B05D2314C8';
// // 		$post_data = 'tb_order_type=1&operate_time=2&biz_unique_code=3&items=4';
		
// 		$resp = send_post_url($url, $post_data);
		
// 		print_r($resp);
// 		echo 'haha';
		
// 		exit();
		
		
		header("Content-type: text/html; charset=utf-8");
		include ('taobaosdk/TopSdk.php');
		//require_once 'taobaosdk/top/TopClient.php';
		$c = new TopClient;
$c->appkey = '1012129701';
$c->secretKey = '';
$req = new InventoryAdjustTradeRequest;

// // $req->putOtherTextParam('sign','4DD7E52495920F932FE07A1E9F496F65');
// // $req->putOtherTextParam('timestamp','2015-05-05+13%3A45%3A53');
// // $req->putOtherTextParam('v','2.0');
// // $req->putOtherTextParam('partner_id','top-apitools');
// // $req->putOtherTextParam('format','json');

$req->setTbOrderType("1");
$req->setOperateTime("2");
$req->setBizUniqueCode("3");
$req->setItems("4");
$sessionKey = '61019241b62a7bbb4dc8ac87631b9bb216a110d9a2cbeaf2074082787';

$resp = $c->execute($req, $sessionKey);
	
		print_r($resp);echo 'wawa';
	
	}
	
	function call_offical_taobao_api()
	{
		/*
		 * http://gw.api.taobao.com/router/rest?sign=168EE91903D268A635F9ADBDA3B5085E&timestamp=2015-05-05+15%3A34%3A25&v=2.0&app_key=12129701&method=taobao.inventory.adjust.trade&partner_id=top-apitools&session=6102518b65d5069b1f61c1fc151644654327dd814c178df393630384&format=json&tb_order_type=1&operate_time=2&biz_unique_code=3&items=4
		 * 
		 */
		
		//这三个参数from Qinglan
		$app_key = '23038146';
		$appSecret = '4dd6883954417a28bfea022777c488a9';
		$session = '6102a162238c847c1d5e60061844a3454efdb66274132a3352469034';
		
		
		$paramArr = array(
				'app_key' => $app_key,
				'session' => $session,
				'method' => 'taobao.inventory.adjust.trade', //改它---------
				'format' => 'json',
				'v' => '2.0',
				'sign_method'=>'md5',
				'timestamp' => date('Y-m-d H:i:s'),
// 				'partner_id'=> 'top-apitools',
				//从这里以上，是系统参数；以下，是业务参数。
				'tb_order_type' => 'B2C',
				'operate_time' => date('Y-m-d H:i:s'),
				'biz_unique_code' => '333',
				'items' => '[{"TBOrderCode":"940580049807784","TBSubOrderCode":"940580049807784","isGift":"false","storeCode":"test00001","scItemId":"45255329769","scItemCode":"33610","originScItemId":"45255329769","inventoryType":"1","quantity":"12","isComplete":"true"}]',
		);
		
		//生成签名
		$sign = $this->createSign($paramArr,$appSecret);
		//组织参数
		$strParam = $this->createStrParam($paramArr);
		$strParam .= 'sign='.$sign;
		//访问服务
		//沙箱环境调用地址http://gw.api.tbsandbox.com/router/rest
		//正式环境调用地址http://gw.api.taobao.com/router/rest
		$url = 'http://gw.api.taobao.com/router/rest?'.$strParam; 
		$result = file_get_contents($url);
		$result = json_decode($result);
		echo "json的结构为:";
		print_r($result);
// 		echo "<br>";
// 		echo "用户名称为:".$result->user_get_response->user->nick;
// 		echo "<br>";
// 		echo "买家信用等级为:".$result->user_get_response->user->buyer_credit->level;
		
		
		
		
		
		

// 		$resp = send_post_url($gatewayUrl, $post_data);

// 		print_r($resp);
// 		echo 'call_offical_taobao_api done!';
	}
	
	function createSign ($paramArr,$appSecret) {
		
		$sign = $appSecret;
		ksort($paramArr);
		foreach ($paramArr as $key => $val) {
			if ($key != '' && $val != '') {
				$sign .= $key.$val;
			}
		}
		$sign.=$appSecret;
		$sign = strtoupper(md5($sign));
		return $sign;
	}
	//组参函数
	function createStrParam ($paramArr) {
		$strParam = '';
		foreach ($paramArr as $key => $val) {
			if ($key != '' && $val != '') {
				$strParam .= $key.'='.urlencode($val).'&';
			}
		}
		return $strParam;
	}
	
	function testjson()
	{
		$jsonstr = '{

    "vmarket_eticket_store_get_response": {

        "store_id": 12345,

        "address": "文一西路969号",

        "name": "阿里巴巴西溪园区",

        "city": "杭州",

        "province": "浙江",

        "district": "西湖区",

        "contract": "0756-12345678",

        "selfcode": "K123"

    }

}';
		$jsonobj = json_decode($jsonstr);
		
		$jsonarr = json_decode($jsonstr,1);
		
		error_log(date('Y-m-d H:i:s ') . __CLASS__ . '::' . __FUNCTION__ . ' @ '.
				'$jsonobj: ' . var_export($jsonobj, 1) . "\r\n", 3, DATA_PATH."chutest/CHUTEST.log");
		
		error_log(date('Y-m-d H:i:s ') . __CLASS__ . '::' . __FUNCTION__ . ' @ '.
				'$$jsonarr: ' . var_export($jsonarr, 1) . "\r\n", 3, DATA_PATH."chutest/CHUTEST.log");
		
		/*2015-09-16 15:39:58 ModuleObject::testjson @ $jsonobj: stdClass::__set_state(array(
		 'vmarket_eticket_store_get_response' =>
				stdClass::__set_state(array(
						'store_id' => 12345,
						'address' => '文一西路969号',
						'name' => '阿里巴巴西溪园区',
						'city' => '杭州',
						'province' => '浙江',
						'district' => '西湖区',
						'contract' => '0756-12345678',
						'selfcode' => 'K123',
				)),
		))
		
		2015-09-16 15:39:58 ModuleObject::testjson @ $$jsonarr: array (
				'vmarket_eticket_store_get_response' =>
				array (
						'store_id' => 12345,
						'address' => '文一西路969号',
						'name' => '阿里巴巴西溪园区',
						'city' => '杭州',
						'province' => '浙江',
						'district' => '西湖区',
						'contract' => '0756-12345678',
						'selfcode' => 'K123',
				),
		)
		*
		*/
		
		error_log(date('Y-m-d H:i:s ') . __CLASS__ . '::' . __FUNCTION__ . ' @ '.
				'$jsonobj->vmarket_eticket_store_get_response: ' . var_export($jsonobj->vmarket_eticket_store_get_response, 1) . "\r\n", 3, DATA_PATH."chutest/CHUTEST.log");
		/*
		 * 2015-09-16 15:42:37 ModuleObject::testjson @ $jsonobj->vmarket_eticket_store_get_response: stdClass::__set_state(array(
   'store_id' => 12345,
   'address' => '文一西路969号',
   'name' => '阿里巴巴西溪园区',
   'city' => '杭州',
   'province' => '浙江',
   'district' => '西湖区',
   'contract' => '0756-12345678',
   'selfcode' => 'K123',
))
		 * 
		 */
		
		error_log(date('Y-m-d H:i:s ') . __CLASS__ . '::' . __FUNCTION__ . ' @ '.
				'$jsonobj->vmarket_eticket_store_get_response->name: ' . var_export($jsonobj->vmarket_eticket_store_get_response->name, 1) . "\r\n", 3, DATA_PATH."chutest/CHUTEST.log");
		
		/*
		 * $jsonobj->vmarket_eticket_store_get_response->name: '阿里巴巴西溪园区'
		 */
	
	}
	
	function testjson2()
	{
		$mainorder = array(
			'process_status' => '1',
				'pay_status' => '2',
				'is_fail' => 'true',
				
		);
		$data = array(
							'data' => $mainorder,
							'rsp' => 'succ',
					);
					$feedback = json_encode($data);
					echo $feedback;
	}
	
	function testecho()
	{
		$r = array(
				"data" => array(
					'23423434634' =>array(
						"process_status" => "confirmed",
						"pay_status"=> '1',
						"is_fail" => "false",
						"pause" => "false",
					),
						
					'56476578768' =>array(
							"process_status" => "confirmed",
							"pay_status"=> '1',
							"is_fail" => "false",
							"pause" => "false",
					),
						
					'98806756560' =>array(
							"process_status" => "confirmed",
							"pay_status"=> '1',
							"is_fail" => "false",
							"pause" => "false",
					),
				),
				"rsp" => "succ",
				
		);

		echo json_encode($r);
	}
	
	function testjimploade()
	{
		$row[0] = array();
		list($class,$method) = explode(':',$row[0]['callback']);
		
		var_dump($class);
		var_dump($method);
	}
	
	function testhtmlQQ()
	{
		include(template('testhtmlQQ'));
	}
	
	function testaaa()
	{
		$sign = '27318502026ffe721598c9d93a4c482dconsume_type2item_title此页面为系统测试页面 请勿下单 tmallomstest6methodsendmobile13818465431num1num_iid522858768217order_id1222134479495670outer_iidtmallomstest6seller_nick迪卡侬旗舰店send_type2sku_properties尺码:均码;运动鞋尺码:27.5sms_template验证码$code.您已成功订购迪卡侬旗舰店提供的此页面为系统测试页面 请勿下单 tmallomstest6,有效期2015/10/09至2015/10/22,消费时请出示本短信以验证.如有疑问,请联系卖家.sub_method1sub_outer_iidtmallomstest6taobao_sid352469034timestamp2015-10-09 16:30:04token38e70c95b1e564e9c63fefd408acf69ftype0valid_ends2015-10-22 23:59:59valid_start2015-10-09 00:00:00';
		
		$s = iconv("UTF-8","gbk//TRANSLIT",$sign);
		echo $s;
		$sign = strtoupper(md5($s));
		
		echo $sign;
	}
	
	function testpost()
	{
		//include(template('testpost'));

		$a = sprintf("%.2f", '38.198');echo $a;
	}
	
	function testinserttemp()
	{
		
		echo strtotime("2016-11-11 00:00:00");

		$aa = 'ddd';
		if(1)
		{
			$aa = 'abc';
		}

		echo $aa;
		
	}
	
	//黄华的我爱鲜生项目，生成2000对一一对应的提货码和提货券号
	//提货码就是刮开的密码，提货券号就是每张卡唯一的序列号。
	function gen_code()
	{
		//提货码8位数字；
// 		for($i=0;$i<2000;$i++)
// 		{
// 			$s = '';
// 			$s = rand(1, 9);
// 			$s .= rand(0, 9);
// 			$s .= rand(0, 9);
// 			$s .= rand(0, 9);
// 			$s .= rand(0, 9);
// 			$s .= rand(0, 9);
// 			$s .= rand(0, 9);
// 			$s .= rand(0, 9); //8位随机数
			
// 			echo $s;echo '<br />';
// 		}
		
		//提货券号，4位字母+7位数字+1位字母+1位数字+1位字母+1位数字+1位字母+1位数字+1位字母+1位数字。总共19位。
		//其中头4位字母用WAXS，我爱鲜生。
		$h = 'WAXS';
		$zimu = array('a','b','c','d','e','f','g','h','i','j','k',
				      'm','n','p','r','s','t','u','v','w','x','y','z',
				      'A','B','C','D','E','F','G','H','J','K','L',
				      'M','N','P','Q','R','S','T','U','V','W','X','Y','Z'
		);//l,o,q,I,O,这几个字母不用，容易和数字混。保留47个字母。
		for($i=0;$i<2000;$i++)
		{
			
			
			$s = rand(1, 9);
			$s .= rand(0, 9);
			$s .= rand(0, 9);
			$s .= rand(0, 9);
			$s .= rand(0, 9);
			$s .= rand(0, 9);
			$s .= rand(0, 9);//7位随机数
			
			$qh = $h.$s;//4位字母+7位数字
			$ind = rand(0,46);$qh .= $zimu[$ind]; //+1位字母
			$qh .= rand(0,9);  //+1位数字
			$ind = rand(0,46);$qh .= $zimu[$ind]; //+1位字母
			$qh .= rand(0,9);  //+1位数字
			$ind = rand(0,46);$qh .= $zimu[$ind]; //+1位字母
			$qh .= rand(0,9);  //+1位数字
			$ind = rand(0,46);$qh .= $zimu[$ind]; //+1位字母
			$qh .= rand(0,9);  //+1位数字
			
			echo $qh.'<br />';
		}
		
		
		
		
		
	}


	function testhong()
	{

//		$url = 'http://www.baisontest.com/e3_trunk/webopm/web?app_act=api/ec&app_mode=func';
//		$system_param = array(  //系统级参数
//'key' => 'baison',
//			'requestTime' => time(),
//			'version' => 0.1,
//			'serviceType' => 'goods.list.get',
//			'data' => json_encode(array('a'=> 123, 'b'=>'ppp')),//业务级参数
//			'sign'
//		);
//
//		send_post_url($url,$post_data);
		echo json_encode(array('a'=>123, 'b' => 'ppo'));

	}


	
}


?>