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
//        include_once 'include/aliyun_sms/aliyun-php-sdk-core/Config.php';
//
//        error_log(date('Y-m-d H:i:s ') . __CLASS__ . '::' . __FUNCTION__ . ' @ '.
//            'here 111: ' . var_export('', 1) . "\r\n", 3, "data/chutest/CHUTEST-XX.log");
//
//        use Sms\Request\V20160927 as Sms;
//
//        error_log(date('Y-m-d H:i:s ') . __CLASS__ . '::' . __FUNCTION__ . ' @ '.
//            'here 222: ' . var_export('', 1) . "\r\n", 3, "data/chutest/CHUTEST-XX.log");
//
//        $iClientProfile = DefaultProfile::getProfile("cn-hangzhou", $aliyun_AccessKeyID, $aliyun_AccessKeySecret);
//
//        error_log(date('Y-m-d H:i:s ') . __CLASS__ . '::' . __FUNCTION__ . ' @ '.
//            'here 333: ' . var_export('', 1) . "\r\n", 3, "data/chutest/CHUTEST-XX.log");
//
//        $client = new DefaultAcsClient($iClientProfile);
//
//        error_log(date('Y-m-d H:i:s ') . __CLASS__ . '::' . __FUNCTION__ . ' @ '.
//            'here 444: ' . var_export('', 1) . "\r\n", 3, "data/chutest/CHUTEST-XX.log");
//
//        $request = new Sms\SingleSendSmsRequest();
//
//        error_log(date('Y-m-d H:i:s ') . __CLASS__ . '::' . __FUNCTION__ . ' @ '.
//            'here 555: ' . var_export('', 1) . "\r\n", 3, "data/chutest/CHUTEST-XX.log");
//
//        $request->setSignName("食令");/*签名名称*/
//        $request->setTemplateCode("SMS_44480306");/*模板code*/
//        $request->setRecNum($cellphone);/*目标手机号*/
//        $request->setParamString($code_json);/*模板变量，数字一定要转换为字符串*/
//
//        error_log(date('Y-m-d H:i:s ') . __CLASS__ . '::' . __FUNCTION__ . ' @ '.
//            '$cellphone: ' . var_export($cellphone, 1) . "\r\n", 3, "data/chutest/CHUTEST-XX.log");
//
//        try {
//            $response = $client->getAcsResponse($request);
//            //print_r($response);
//            error_log(date('Y-m-d H:i:s ') . __CLASS__ . '::' . __FUNCTION__ . ' @ '.
//                'here 7777: ' . var_export('', 1) . "\r\n", 3, "data/chutest/CHUTEST-XX.log");
//
//
//            json_result('验证码发送成功');
//        }
//        catch (ClientException  $e) {
//            //print_r($e->getErrorCode());
//            //print_r($e->getErrorMessage());
//
//            error_log(date('Y-m-d H:i:s ') . __CLASS__ . '::' . __FUNCTION__ . ' @ '.
//                'here 8888: ' . var_export('', 1) . "\r\n", 3, "data/chutest/CHUTEST-XX.log");
//
//
//            json_error('验证码生成失败，请重试','40013');
//        }
//        catch (ServerException  $e) {
//            //print_r($e->getErrorCode());
//            //print_r($e->getErrorMessage());
//
//            error_log(date('Y-m-d H:i:s ') . __CLASS__ . '::' . __FUNCTION__ . ' @ '.
//                'here 9999: ' . var_export('', 1) . "\r\n", 3, "data/chutest/CHUTEST-XX.log");
//
//
//            json_error('验证码生成失败，请重试','40013');
//        }

        $res = $this->SendSMS($cellphone, $code_json, "食令", "SMS_44480306",$aliyun_AccessKeyID,$aliyun_AccessKeySecret);
        error_log(date('Y-m-d H:i:s ') . __CLASS__ . '::' . __FUNCTION__ . ' @ '.
            'SendSMS res: ' . var_export($res, 1) . "\r\n", 3, "data/chutest/CHUTEST-XX.log");

    }




    function SendSMS($RecNum,$ParamString,$SignName,$TemplateCode,$AccessKeyId,$AccessKeySecret)
    {
        $url='https://sms.aliyuncs.com/';//短信网关地址
        $Params['Action']='SingleSendSms';//操作接口名，系统规定参数，取值：SingleSendSms
        //$Params['RegionId']='cn-hangzhou';//机房信息
        $Params['AccessKeyId']=$AccessKeyId;//阿里云颁发给用户的访问服务所用的密钥ID
        $Params['Format']='JSON';//返回值的类型，支持JSON与XML。默认为XML
        $Params['ParamString']=rawurlencode($ParamString);//短信模板中的变量；数字需要转换为字符串；个人用户每个变量长度必须小于15个字符。
        $Params['RecNum']=$RecNum;//目标手机号
        $Params['SignatureMethod']='HMAC-SHA1';//签名方式，目前支持HMAC-SHA1
        $Params['SignatureNonce']=time();//唯一随机数
        $Params['SignatureVersion']='1.0';//签名算法版本，目前版本是1.0
        $Params['SignName']=rawurlencode($SignName);//管理控制台中配置的短信签名（状态必须是验证通过）
        $Params['TemplateCode']=$TemplateCode;//管理控制台中配置的审核通过的短信模板的模板CODE（状态必须是验证通过）
        $Params['Timestamp']=rawurlencode(gmdate("Y-m-d\TH:i:s\Z"));//请求的时间戳。日期格式按照ISO8601标准表示，
        //并需要使用UTC时间。格式为YYYY-MM-DDThh:mm:ssZ
        $Params['Version']='2016-09-27';//API版本号，当前版本2016-09-27
        ksort($Params);
        $PostData='';
        foreach ($Params as $k => $v) $PostData.=$k.'='.$v.'&';
        $PostData.='&Signature='.rawurlencode(base64_encode(hash_hmac('sha1','POST&%2F&'.rawurlencode(substr($PostData,0,-1)),$AccessKeySecret.'&',true)));
        $httphead['http']['method']="POST";
        $httphead['http']['header']="Content-type:application/x-www-form-urlencoded\n";
        $httphead['http']['header'].="Content-length:".strlen($PostData)."\n";
        $httphead['http']['content']=$PostData;
        $httphead=stream_context_create($httphead);

        return $httphead;
    }




}
?>