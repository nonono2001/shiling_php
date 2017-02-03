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


			default:
				$this->Do_login(); //小程序前端做登录动作
				break;
		}

	}



	//小程序前端做登录动作
	function Do_login()
	{
		//接收小程序发来的code登录凭证
		$xcx_code = getPG('code');

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
        $params_str = 'appid='.$appid.'&secret='.$secret.'&js_code='.$xcx_code.'&grant_type=authorization_code';
		$sessionkey_openid = send_post_url($url,$params_str);
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

		$sessionkey_openid_array = json_decode($sessionkey_openid,true);

		if(!$sessionkey_openid_array || $sessionkey_openid_array['errcode'])
		{
            //可能是api调用返回空，也可能返回失败信息
            json_error('code可能过期，无法用code换取到openid和session_key');
		}
        else
        {
            //$sessionkey_openid_array一定是成功信息
            json_error('code可能过期，无法用code换取到openid和session_key');
            json_result($sessionkey_openid_array['openid'],'this is openid using params_str');
        }


	}


	
	

}
?>