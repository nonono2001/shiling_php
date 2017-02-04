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
            case 'do_bind_cellphone_openid':
                $this->Do_bind_cellphone_openid();
                break;

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

        error_log(date('Y-m-d H:i:s ') . __CLASS__ . '::' . __FUNCTION__ . ' @ '.
            '$xcx_code111: ' . var_export($xcx_code, 1) . "\r\n", 3, "data/chutest/CHUTEST-XX.log");

		$sessionkey_openid = code_to_sessionkey_openid($xcx_code);

        error_log(date('Y-m-d H:i:s ') . __CLASS__ . '::' . __FUNCTION__ . ' @ '.
            '$sessionkey_openid111: ' . var_export($sessionkey_openid, 1) . "\r\n", 3, "data/chutest/CHUTEST-XX.log");

        /*$sessionkey_openid，可能为空。
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
            json_error('code可能过期，无法用code换取到openid和session_key','40011');
		}
        else
        {
            //code换取openid和session_key成功。
            //微信联合登录成功。但第三方服务器还未登录成功。

            //在第三方服务器登录之前，要先判断该openid是否已绑定了手机号。手机号是第三方项目中，会员必需的属性，也算是唯一标识。
            //在qkdb_member表中，有openid字段。
            $sql = "select * from qkdb_member where openid = '".$sessionkey_openid_array['openid']."'";
            $query = $this->DatabaseHandler->Query($sql);
            $onemember = $this->DatabaseHandler->GetRow($query);

            if(!$onemember)
            {
                //说明尚未绑定本站的某个会员。
                json_error($xcx_code,'40012');//尚未绑定本站手机号，要把code返回给前端，以便前端绑定后把code再传回来。
            }
            else
            {
                //已经绑定过了。
                $session_value = $sessionkey_openid_array;
                $session_value['member_id'] = $onemember['member_id'];
                $third_session_id = gen_3rd_session($session_value);//生成第三方session。
                json_result('this is 3rd_session_id',$third_session_id);
            }

        }

	}

    //绑定手机号和openid的动作。
    function Do_bind_cellphone_openid()
    {
        /*前端传过来
        'xcx_code' => '021BNw3v1iR7Ea0i3t2v1jwb3v1BNw3E',
  'mobile' => '15899587743',
  'yzm' => '1234',
  'password' => '111',
  'repassword' => '111',
         *
         */
        error_log(date('Y-m-d H:i:s ') . __CLASS__ . '::' . __FUNCTION__ . ' @ '.
            '$_GET: ' . var_export($_GET, 1) . "\r\n", 3, "data/chutest/CHUTEST-XX.log");
        //前端会传来code、手机号、验证码、密码。
        $xcx_code = getPG('xcx_code');
        $cellphone = getPG('mobile');
        $vali_code = getPG('yzm');
        $password = getPG('password');
        $repassword = getPG('repassword');

        if($password != $repassword)
        {
            json_error('两次密码不相同，请重新输入','40013');
        }

        if(!$xcx_code || !$cellphone || !$vali_code || !$password)
        {
            json_error('缺少参数，请重新输入','40013');
        }

        //手机验证码，需要check它的正确性。
        $res = check_sms_vali_code($cellphone, $vali_code);
        if(!$res)
        {
            //验证码不正确，或者已过期。
            json_error('验证码错误或已过期，请重新获取验证码后提交','40013');
        }

        //拿code换取session_key 和 openid。
        error_log(date('Y-m-d H:i:s ') . __CLASS__ . '::' . __FUNCTION__ . ' @ '.
            '$xcx_code222: ' . var_export($xcx_code, 1) . "\r\n", 3, "data/chutest/CHUTEST-XX.log");

        $sessionkey_openid = code_to_sessionkey_openid($xcx_code);

        error_log(date('Y-m-d H:i:s ') . __CLASS__ . '::' . __FUNCTION__ . ' @ '.
            '$sessionkey_openid222: ' . var_export($sessionkey_openid, 1) . "\r\n", 3, "data/chutest/CHUTEST-XX.log");

        $sessionkey_openid_array = json_decode($sessionkey_openid,true);
        

        if(!$sessionkey_openid_array || $sessionkey_openid_array['errcode'])
        {
            //可能是api调用返回空，也可能返回失败信息
            json_error('code可能过期，无法用code换取到openid和session_key','40011');
        }
        else
        {
            $openid = $sessionkey_openid_array['openid'];
            $session_key = $sessionkey_openid_array['session_key'];
            //code换取openid和session_key成功。
            //下面进行绑定。
            //如果该手机号已经存在member表中，则update该条记录，写入openid和session_key。
            //如果该手机号不存在member表中，则新插入一条记录。
            $sql = "select member_id from qkdb_member where cellphone = '".addslashes($cellphone)."'"; //因为$cellphone是用户填的，所以需要防卡sql注入，加反斜杠。
            $query = $this->DatabaseHandler->Query($sql);
            $onemember = $this->DatabaseHandler->GetRow($query);

            $member_id_login = '';//要登录的member_id。
            if($onemember)
            {
                //update
                $update_data = array(
                    'password' => md5($password),
                    'openid' => $openid,
                    'session_key' => $session_key,
                    'lastbindtime' => time(),
                );

                $this->DatabaseHandler->update('qkdb_member',$update_data, "member_id = '".$onemember['member_id']."'");

                $member_id_login = $onemember['member_id'];
            }
            else
            {
                //insert
                $insert_data = array(
                    'cellphone' => addslashes($cellphone),
                    'password' => md5($password),
                    'openid' => $openid,
                    'session_key' => $session_key,
                    'lastbindtime' => time(),
                );
                $member_id_login = $this->DatabaseHandler->insert('qkdb_member',$insert_data,true);
            }

            //到这里，绑定成功。
            //生成3rd_session
            $session_value = $sessionkey_openid_array;
            $session_value['member_id'] = $member_id_login;
            $third_session_id = gen_3rd_session($session_value);//生成第三方session。
            json_result('this is 3rd_session_id',$third_session_id);

        }
    }

	
	

}
?>