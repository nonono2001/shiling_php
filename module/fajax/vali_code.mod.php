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

        $code_json = json_encode( array('code'=> strval($vali_code)), 1);
        //调用sdk，向手机号发送一次短信。
        //参考https://help.aliyun.com/document_detail/44368.html?spm=5176.doc44327.6.581.MadQEG
        include_once 'include/aliyun_sms/aliyun-php-sdk-core/Config.php';
        use Sms\Request\V20160927 as Sms;
        $iClientProfile = DefaultProfile::getProfile("cn-hangzhou", "your accessKey", "your accessSecret");
        $client = new DefaultAcsClient($iClientProfile);
        $request = new Sms\SingleSendSmsRequest();
        $request->setSignName("食令");/*签名名称*/
        $request->setTemplateCode("SMS_44480306");/*模板code*/
        $request->setRecNum($cellphone);/*目标手机号*/
        $request->setParamString($code_json);/*模板变量，数字一定要转换为字符串*/
        try {
            $response = $client->getAcsResponse($request);
            //print_r($response);
            json_result('验证码发送成功');
        }
        catch (ClientException  $e) {
            //print_r($e->getErrorCode());
            //print_r($e->getErrorMessage());
            json_error('验证码生成失败，请重试','40013');
        }
        catch (ServerException  $e) {
            //print_r($e->getErrorCode());
            //print_r($e->getErrorMessage());
            json_error('验证码生成失败，请重试','40013');
        }


    }

	
	

}
?>