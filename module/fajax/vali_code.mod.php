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
    {
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

        $config = getSetting( 'sys_setting' );

        $aliyun_AccessKeyID = $config['aliyun_AccessKeyID']; //微信服务器提供给调用者的appid和appsecret。
        $aliyun_AccessKeySecret = $config['aliyun_AccessKeySecret'];

        //阿里云提供的发短信sdk不好用，搜出了一篇文章，自己写原生的发短信函数，抄来直接就用了。
        //原文：http://blog.csdn.net/bakw/article/details/53914014
        $sms_type = getPG('type');//短信类型
        if(!$sms_type || $sms_type == 1)
        {
            //默认短信类型为 绑定手机号和openid的短信验证码
            $TemplateCode = "SMS_44480306";
            $ParamString = json_encode( array('code'=> strval($vali_code)), 1);
        }
        else if($sms_type == 2)
        {
            //注册验证码
            $TemplateCode = "SMS_44315145";
            $param_arr = array(
                'code' => strval($vali_code),
                'product' => '食令',
            );
            $ParamString = json_encode($param_arr, 1);
        }
        else
        {
            //默认短信类型
            $TemplateCode = "SMS_44480306";
            $ParamString = json_encode( array('code'=> strval($vali_code)), 1);
        }
        $res = $this->SendSMS($cellphone, $ParamString, "食令", $TemplateCode,$aliyun_AccessKeyID,$aliyun_AccessKeySecret);
        if($res)
        {
            json_result();
        }
        else
        {
            json_error('验证码发送失败，请重试','40013');
        }
    }



    //调用阿里云发短信接口api。成功返回true，失败返回false。
    //原文：http://blog.csdn.net/bakw/article/details/53914014
    function SendSMS($RecNum,$ParamString,$SignName,$TemplateCode,$AccessKeyId,$AccessKeySecret)
    {
        $url='https://sms.aliyuncs.com/';//短信网关地址
        $Params['Action']='SingleSendSms';//操作接口名，系统规定参数，取值：SingleSendSms
        //$Params['RegionId']='cn-hangzhou';//机房信息
        $Params['AccessKeyId']=$AccessKeyId;//阿里云颁发给用户的访问服务所用的密钥ID
        //$Params['Format']='JSON';//返回值的类型，支持JSON与XML。默认为XML
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
        $result=@simplexml_load_string(file_get_contents($url,false,$httphead));

        if(isset($result->Code) || !$result)
        {
            return false;
        }
        return true;
    }




}
?>