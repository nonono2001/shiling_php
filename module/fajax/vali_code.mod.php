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
            case 'gen_valicode_bindphone':
                $this->Gen_valicode_bindphone(); //生成绑定手机号的验证码
                break;

			default:
				$this->Common();
				break;
		}

	}



	//小程序前端做登录动作
	function Common()
	{
		echo '';
	}

    //生成绑定手机号的验证码
    function Gen_valicode_bindphone()
    {error_log(date('Y-m-d H:i:s ') . __CLASS__ . '::' . __FUNCTION__ . ' @ '.
        '$code_json: ' . var_export(1111, 1) . "\r\n", 3, "data/chutest/CHUTEST-XX.log");
        //前端会传来手机号
        $cellphone = getPG('mobile'); //前端已对手机号格式做过验证。这里省点事，不做格式验证了

        if(!$cellphone)
        {
            json_error('手机号不能为空','40013');
        }

        //生成6位数字验证码，并已存入数据库。
        $vali_code = gen_vali_code_6n($cellphone);
        if(!$vali_code)
        {
            //验证码生成失败
            json_error('验证码生成失败，请重试','40013');
        }

        $code_json = json_encode( array('code'=> strval($vali_code)), 1);

        error_log(date('Y-m-d H:i:s ') . __CLASS__ . '::' . __FUNCTION__ . ' @ '.
            '$code_json: ' . var_export($code_json, 1) . "\r\n", 3, "data/chutest/CHUTEST-XX.log");

        $config = getSetting( 'sys_setting' );

        $aliyun_AccessKeyID = $config['aliyun_AccessKeyID']; //微信服务器提供给调用者的appid和appsecret。
        $aliyun_AccessKeySecret = $config['aliyun_AccessKeySecret'];
        //调用sdk，向手机号发送一次短信。
        //参考https://help.aliyun.com/document_detail/44368.html?spm=5176.doc44327.6.581.MadQEG
        include_once 'include/aliyun_sms/aliyun-php-sdk-core/Config.php';

        error_log(date('Y-m-d H:i:s ') . __CLASS__ . '::' . __FUNCTION__ . ' @ '.
            'here 111: ' . var_export('', 1) . "\r\n", 3, "data/chutest/CHUTEST-XX.log");

    use Sms\Request\V20160927 as Sms;

        error_log(date('Y-m-d H:i:s ') . __CLASS__ . '::' . __FUNCTION__ . ' @ '.
            'here 222: ' . var_export('', 1) . "\r\n", 3, "data/chutest/CHUTEST-XX.log");

        $iClientProfile = DefaultProfile::getProfile("cn-hangzhou", $aliyun_AccessKeyID, $aliyun_AccessKeySecret);

        error_log(date('Y-m-d H:i:s ') . __CLASS__ . '::' . __FUNCTION__ . ' @ '.
            'here 333: ' . var_export('', 1) . "\r\n", 3, "data/chutest/CHUTEST-XX.log");

        $client = new DefaultAcsClient($iClientProfile);

        error_log(date('Y-m-d H:i:s ') . __CLASS__ . '::' . __FUNCTION__ . ' @ '.
            'here 444: ' . var_export('', 1) . "\r\n", 3, "data/chutest/CHUTEST-XX.log");

        $request = new Sms\SingleSendSmsRequest();

        error_log(date('Y-m-d H:i:s ') . __CLASS__ . '::' . __FUNCTION__ . ' @ '.
            'here 555: ' . var_export('', 1) . "\r\n", 3, "data/chutest/CHUTEST-XX.log");

        $request->setSignName("食令");/*签名名称*/
        $request->setTemplateCode("SMS_44480306");/*模板code*/
        $request->setRecNum($cellphone);/*目标手机号*/
        $request->setParamString($code_json);/*模板变量，数字一定要转换为字符串*/

        error_log(date('Y-m-d H:i:s ') . __CLASS__ . '::' . __FUNCTION__ . ' @ '.
            '$cellphone: ' . var_export($cellphone, 1) . "\r\n", 3, "data/chutest/CHUTEST-XX.log");

        try {
            $response = $client->getAcsResponse($request);
            //print_r($response);
            error_log(date('Y-m-d H:i:s ') . __CLASS__ . '::' . __FUNCTION__ . ' @ '.
                'here 7777: ' . var_export('', 1) . "\r\n", 3, "data/chutest/CHUTEST-XX.log");


            json_result('验证码发送成功');
        }
        catch (ClientException  $e) {
            //print_r($e->getErrorCode());
            //print_r($e->getErrorMessage());

            error_log(date('Y-m-d H:i:s ') . __CLASS__ . '::' . __FUNCTION__ . ' @ '.
                'here 8888: ' . var_export('', 1) . "\r\n", 3, "data/chutest/CHUTEST-XX.log");


            json_error('验证码生成失败，请重试','40013');
        }
        catch (ServerException  $e) {
            //print_r($e->getErrorCode());
            //print_r($e->getErrorMessage());

            error_log(date('Y-m-d H:i:s ') . __CLASS__ . '::' . __FUNCTION__ . ' @ '.
                'here 9999: ' . var_export('', 1) . "\r\n", 3, "data/chutest/CHUTEST-XX.log");


            json_error('验证码生成失败，请重试','40013');
        }


    }

	
	

}
?>