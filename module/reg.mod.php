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

            case 'do_reg':
                $this->Do_reg(); //实际注册
                break;

            default:
                $this->Reg_page();  //注册页面
                break;
        }

    }

    //注册页面
    function Reg_page()
    {

        $page_title = '会员注册';
        include(template('register'));

    }

    //实际注册。返回json
    function Do_reg()
    {
        //用户提交：手机号、验证码、密码、确认密码。
        $cellphone = getPG('mobile');
        $vali_code = getPG('yzm');
        $password = getPG('password');
        $repassword = getPG('repassword');
        if($password != $repassword)
        {
            json_error('两次密码不相同，请重新输入','40013');
        }

        if(!$cellphone || !$vali_code || !$password)
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

        //判断该手机号是否已经注册过。
        $sql = "select member_id from qkdb_member where cellphone = '".addslashes($cellphone)."'"; //因为$cellphone是用户填的，所以需要防卡sql注入，加反斜杠。
        $query = $this->DatabaseHandler->Query($sql);
        $onemember = $this->DatabaseHandler->GetRow($query);
        if($onemember)
        {
            //已经注册过，无法重复注册。
            json_error('该手机号已经注册过，无法重复注册','40013');

        }
        else
        {
            //insert
            $insert_data = array(
                'cellphone' => addslashes($cellphone),
                'password' => md5($password),
                'reg_time' => TIMESTAMP,
            );
            $member_id_reg = $this->DatabaseHandler->insert('qkdb_member',$insert_data,true);

            if($member_id_reg > 0)
            {
                //注册成功
                json_result();
            }
            else
            {
                json_error('系统创建会员失败，请重试','40013');
            }
        }
    }


}

?>