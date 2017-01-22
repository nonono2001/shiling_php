

function input_defval_hide(t)
{
	if($(t).attr('defval') == $(t).val())
	{
		$(t).val('');
		$(t).css('color','#333');
	}
}

function input_defval_show(t)
{
	if($(t).val() == '')
	{
		$(t).val($(t).attr('defval'));
		$(t).css('color','#999');
	}

}

function show_obj(objid)
{
	$('#'+objid).show();


}


function hide_obj(objid)
{
	$('#'+objid).hide();

}

//修改某个input的value值
function modify_input_value(objid, objvalue)
{
	$("#"+objid).val(objvalue);

}

/**
**  检测邮箱地址格式
**/
function check_email_format(t,msgtip)
{
	var email = $.trim($(t).val());
	if(!isEmail(email)) //邮箱地址输入格式有误
	{
		$("#"+msgtip).html('邮箱格式有误，请修改');
		$("#"+msgtip).removeClass('mb_kongbai');
		$("#"+msgtip).addClass('mb_error');
		modify_input_value('is_email_ok', '0');
		return;

	}
	else
	{
		//邮箱格式没有错，下面要ajax到数据库检查这个邮箱账户是否已存在。
		$.ajax({
			type:"post",
			async : true,//false表示同步，true表示异步
			url:"ajax.php?mod=reg&code=check_emailaccount",
			data:{emailaccount:email},
			beforeSend: function(XMLHttpRequest){
				$("#"+msgtip).html('正在检测中...');
				$("#"+msgtip).removeClass('mb_kongbai');
				$("#"+msgtip).removeClass('mb_error');
			},
			success: function(data){
				if(!data)//表示该email已存在于数据库中
				{
					$("#"+msgtip).html('该邮箱已被注册，请重新输入');
					$("#"+msgtip).removeClass('mb_kongbai');
					$("#"+msgtip).addClass('mb_error');
					modify_input_value('is_email_ok', '0');
				}
				else//表示数据库中还不存在该email，可以注册
				{
					$("#"+msgtip).removeClass('mb_error');
					$("#"+msgtip).addClass('mb_kongbai');
					$("#"+msgtip).html('<i class="duigou"></i>');
					modify_input_value('is_email_ok', '1');
				}
			}
			});
		return;
	}



}

function isEmail(str)
{
   var reg = /^(\w)+(\.\w+)*@(\w)+((\.\w+)+)$/;
   return reg.test(str);
}

/**
**  注册时，检测昵称格式
**/
function check_nickname_format(t,msgtip)
{
	var nick_name = $.trim($(t).val());
	var len = nick_name.length;
	if(len>16 || len<1)//nickname长度不对
	{
		$("#"+msgtip).html('长度为1~16个字符');
		$("#"+msgtip).removeClass('mb_kongbai');
		$("#"+msgtip).addClass('mb_error');
		modify_input_value('is_nickname_ok', '0');
		return;
	}
	else
	{
		var ret = isNicknameHefa(nick_name);
		if( !ret )//nickname格式错误
		{
			$("#"+msgtip).html('只能包含字母、数字、中文、下划线');
			$("#"+msgtip).removeClass('mb_kongbai');
			$("#"+msgtip).addClass('mb_error');
			modify_input_value('is_nickname_ok', '0');
			return;
		}
		else
		{	
			//nickname格式正确，下面要ajax到数据库检查这个昵称是否已存在。
			$.ajax({
				type:"post",
				async : true,//false表示同步，true表示异步
				url:"ajax.php?mod=reg&code=check_nickname",
				data:{nickname:nick_name},
				beforeSend: function(XMLHttpRequest){
					$("#"+msgtip).html('正在检测中...');
					$("#"+msgtip).removeClass('mb_kongbai');
					$("#"+msgtip).removeClass('mb_error');
				},
				success: function(data){
					if(!data)//表示该nickname已存在于数据库中
					{
						$("#"+msgtip).html('该昵称已被注册，请重新输入');
						$("#"+msgtip).removeClass('mb_kongbai');
						$("#"+msgtip).addClass('mb_error');
						modify_input_value('is_nickname_ok', '0');
					}
					else//表示数据库中还不存在该nickname，可以注册
					{
						$("#"+msgtip).removeClass('mb_error');
						$("#"+msgtip).addClass('mb_kongbai');
						$("#"+msgtip).html('<i class="duigou"></i>');
						modify_input_value('is_nickname_ok', '1');
					}
				}
				});
			return;
		}
	}

}

/**
**  修改昵称时，检测昵称格式
**/
function check_nickname_format_modify(t)
{
	var nick_name = $.trim($(t).val());
	var len = nick_name.length;
	
	if(len>16 || len<1)//nickname长度不对
	{
		$.jBox.tip('昵称长度为1~16个字符','error');
		return;
	}
	else
	{
		var ret = isNicknameHefa(nick_name);
		if( !ret )//nickname格式错误
		{
			$.jBox.tip('昵称只能包含字母、数字、中文、下划线','error');
			return;
		}
		else
		{	
			//nickname格式正确，下面要ajax到数据库检查这个昵称是否已存在。
			$.ajax({
				type:"post",
				async : true,//false表示同步，true表示异步
				url:"ajax.php?mod=account&code=check_nickname_modify",
				data:{nickname:nick_name},
				beforeSend: function(XMLHttpRequest){
					
				},
				success: function(data){
					data = jQuery.parseJSON( data );
					if(!data.done)//表示该nickname已存在于数据库中，或其他错误。
					{
						$.jBox.tip(data.msg,'error');
					}
					else//表示数据库中还不存在该nickname，可以注册
					{
						$.jBox.tip('该昵称可以使用！','success');
					}
				}
				});
			return;
		}
	}

}

//判断nickname合法性，是否只由中文、英文、数字、下划线组成。
function isNicknameHefa(nickname)
{
	var regex = /^[A-Za-z0-9_\u4E00-\u9FA5]+$/; //百度搜索：jquery字符串中文,字母,数字,下划线组成
	// regex = /^[^_][A-Za-z]*[a-z0-9_]*$/ ;这个正则只能验证字母,数字,下划线，不能验证中文
	var ret = regex.test(nickname);
	if( ret )
	{
		return true;
	}
	else
	{
		return false;
	}

}

function check_password_format(t,msgtip)
{
	//6~16位字符。
	var len = $(t).val().length;
	if(len>16 || len<6)//密码长度不对
	{
		$("#"+msgtip).html('长度应为6-16个字符');
		$("#"+msgtip).removeClass('mb_kongbai');
		$("#"+msgtip).addClass('mb_error');
		modify_input_value('is_password_ok', '0');
		return;
	}
	else//密码OK
	{
		$("#"+msgtip).removeClass('mb_error');
		$("#"+msgtip).addClass('mb_kongbai');
		$("#"+msgtip).html('<i class="duigou"></i>');
		modify_input_value('is_password_ok', '1');
		return;
	}


}

function check_passwordagain_format(t,msgtip)
{
	//跟password一致，位数在6~16位。
	var pwd = $("#password").val();//第一次输的密码
	var pwd_again = $(t).val();//重复确认的密码
	
	if(!pwd_again)
	{
		$("#"+msgtip).html('请再输一次密码');
		$("#"+msgtip).removeClass('mb_kongbai');
		$("#"+msgtip).addClass('mb_error');
		modify_input_value('is_passwordagain_ok', '0');
		return;
	}
	else if(pwd == pwd_again)
	{
		$("#"+msgtip).removeClass('mb_error');
		$("#"+msgtip).addClass('mb_kongbai');
		$("#"+msgtip).html('<i class="duigou"></i>');
		modify_input_value('is_passwordagain_ok', '1');
		return;

	}
	else
	{
		$("#"+msgtip).html('两次密码输入不一致');
		$("#"+msgtip).removeClass('mb_kongbai');
		$("#"+msgtip).addClass('mb_error');
		modify_input_value('is_passwordagain_ok', '0');
		return;
	}

}

//新建或修改便利贴的按钮被按下
//弹出大的便利贴，这个便利贴里面是输入框，可以编辑的。
function show_postnote_editor(postid,gzid)
{
	//必需要登录。
	if(!$("#current_uid_page_info").val())//未登录，弹出登录对话框
	{
		login_dialog_show();
		return ;
	}
	//参数postid是便利贴id，如果postid>0说明要修改这个便利贴；否则是要新建便利贴。
	if(postid > 0)//修改，或删除便利贴
	{
		//ajax得到现有便利贴的内容。
		$.ajax({
			type:"post",
			async : true,//false表示同步，true表示异步
			url:"ajax.php?mod=topic&code=get_postnote",
			data:{gzid:gzid, postid:postid},
			beforeSend: function(XMLHttpRequest){
				
			},
			success: function(data){//data是json格式
				
				data = jQuery.parseJSON( data );
				if(!data.done)//获取便利贴内容失败
				{
					$.jBox.tip(data.msg,'error');
					return;
					
				}
				else//获取便利贴内容成功
				{
					//第一步，创建半透明层背景
					make_opacity_layer();
					
					//第二步，创建对话框，本函数的对话框是一个便利贴。
					var postwrap = '<div id="postWrap" style="width:256px;height:256px;left:50%;top:50%;position:fixed;background:url(templates/images/postit_empty.png) no-repeat;z-index:10001;margin-left:-128px;margin-top:-128px;"></div>';
					$('body').append(postwrap);
					
					//右上角的叉叉
					var chacha = '<a href="javascript:void(0);" onclick="close_post(\'bgDiv\',\'postWrap\');" style="position: absolute;top: 25px;right: 25px;background: url(templates/images/chacha.png) 0 -146px no-repeat;font-size: 9px;width: 9px;height: 9px;overflow: hidden;"></a>';
					$("#postWrap").append(chacha);
					
					//在postWrap内添加textarea对象
				    var poststr_textarea = '<textarea id="postStr" style="float:left;width:214px;height:179px;margin-left:20px;margin-top:40px;font-size:14px;font-family:microsoft yahei;word-break:break-all;text-align:left;overflow:hidden;background: none;border: none;padding: 0px;" onkeyup="changeTip_by_val(\'postStr\',\'poststr_tip\',1,135);"></textarea>';
				    $("#postWrap").append(poststr_textarea);
				    
				    $("#postStr").val(data.msg);
				    
				    $("#postStr").focus();
				    
				    //字数提示
				    var poststr_tip = '<div id="poststr_tip" style="position: absolute;top: 243px;right: -30px;font-size:14px;font-family:microsoft yahei;">135字以内</div>';
				    $("#postWrap").append(poststr_tip);
				    
				    //修改便利贴、删除便利贴、取消 按钮。
				    var confirm_cancel_btn =  '<div style="width:400px;float:left;margin-top: 50px;">';
				    confirm_cancel_btn +=     '<input id="create_postnote_confirm_btn" type="button" value="修改便利贴" class="btnText" style="height:26px;line-height:26px;margin:0px;box-shadow:none;" onclick="do_update_postnote(this, \''+postid+'\', \''+gzid+'\');"/>';
				    confirm_cancel_btn +=     '<input id="create_postnote_confirm_btn" type="button" value="删除便利贴" class="btnText" style="height:26px;line-height:26px;margin:0px;margin-left:20px;box-shadow:none;" onclick="do_delete_postnote(this, \''+postid+'\', \''+gzid+'\');"/>';
				    confirm_cancel_btn +=     '<input id="create_postnote_cancel_btn" type="button" value="取消" onclick="close_post(\'bgDiv\',\'postWrap\');" class="canlText" style="margin-left:20px;"/>';
				    confirm_cancel_btn +=     '</div>';
				    $("#postWrap").append(confirm_cancel_btn);
				}
			}
			});
		
	}
	else //新建便利贴
	{
		//第一步，创建半透明层背景
		make_opacity_layer();
		
		//第二步，创建对话框，本函数的对话框是一个便利贴。
		var postwrap = '<div id="postWrap" style="width:256px;height:256px;left:50%;top:50%;position:fixed;background:url(templates/images/postit_empty.png) no-repeat;z-index:10001;margin-left:-128px;margin-top:-128px;"></div>';
		$('body').append(postwrap);
		
		//右上角的叉叉
		var chacha = '<a href="javascript:void(0);" onclick="close_post(\'bgDiv\',\'postWrap\');" style="position: absolute;top: 25px;right: 25px;background: url(templates/images/chacha.png) 0 -146px no-repeat;font-size: 9px;width: 9px;height: 9px;overflow: hidden;"></a>';
		$("#postWrap").append(chacha);
		
		//在postWrap内添加textarea对象
	    var poststr_textarea = '<textarea id="postStr" style="float:left;width:214px;height:179px;margin-left:20px;margin-top:40px;font-size:14px;font-family:microsoft yahei;word-break:break-all;text-align:left;overflow:hidden;background: none;border: none;padding: 0px;" onkeyup="changeTip_by_val(\'postStr\',\'poststr_tip\',1,135);"></textarea>';
	    $("#postWrap").append(poststr_textarea);
	    
	    $("#postStr").focus();
	    
	    //字数提示
	    var poststr_tip = '<div id="poststr_tip" style="position: absolute;top: 243px;right: -30px;font-size:14px;font-family:microsoft yahei;">135字以内</div>';
	    $("#postWrap").append(poststr_tip);
	    
	    //确定、取消 按钮。
	    var confirm_cancel_btn = '<div style="width:100%;float:left;margin-top: 50px;"><input id="create_postnote_confirm_btn" type="button" value="创建便利贴" class="btnText" style="height:26px;line-height:26px;margin:0px;margin-left:40px;box-shadow:none;" onclick="do_create_postnote(this, \''+gzid+'\');"/><input id="create_postnote_cancel_btn" type="button" value="取消" onclick="close_post(\'bgDiv\',\'postWrap\');" class="canlText" style="margin-left:20px;"/></div>';
	    $("#postWrap").append(confirm_cancel_btn);
	    
	}
	
}

//做创建便利贴的动作
function do_create_postnote(t, gzid)
{
	//检查便利贴的字数是否为空。
	var poststr = $("#postStr").val();
	if(poststr.length <= 0)
	{
		$("#poststr_tip").html('<span style="color:rgb(155, 0, 0);">便利贴内容不能为空哦！</span>');
		return ;
	}
	
	var attrval;
	//ajax传送便利贴内容到数据库
	$.ajax({
		type:"post",
		async : true,//false表示同步，true表示异步
		url:"ajax.php?mod=topic&code=create_postnote",
		data:{gzid:gzid,poststr:poststr},
		beforeSend: function(XMLHttpRequest){
			//将确定按钮给disable掉。
			attrval = $(t).attr("onclick");
			$(t).attr("onclick","");
		},
		success: function(data){//data是json格式
			
			data = jQuery.parseJSON( data );
			if(data.done)//创建便利贴成功
			{
				$("#poststr_tip").html('<span style="color:rgb(155, 0, 0);">恭喜您，便利贴创建成功啦！</span>');
				setTimeout('location.reload();', 1000);//1秒后刷新页面
				return;
			}
			else//创建失败
			{
				//将确定按钮给点亮。
				$(t).attr("onclick",attrval);
				//失败原因提示
				$("#poststr_tip").html('<span style="color:rgb(155, 0, 0);">'+data.msg+'</span>');
				
				return;
			}
		}
		});

}

//修改便利贴
function do_update_postnote(t, postnoteid, gzid)
{
	//检查便利贴的字数是否为空。
	var poststr = $("#postStr").val();
	if(poststr.length <= 0)
	{
		$("#poststr_tip").html('<span style="color:rgb(155, 0, 0);">便利贴内容不能为空哦！</span>');
		return ;
	}
	
	var attrval;
	//ajax传送便利贴内容到数据库
	$.ajax({
		type:"post",
		async : true,//false表示同步，true表示异步
		url:"ajax.php?mod=topic&code=update_postnote",
		data:{postnoteid:postnoteid, gzid:gzid,poststr:poststr},
		beforeSend: function(XMLHttpRequest){
			//将按钮给disable掉。
			attrval = $(t).attr("onclick");
			$(t).attr("onclick","");
		},
		success: function(data){//data是json格式
			
			data = jQuery.parseJSON( data );
			if(data.done)//修改便利贴成功
			{
				$("#poststr_tip").html('<span style="color:rgb(155, 0, 0);">恭喜您，便利贴修改成功啦！</span>');
				setTimeout('location.reload();', 1000);//1秒后刷新页面
				return;
			}
			else//修改失败
			{
				//将修改按钮给点亮。
				$(t).attr("onclick",attrval);
				//失败原因提示
				$("#poststr_tip").html('<span style="color:rgb(155, 0, 0);">'+data.msg+'</span>');
				
				return;
			}
		}
		});
}

//删除便利贴
function do_delete_postnote(t, postnoteid, gzid)
{
	var attrval;
	
	$.ajax({
		type:"post",
		async : true,//false表示同步，true表示异步
		url:"ajax.php?mod=topic&code=delete_postnote",
		data:{postnoteid:postnoteid, gzid:gzid},
		beforeSend: function(XMLHttpRequest){
			//将按钮给disable掉。
			attrval = $(t).attr("onclick");
			$(t).attr("onclick","");
		},
		success: function(data){//data是json格式
			
			data = jQuery.parseJSON( data );
			if(data.done)//删除便利贴成功
			{
				$("#poststr_tip").html('<span style="color:rgb(155, 0, 0);">删除成功！</span>');
				setTimeout('location.reload();', 1000);//1秒后刷新页面
				return;
			}
			else//删除失败
			{
				//将修改按钮给点亮。
				$(t).attr("onclick",attrval);
				//失败原因提示
				$("#poststr_tip").html('<span style="color:rgb(155, 0, 0);">'+data.msg+'</span>');
				
				return;
			}
		}
		});

}

//小的便利贴被点击事件，弹出一张大的便利贴，也就是查看便利贴内容。
function show_postnote_content(postid, gzid)
{
	if(postid<=0 || gzid<=0)
	{
		$.jBox.tip('参数错误！','error');
		return ;
	}
	
	//ajax得到现有便利贴的内容。
	$.ajax({
		type:"post",
		async : true,//false表示同步，true表示异步
		url:"ajax.php?mod=topic&code=get_postnote",
		data:{gzid:gzid, postid:postid},
		beforeSend: function(XMLHttpRequest){
			
		},
		success: function(data){//data是json格式
			
			data = jQuery.parseJSON( data );
			if(!data.done)//获取便利贴内容失败
			{
				$.jBox.tip(data.msg,'error');
				return;
				
			}
			else//获取便利贴内容成功
			{
				//第一步，创建半透明层背景
				make_opacity_layer();
				
				//第二步，创建对话框，本函数的对话框是一个便利贴。
				var postwrap = '<div id="postWrap" style="width:256px;height:256px;left:50%;top:50%;position:fixed;background:url(templates/images/postit_empty.png) no-repeat;z-index:10001;margin-left:-128px;margin-top:-128px;"></div>';
				$('body').append(postwrap);
				
				//右上角的叉叉
				var chacha = '<a href="javascript:void(0);" onclick="close_post(\'bgDiv\',\'postWrap\');" style="position: absolute;top: 25px;right: 25px;background: url(templates/images/chacha.png) 0 -146px no-repeat;font-size: 9px;width: 9px;height: 9px;overflow: hidden;"></a>';
				$("#postWrap").append(chacha);
				
				//在postWrap内添加textarea对象，不可编辑的 disabled="disabled"。
			    var poststr_textarea = '<div id="postStr" style="float:left;width:214px;height:179px;margin-left:20px;margin-top:40px;font-size:14px;font-family:microsoft yahei;word-break:break-all;text-align:left;overflow:hidden;background: none;border: none;padding: 0px;color:#000;"></div>';
			    $("#postWrap").append(poststr_textarea);
			    
			    var retval = data.retval;
			    $("#postStr").html(retval.poststr_nospecial);
			}
		}
		});
}

//在整个页面创建半透明层
function make_opacity_layer()
{
	//var   sWidth,sHeight; 
    //sWidth=document.body.offsetWidth;//浏览器工作区域内页面宽度 
    //sHeight=screen.height;//屏幕高度（垂直分辨率） 
	var   bgObj=document.createElement("div");//创建一个div对象（背景层） 
	
	//参考：http://blog.163.com/wangjiecaicai@126/blog/static/25757126200791195480/，http://www.jb51.net/css/11620.html
	//定义div属性，即相当于 
    // <div   id="bgDiv"   style="position:absolute;   top:0;   background-color:#777;   filter:progid:DXImagesTransform.Microsoft.Alpha(style=3,opacity=25,finishOpacity=75);   opacity:0.6;   left:0;   width:918px;   height:768px;   z-index:10000;" > </div > 
	bgObj.setAttribute( "id", "bgDiv"); 
    bgObj.style.position="fixed"; 
    bgObj.style.top="0"; 
    bgObj.style.left="0"; 
    bgObj.style.background="#777"; 
    bgObj.style.opacity="0.6"; 
    bgObj.style.filter="alpha(opacity=60)"; //for IE
    bgObj.style.width="100%"; 
    bgObj.style.height="100%";
    bgObj.style.zIndex   =   "10000"; 
    $("body").append(bgObj);//在body内添加该div对象 
    $("#bgDiv").css("-moz-opacity","0.6"); //for FF
}

//点击“去购买”后会到一个分成情况描述页go_des.html，这个描述页刚加载时就会运行该函数
function create_touming_layer()
{
	//第一步，创建半透明层背景
	var   bgObj=document.createElement("div");//创建一个div对象（背景层） 
	
	//参考：http://blog.163.com/wangjiecaicai@126/blog/static/25757126200791195480/，http://www.jb51.net/css/11620.html
	//定义div属性，即相当于 
    // <div   id="bgDiv"   style="position:absolute;   top:0;   background-color:#777;   filter:progid:DXImagesTransform.Microsoft.Alpha(style=3,opacity=25,finishOpacity=75);   opacity:0.6;   left:0;   width:918px;   height:768px;   z-index:10000;" > </div > 
	bgObj.setAttribute( "id", "bgDiv"); 
    bgObj.style.position="fixed"; 
    bgObj.style.top="0"; 
    bgObj.style.left="0"; 
    bgObj.style.background="#777"; 
    bgObj.style.opacity="0.6"; 
    bgObj.style.filter="alpha(opacity=60)"; //for IE
    bgObj.style.width="100%"; 
    bgObj.style.height="100%";
    bgObj.style.zIndex   =   "10000"; 
    $("#frame_wrap").after(bgObj);//在body内添加该div对象 
    $("#bgDiv").css("-moz-opacity","0.6"); //for FF
	
}

function close_post(bgid,postid)
{
	$("#"+bgid).remove();
	$("#"+postid).remove();
	
}


//点击发布格子按钮触发，弹出发布框
//参数是架子id，说明本次发布格子，是想放在哪个架子下，默认为用户选好该架子。
function showgezipub(aimjid)
{
	$.jBox("post:ajax.php?mod=gezi_publish&aimjid="+aimjid,{
		id: 'gezi_pub',
	    title: "创建格子（每个格子就是一个宝贝晒单）",
	    width: 650,
	    height: 283
	});

}

//创建架子弹出层
function showhuojiapub()
{
	$.jBox("post:ajax.php?mod=jiazi_publish",{
		id: 'jiazi_pub',
		title: '创建架子',
		width:470,
		height:420
		
	});
	
}

//修改架子信息弹出层。参数jid是要修改信息的那个架子id
function showhuojiamodify(jid)
{
	$.jBox("post:ajax.php?mod=jiazi_publish&code=updatejiazi_dialog&jid="+jid,{
		id: 'jiazi_update',
		title: '修改架子信息',
		width:470,
		height:420
		
	});
}


//文本框输入时的字数检测，并随着字数的变化改变文本框下的提示语句
function changeTip_by_val(textid,tipid,minword,maxword)
{
	 var content = $.trim($('#'+textid).val()); //文本框中的字符串首尾的空格会被去除，不占字数，也不会被传到数据库。
	  if ( 0 >= content.length ) //即用户还没有输入任何字符
	  {
		$("#"+tipid).html( minword+"~"+maxword+"字" );
	  }
	  else if( 0 < content.length && content.length < minword ) //用户输入了1~minword-1个字符。
	  {
		word_need = minword-content.length; //还需要再输入的字符个数。
		$("#"+tipid).html('还需要输入<span style="color:red; font-weight:bold;">' + word_need + '</span>字');
	  }
	  else if( minword <= content.length && content.length <=maxword ) //用户输入了minword~maxword个字符。
	  {
		word_max = maxword - content.length; //还可以再输入的字符个数。
		$("#"+tipid).html('还可以输入<span style="color:green; font-weight:bold;">' + word_max + '</span>字');
	  }
	  else //用户输入了maxword+1或更多的字符。
	  {
		$('#'+textid).val( content.substr( 0, maxword ) );
	  }
	
}

//检测格子铺名称的合法性
function check_gzpname(t,gzpid)
{
	//格子铺名称只能包含中文、字母、数字、下划线。长度在1~25字符，不能为空。
	var gzpname = $.trim($(t).val());
	var len = gzpname.length;
	
	if(len>25 || len<1)//格子铺名称长度不对
	{
		$.jBox.tip('名称不能为空，最多25个字符。', 'error');
		return;
	}
	else
	{
		var ret = isNicknameHefa(gzpname);
		if( !ret )//格子铺名称格式错误
		{
			$.jBox.tip('格子铺名称格式错误，请重新输入。', 'error');
			return;
		}
		else
		{
			//格子铺名称格式正确，下面要ajax到数据库检查这个昵称是否已存在。
			$.ajax({
				type:"post",
				async : true,//false表示同步，true表示异步
				url:"ajax.php?mod=account&code=check_gzpname",
				data:{gzpname:gzpname,gzpid:gzpid},
				beforeSend: function(XMLHttpRequest){
					
				},
				success: function(data){
					if(!data)//表示该格子铺名称已存在于数据库中
					{
						$.jBox.tip('该名称已被占用，请重新输入。', 'error');
						return;
					}
					else//表示数据库中还不存在该格子铺名称，可以使用
					{
						$.jBox.tip('该名称可以使用。', 'success');
						return;
					}
				}
				});
			return;
		}
	}
	
}

//显示某个DOM元素
function show_element(objid)
{
	$("#"+objid).show();
}
function show_element_fade(objid)
{
	if($("#"+objid).is(":hidden"))
	{
		$("#"+objid).fadeIn();
	}
	
}
//隐藏某个DOM元素
function hide_element(objid)
{
	if(!($("#"+objid).is(":hidden")))
	{
		$("#"+objid).hide();
	}
	
}
function hide_element_fade(objid)
{
	$("#"+objid).fadeOut();
}

//设置某个element的value值。
function set_element_val(inputid, aimval)
{
	$("#"+inputid).val(aimval);
}

//替换表情
function replace_em(str){
//	str = str.replace(/\</g,'&lt;');
//	str = str.replace(/\>/g,'&gt;');
	str = str.replace(/\n/g,'<br/>');
	str = str.replace(/\[em_([0-9]*)\]/g,'<img src="templates/images/qqface/$1'+'.gif" '+' border="0" />');
	return str;
}

//替换表情，不包括把\n替换成<br/>
function replace_em_nobr(str){
//	str = str.replace(/\</g,'&lt;');
//	str = str.replace(/\>/g,'&gt;');
//	str = str.replace(/\n/g,'<br/>');
	str = str.replace(/\[em_([0-9]*)\]/g,'<img src="templates/images/qqface/$1'+'.gif" '+' border="0" />');
	return str;
}

//主发布框，发布宝贝或者发布格子的“发布”按钮点击事件。
function fabu_baobeigezi(t)
{
	var gntv = $("#geziname_text").val();//宝贝名称
	gntv = $.trim(gntv);
	if(!gntv || gntv==$("#geziname_text").attr('defval'))
	{
		$.jBox.tip('对不起，宝贝名称不能为空', 'warn'); 
		return false;
	}
	
	var jzid = $("#fabugezi_jiazivalue_input").val();//架子id
	if(jzid<=0)
	{
		$.jBox.tip('对不起，请选择架子', 'warn');
		return false;
	}
	
	var stv = $("#saytext").val();//使用心得
	stv = $.trim(stv);
	if(!stv || stv == $("#saytext").attr('defval'))
	{
		$.jBox.tip('对不起，请输入使用心得', 'warn');
		return false;
	}
	//stv = replace_em(stv);//表情字符替换成表情图片路径，这步在发布时不做，存入数据库的就是这种：[em_3]文本。
	//在需要展示表情的页面上，用js来转。
	//展示的时候利用的是客户端的资源来转换，服务端省了力气。
	//这样发布的时候省了转换的时间，数据库里也不用保存长长的图片路径。转换工作交给了客户端js。
	
	var dealprice = $("#dealprice").val();//成交价
	if(!dealprice)
	{
		$.jBox.tip("成交价不能为空", 'warn');
		return false;
	}
	//成交价不为空，0也是不为空
	if(!isNaN(dealprice))
	{
		//是数字（整数、小数或负数）
		if(dealprice<0)
		{
			$.jBox.tip("成交价必需大于等于0", 'warn');
			return false;
		}
	}
	else
	{
		$.jBox.tip("成交价只能是整数或小数", 'warn');
		return false;
	}
	dealprice = parseFloat(dealprice);
	
	var marketprice = $("#marketprice").val();//市场价
	if(!marketprice)
	{
		//价格为空，市场价可以为空
	}
	else //市场价不为空，0也是不为空
	{
		if(!isNaN(marketprice))
		{
			//是数字（整数、小数或负数）
			if(marketprice<0)
			{
				$.jBox.tip("市场价必需大于等于0", 'warn');
				return false;
			}
		}
		else
		{
			$.jBox.tip("市场价只能是整数或小数", 'warn');
			return false;
		}
		
		marketprice = parseFloat(marketprice);
		
		if(marketprice<dealprice)
		{
			$.jBox.tip("市场价不能低于成交价", 'warn');
			return false;
		}
	}
	
	var imageids='';//图片id
	$("#image_upload_ul li div a[dataval]").each(function(){
		var oneimageid = $(this).attr('dataval');
		imageids += oneimageid+',';
		
	});
	if(!imageids)
	{
		$.jBox.tip("亲，要晒单哦！", 'warn');
		return false;
		
	}
	
	var product_url = $("#fabugezi_prourl_input").val();//宝贝原始链接

	if(!product_url) //该分支理论上不会为真
	{
		$.jBox.tip("宝贝链接为空，请重新发布。", 'warn');
		return false;
	}
	
	//下面要用ajax把宝贝信息发送到后台
	var attr = '';
	$.ajax({
		type:"post",
		async : true,//false表示同步，true表示异步
		url:"ajax.php?mod=gezi_publish&code=dofabu_gezi",
		data:{gzname:gntv,jzid:jzid,xinde:stv,dealprice:dealprice,marketprice:marketprice,imageids:imageids,product_url:product_url},
		beforeSend: function(XMLHttpRequest){
			//发布按钮功能禁用
			attr = $(t).attr('onclick');
			$(t).attr('onclick','');
			$.jBox('<div style="margin:auto;margin-top:80px;width: 220px;"><img src="templates/images/jbox-content-loading.gif" /><div id="tip" style="font-size: 14px;color: #666;margin-top: 5px;text-align: center;">正在努力发布中，请稍候...</div></div>',
					{
						id: 'loading_wall',
					    title: "请稍候...",
					    width: 650,
					    height: 390
					});
		},
		success: function(data){//data是json格式
			$.jBox.close('loading_wall');
			//data = JSON.parse(data);
			data = jQuery.parseJSON( data );
			if(data.done)//发布成功
			{
				$.jBox.close('gezi_pro_info');
				$.jBox.tip('恭喜您，格子发布成功啦！', 'success');
				return;
			}
			else//发布失败
			{
				//恢复发布按钮功能
				$(t).attr('onclick',attr);
				
				$.jBox.tip(data.msg, 'error');
				return;
			}
		}
		});
	
	
}

//得到一个maintopic图片的保存路径。
//参数imgid，是一个图片的id。
//成功，返回图片路径；失败，返回空。
function proimg_path(image_id)
{
	if(image_id<=0 || !image_id)
	{
		return '';
	}
	
	path = 'images/product/' + 'product_'+image_id+'/';
	return path;

}

//得到一个topic图片的保存路径。
//参数imgid，是一个图片的id。
//成功，返回图片路径；失败，返回空。
function topicimg_path(image_id)
{
	if(image_id<=0 || !image_id)
	{
		return '';
	}
	
	path = 'images/topic/' + 'topic_'+image_id+'/';
	return path;

}


/////////////小提示弹出框（参见宝贝发面框中的小提示）////////////////
function tishi_show(t)
{
	$(t).find(".hint-highlight").show();

	
}

function tishi_hide(t)
{
	$(t).find(".hint-highlight").hide();
}

//通过ajax获得表情html
//参数qipao_popup_id，代表表情展示在的弹出层id；参数aimtext_id，代表表情点击后，要插入哪个文本框的id。
//成功返回true；失败返回空
function load_biaoqing_byajax(qipao_popup_id, aimtext_id)
{
	var biaoqing_list_ele = $("#"+qipao_popup_id).find("[name='biaoqing_list']");
	if(biaoqing_list_ele.length>0) //说明找到了拥有属性：name=biaoqing_list的元素。也就是说表情列表已经加载完了。
	{
		return '';
	}
	if(!qipao_popup_id || !aimtext_id)
	{
		return '';
	}
	
	$.ajax({
		type:"post",
		async : true,//false表示同步，true表示异步
		url:"ajax.php?mod=gezi_publish&code=load_biaoqing",
		data:{qipao_popup_id:qipao_popup_id,aimtext_id:aimtext_id},
		beforeSend: function(XMLHttpRequest){
			
		},
		success: function(data){//data是html，也就是表情的html
			
			if(data)//加载成功
			{
				$("#"+qipao_popup_id).append(data);
				return true;
			}
			else//发布失败
			{
				return '';
			}
		}
		});
}

/////////////////发布普通贴子（追加心得，网友讨论，买后点评）////////////////
//gzid，格子id，即maintopic的tid；
//topictype是指即将发布的贴子类型：追加心得？网友讨论？买后点评？
//contentid，是文本内容的元素id
//image_upload_id是上传图片展示区的元素id。
//t 是指发布按钮的this元素，这里传入，是因为函数里面要改变它的样式，以及禁用它。
function fabu_topic(gzid, topictype, contentid, image_upload_id , t)
{
	//判断是否登录
	var currentuid = $("#current_uid_page_info").val();
	if(currentuid<=0 || !currentuid)
	{
		$.jBox.tip('对不起，请先登录。', 'warn');
		return;
	}
	//先做提交前的信息格式检查
	//贴子文本内容检查
	var stv = $("#"+contentid).val();//文本内容
	stv = $.trim(stv);
	if(!stv || stv == $("#"+contentid).attr('defval'))
	{
		$.jBox.tip('对不起，请输入内容。', 'warn');
		return;
	}
	//图片id获得
	var imageids='';//图片id
	$("#"+image_upload_id+" li div a[dataval]").each(function(){
		var oneimageid = $(this).attr('dataval');
		imageids += oneimageid+',';
		
	});//imageids可能为空
	var attrval = '';
	//下面要用ajax把贴子信息发送到后台
	$.ajax({
		type:"post",
		async : true,//false表示同步，true表示异步
		url:"ajax.php?mod=topic_publish&code=dofabu_topic",
		data:{gzid:gzid,topictype:topictype,content:stv,imageid:imageids},
		beforeSend: function(XMLHttpRequest){
			//将发布按钮给disable掉。
			$(t).addClass('W_btn_a_disable');
			attrval = $(t).attr("onclick");
			$(t).attr("onclick","");
		},
		success: function(data){//data是json格式
			
			data = jQuery.parseJSON( data );
			if(data.done)//发布成功
			{
				$.jBox.tip('恭喜您，发布成功啦！', 'success');
				
				setTimeout('location.reload();', 1000);//1秒后刷新页面
				return;
			}
			else//发布失败
			{
				//将发布按钮给点亮。
				$(t).removeClass('W_btn_a_disable');
				$(t).attr("onclick",attrval);
				
				$.jBox.tip(data.msg, 'error');
				return;
			}
		}
		});
}


//inputid是回复的内容所在的输入框。
//做一次真正的回复，aimtid是回复的对象，可能回复的对象是其父母（也就是普通贴子），也可能回复的对象是一个回复。
function do_reply_ajax(t, inputid , aimtid)
{
	if(!inputid || !aimtid)
	{
		return;
	}
	//检查输入框的内容格式正确性
	var replytext = $.trim($("#"+inputid).val());
	if(!replytext)
	{
		$.jBox.tip('对不起，请输入回复内容。', 'warn');
		return;
	}

	var attrval = '';
	
	$.ajax({
		type:"post",
		async : true,//false表示同步，true表示异步
		url:"ajax.php?mod=topic_publish&code=dofabu_topic_reply",
		data:{content:replytext, aimtid:aimtid},
		beforeSend: function(XMLHttpRequest){
			//将发布按钮给disable掉。
			$(t).addClass('W_btn_a_disable');
			attrval = $(t).attr("onclick"); 
			$(t).attr("onclick","");
			
		},
		success: function(data){//data是json格式
			
			data = jQuery.parseJSON( data );
			if(data.done)//发布成功
			{
				$.jBox.tip('恭喜您，发布成功啦！', 'success');
				//回复按钮恢复功能
				$(t).attr("onclick",attrval);
				$(t).removeClass('W_btn_a_disable');
				
				//要把刚发布的回复，给展示出来，也就是作为一个li放在comment_lists_ul的最上端
				var retval = data.retval; //retval里存放的是刚发布成功的回复的一些信息，包括发布者头像、发布者昵称、发布的内 容、发布时间、发布回复的id.
				
				var comment_lists_ul_li_ele = '<li class="comment_list_li" operdata="'+retval.fabu_reply_id+'" style="display:none;">';
				   comment_lists_ul_li_ele += '  <div style="width: 30px;padding: 3px 0 0;"><a href="javascript:void(0);"><img src="'+retval.fabuzhe_face_m+'" style="width:30px;height:30px;"></a></div>';
				   comment_lists_ul_li_ele += '  <div class="reply_text_area">';
				   comment_lists_ul_li_ele += '    <div style="width:100%;"><a href="javascript:void(0);">'+retval.fabuzhe_nickname+'</a>：'+'<span name="reply_item_need_replace_em">'+replace_em(retval.fabu_reply_content)+'</span> ('+retval.fabu_time+')</div>';
				   comment_lists_ul_li_ele += '    <div class="info" style="width:100%;text-align:right;">';
				   comment_lists_ul_li_ele += '      <span class="W_linkb" style="line-height: 15px;float:right;">';
				   comment_lists_ul_li_ele += '        <a href="javascript:void(0);" onclick="show_replyinput_of_reply(this,\''+retval.fabu_reply_id+'\', \''+retval.fabuzhe_nickname+'\');">回复</a>';
				   comment_lists_ul_li_ele += '      </span>';
				   comment_lists_ul_li_ele += '      <span class="W_linkb" style="line-height: 15px;float:right;margin-right: 10px;">';
				   comment_lists_ul_li_ele += '        <a href="javascript:void(0);" onclick="delete_reply_alert(\''+retval.fabu_reply_id+'\');">删除</a>';
				   comment_lists_ul_li_ele += '      </span>';
				   comment_lists_ul_li_ele += '    </div>';
				   comment_lists_ul_li_ele += '  </div>';
				   comment_lists_ul_li_ele += '</li>';
	            
				$("#comment_lists_ul_t"+retval.fabu_reply_ptid).prepend(comment_lists_ul_li_ele);
				$("#comment_lists_ul_t"+retval.fabu_reply_ptid).find('li:first').slideDown('slow');
				
				//每个普通topic的回复总数要加一。
				var totalreplynum = $("#total_reply_num_show_area_parent_"+retval.fabu_reply_ptid).html();
				$("#total_reply_num_show_area_parent_"+retval.fabu_reply_ptid).html(parseInt(totalreplynum)+1);
	            
				//如果回复的是一个普通topic，那么需要清空这个普通topic的回复框的内容。
				$("#topicreply_fabu_input_"+retval.fabu_reply_ptid).val('');
				//回复回复的输入框要消除。
				//本函数在回复普通贴子，和回复回复的时候，都会运行。所以，这里只想在回复一个回复时运行。
				if($(t).parents('[name=reply_replyt_inputbox]:first').length > 0)//说明这个元素存在，而这个元素正是回复回复框。回复普通贴子框是没有的。
				{
					$(t).parents('[name=reply_replyt_inputbox]:first').remove();
				}
			}
			else//发布失败
			{
				//回复按钮恢复功能
				$(t).attr("onclick",attrval);
				$(t).removeClass('W_btn_a_disable');
				
				$.jBox.tip(data.msg, 'error');
				return;
			}
		}
		});
}

function show_replyinput_of_reply(t, totid, touser_nickname)
{
	if(totid<=0 || !totid)
	{
		return;
	}
	if($(t).parent().parent('.info').next().attr('name') == 'reply_replyt_inputbox')
	{
		//删除回复发布框。
		$(t).parent().parent('.info').next().remove();
	}
	else
	{
		//增加回复发布框
		var ele = '<div name="reply_replyt_inputbox" id="reply_replyt_inputbox_t_'+totid+'" class="WB_media_expand repeat S_line1 S_bg1" style="">';
		   ele += '  <div class="WB_arrow">';
		   ele += '    <em class="S_line1_c">◆</em><span class=" S_bg1_c">◆</span>';
		   ele += '  </div>';
		   ele += '  <div class="S_line1 input replyreply_input" style="">';
		   ele += '      <textarea id="topicreply_fabu_input_'+totid+'" class="W_input" name="" style="color: rgb(51, 51, 51);margin: 0px 0px 5px; padding: 5px 0px 0px 2px; border-style: solid; border-width: 1px; font-size: 12px; font-family: Tahoma, 宋体; word-wrap: break-word; line-height: 18px; overflow: hidden; outline: none; height: 23px;" onkeyup="changeTip_by_val(\'topicreply_fabu_input_'+totid+'\', \'\',1,240);">回复'+touser_nickname+':</textarea>';
		   ele += '      <div style="float:left;"><!--发布框需要表情时，把整个div内容拷走-->';
		   ele += '          <div style="float:left;position:relative;"  >';
		   ele += '             <div style="float:left;cursor: pointer;" onclick="show_element(\'biaoqing_popup_parent_t'+totid+'\');load_biaoqing_byajax(\'biaoqing_popup_parent_t'+totid+'\',\'topicreply_fabu_input_'+totid+'\');hide_other_biaoqing_replyreplyinputbox(\''+totid+'\');">';
		   ele += '                  <img src="templates/images/biaoqing_sd.jpg" style="width:16px;height:16px;float:left;margin-top: 2px;margin-right: 3px;" />';  
		   ele += '              </div>';
		   ele += '              <div name="biaoqing_popup_box" id="biaoqing_popup_parent_t'+totid+'" style="width: 400px; min-height: 50px; position: absolute; top: 25px; left: -99px; display: none;float:left;z-index: 5;" class="menu_fjb">';
		   ele += '                 <i class="menu_fjb_c1" onclick="hide_element(\'biaoqing_popup_parent_t'+totid+'\'); " style="float:right;cursor:pointer;">';
		   ele += '                 <img src="/templates/images/imgdel.gif" title="关闭">';
		   ele += '                 </i>';
		   ele += '               	<span class="arrow-up" style="left:100px;"></span><span class="arrow-up-in" style="left:100px;"></span>';
		   ele += '               </div>';
		   ele += '           </div>';
		   ele += '      </div>';
		   ele += '      <div class="btn" style="float:right;margin-right: 1px;">';
		   ele += '        <a href="javascript:void(0);" class="W_btn_a" onclick="do_reply_ajax(this, \'topicreply_fabu_input_'+totid+'\',\''+totid+'\');">';
		   ele += '        <span class="btn_30px W_f14"><b class="loading"></b><em style="font-style: normal;">回复</em></span>';
		   ele += '        </a>';
		   ele += '      </div>';
		   ele += '  </div>';
		   ele += '</div>  ';
		$(t).parent().parent('.info').after(ele);
	}
	
}

//关闭其他表情弹出层，删除其他的reply_replyt_inputbox
//因为在IE7下，表情弹出层和reply_replyt_inputbox发布框会有重叠，为了在IE7下避免尴尬，必需让这两个东西不要同时出现。
//当一个表情按钮被点击时，或者一个回复回复的输入框被打开时，会运行本函数。
function hide_other_biaoqing_replyreplyinputbox(totid)
{
	//每个表情弹出层都有属性name=biaoqing_popup_box
	$(document).find('div[name=biaoqing_popup_box]').each(function(){
		if( $(this).attr('id') != 'biaoqing_popup_parent_t'+totid )
		{
			$(this).hide();
		}
	});
	//回复一个回复的输入框都有属性name=reply_replyt_inputbox
	$(document).find('div[name=reply_replyt_inputbox]').each(function(){
		if($(this).attr('id') != 'reply_replyt_inputbox_t_'+totid)
		{
			$(this).remove();
		}
	});

}


//删除一个回复前的提示
function delete_reply_alert(replytopicid)
{
	$.jBox("post:ajax.php?mod=topic&code=del_rep_alert&tid="+replytopicid,{
		id: 'del_rep_'+replytopicid,
		title: '确定删除',
		//bottomText: replytopicid ,
		width:300

	});
}

//确实要删除一个回复
function do_delete_reply(replytopicid)
{
	$.ajax({
		type:"post",
		async : true,//false表示同步，true表示异步
		url:"ajax.php?mod=topic&code=do_del_reply",
		data:{replytopicid:replytopicid},
		beforeSend: function(XMLHttpRequest){
			$.jBox.close('del_rep_'+replytopicid);
		},
		success: function(data){//data是json格式
			
			data = jQuery.parseJSON( data );
			if(data.done)//删除成功
			{
				$.jBox.tip('删除成功', 'success');
				
				//页面的那条回复要消除
				$(".comment_list_li[operdata="+replytopicid+"]").slideUp();
				setTimeout(function () {
					$(".comment_list_li[operdata="+replytopicid+"]").remove();
                }, 500);//毫秒，自己定义延迟时间
				
				//还要将父亲贴子的回复数减1，也是页面效果
				var retval = data.retval;
				var currenthuifu_num = parseInt($('#total_reply_num_show_area_parent_'+retval.ptid).html());
				$('#total_reply_num_show_area_parent_'+retval.ptid).html(currenthuifu_num - 1);
				
				return;
			}
			else//删除失败
			{				
				$.jBox.tip(data.msg, 'error');
				return;
			}
		}
		});
}

//删除一个topic前的提示（topic的类型可能是追加心得、网友讨论或者买后点评）
//参数tid就是要删除的那个topic的id
function delete_topic_alert(tid)
{
	options = {
            'onClickYes':function (){
            	$.ajax({
            		type:"post",
            		async : true,//false表示同步，true表示异步
            		url:"ajax.php?mod=topic&code=do_del_topic",
            		data:{topicid:tid},
            		beforeSend: function(XMLHttpRequest){
            			
            		},
            		success: function(data){//data是json格式
            			
            			data = jQuery.parseJSON( data );
            			if(data.done)//删除成功
            			{
            				$.jBox.tip('删除成功', 'success');
            				
            				//页面的那条topic要消除
            				$("[name=timeline_cell_display][operdata="+tid+"]").slideUp();
            				setTimeout(function () {
            					$("[name=timeline_cell_display][operdata="+tid+"]").remove();
                            }, 500);//毫秒，自己定义延迟时间
            				
            				return;
            			}
            			else//删除失败
            			{				
            				$.jBox.tip(data.msg, 'error');
            				return;
            			}
            		}
            		});
           }
     };

	MessageBox('warn','确定删除？','提示', options);

}

//////////////////////////////////////////格子详细里用到的/////////////////////////////////
function topic_big_pic_wrap_mousemove(t)//鼠标在大图片上移动的事件
{
	var e = window.event;
	//var positionX=e.originalEvent.x-$(this).offset().left; alert(e.originalEvent.x+"....."+$(this).offset().left);
  	//e.originalEvent.x在IE下时，我们预期它的值是鼠标相对浏览器最左侧的横向距离，但是IE下在祖先节点有relative时，其值的横向距离就会是从relative元素开始的距离，而不是浏览器最左侧 。
	var mX = e.x ? e.x : e.pageX;//参考http://www.csharpwin.com/dotnetspace/12010r8019.shtml http://www.doc88.com/p-970313518669.html http://www.jb51.net/article/22506.htm
	var positionX = mX - $(t).offset().left;   
	 	if (positionX < $(t).width()/3){
   	 $(t).removeClass('rightcursor');
   	 $(t).removeClass('smallcursor');
	 $(t).addClass('leftcursor');
    }else if($(t).width()/3 <= positionX && positionX <= $(t).width()/3*2){
   	 $(t).removeClass('rightcursor');
   	 $(t).removeClass('leftcursor');
	 $(t).addClass('smallcursor');
    }else {
   	 $(t).removeClass('smallcursor');
   	 $(t).removeClass('leftcursor');
	 $(t).addClass('rightcursor');
    }
	
}
function topic_big_pic_wrap_click(t)//鼠标在大图片上点击的事件
{
    var pic_num = $(t).find('img').length;//共有多少张图片
    
    var choose_box = $(t).siblings('[name=intopic_small_pic_choose_box_wrap]').find('.intopic_small_pic_choose_box');
    
   	var pos = choose_box.find('li a[name=current]').parent('li').index();//当前图片的index
   	
   	if($(t).hasClass('rightcursor')) //鼠标箭头指向右边，展示下一张图片
   	{
   		pos = pos+1;
   		if(pos >= pic_num)
   		{
   			pos = 0;
   		}
   		choose_box.find('li:eq('+pos+') a').trigger("click");
   		return ;
   	}
   	else if($(t).hasClass('leftcursor')) //鼠标箭头指向左边，展示上一张图片
   	{
   		pos = pos-1;
   		if(pos < 0)
   		{
   			pos = pic_num-1;
   		}
   		choose_box.find('li:eq('+pos+') a').trigger("click");
   		return ;
   	}
   	else if($(t).hasClass('smallcursor')) //鼠标是缩小图标，点击后要隐藏大图幻灯片，而展示小图。
   	{
   		var tid = $(t).attr("operationdata");
   		$("#topic_big_pic_ppt_"+tid).hide();
   		$("#topic_pic_"+tid).show();
   		return ;
   	}
	
}

function topic_img_ppt_change(t, windowid)
{
	//兄弟节点的current去除
	$(t).parent('li').siblings().find('a').removeClass('current');
	$(t).parent('li').siblings().find('a').removeAttr('name');
	//自己的current去除
	$(t).removeClass('current');
	$(t).removeAttr('name');
	//给自己安class=current
	if(!$(t).hasClass('current'))
	{
		$(t).addClass("current");	
	}
	
	if($(t).attr('name') == undefined)
	{
		$(t).attr('name','current');
	}
	
	//大图跟着小图变
	var pos = $(t).parent('li').index();
	$("#"+windowid+" img").hide();
	$("#"+windowid+" img:eq("+pos+")").fadeIn();
	
}

//放大贴子图片
function zoom_big_topic_img(t, smallpicid , pptid)
{
	var pos = $(t).parent().parent('li').index();
	var choose_box = $("#"+pptid).find('[name=intopic_small_pic_choose_box_wrap]');
	
	$("#"+smallpicid).hide();
	$("#"+pptid).show();
	choose_box.find('li:eq('+pos+') a').trigger("click");
}
//缩小贴子图片
function zoom_small_topic_img(smallpicid , pptid)
{
	$("#"+smallpicid).show();
	$("#"+pptid).hide();
}

//得到一个普通贴子的回复，通过ajax
//参数tid就是普通贴子的id。
//每个贴子都有一个回复按钮，huifuid就是它的id。
//topage顾名思意，目标页数。
//clicktype，值为huifu，代表点击的是回复按钮；值为pagebutton，代表点击的是页码按钮。
function pickup_topic_reply(huifuid, tid, topage, clicktype)
{
	if($("#"+huifuid).parent().parent(".topic_operation").next('.topic_reply_list').attr('name') == 'loading_tip') //说明回复正在加载中。
	{
		return ;
	}
	else if($("#"+huifuid).parent().parent(".topic_operation").next('.topic_reply_list').attr('name') == 'topic_reply_list') //说明回复列表已经存在
	{
		if(clicktype=='huifu')
		{
			//将回复列表删除，也就是收起回复。
			$("#"+huifuid).parent().parent(".topic_operation").next('.topic_reply_list').remove();
			return;
		}
	}
	$.ajax({
		type:"post",
		async : true,//false表示同步，true表示异步
		url:"ajax.php?mod=topic&code=gettopicreply",
		data:{tid:tid, topage:topage},
		beforeSend: function(XMLHttpRequest){
			$("#"+huifuid).parent().parent(".topic_operation").next('[name=topic_reply_list]').remove();//删除已经存在的topic_reply_list
			var loading_ele = '<div name="loading_tip" class="topic_reply_list" style="text-align:center;"><span class="W_loading">正在加载，请稍候...</span></div>';
			$("#"+huifuid).parent().parent(".topic_operation").after(loading_ele);//正在加载的提示
		},
		success: function(data){//data是html，也就是回复的html
			
			if(data) //获取贴子回复成功。
			{
				//表情符替换成表情图片。
				var dataele = $(data);
				dataele.find('span[name=reply_item_need_replace_em]').each(function(){
					var cont_with_em = replace_em($(this).html());
					$(this).html(cont_with_em);
					
				});
				
				$("#"+huifuid).parent().parent(".topic_operation").next('[name=loading_tip]').remove();//去除提示“正在加载中”的元素
				$("#"+huifuid).parent().parent(".topic_operation").after(dataele);
				var total_reply_num = $("#"+huifuid).parent().parent(".topic_operation").next('.topic_reply_list').attr('operationdata');
				//修改“回复”两字右边的回复总数
				$("#total_reply_num_show_area_parent_"+tid).html(total_reply_num);
				return;
				
			}
			else //获取贴子回复失败。
			{
				return;
			}
		}
		});
}
//////////////////////////////////////////////////////////////////////////////////////

function isUndefined(variable)
{
	return typeof variable=='undefined'?true:false;
}
/////////////////MessageBox///////////////////////////
/**
 * 消息提示框。有确定、取消按钮的弹出层。
 */
//参数说明：
//type---success、error、warn、password，
//msg----文本信息
//title---对话框最上端的标题
//options----按钮点击事件
var _fun_click_yes;
function MessageBox(type, msg, title, options)
{
	if (isUndefined(options)) {
		options = {};
	}
	if(type!='warn' && type!='success' && type!='error' && type!='password')
	{
		type = 'warn';
	}
	var msgboxid = 'msgbox_'+type;
	$.ajax({
		type:"post",
		async : true,//false表示同步，true表示异步
//		url:"ajax.php?mod=topic&code=messagebox",//这是原来晒格子项目的地址
		url:"ajax.php?mod=common&act=messagebox",
		data:{type:type, msg:msg, msgboxid:msgboxid},
		beforeSend: function(XMLHttpRequest){
			
		},
		success: function(data){//data是html，也就是回复的html、
			if(!data)//说明ajax没有回复任何内容，这次ajax请求失败
			{
				return '';
			}
			else //data就是对话框内的html
			{	
				if (options.onClickYes) {
					_fun_click_yes = options.onClickYes;
				}
				$.jBox(data,{
					id: msgboxid,
					title: title,
					//bottomText: replytopicid ,
					width:300
				});
			}
		}
		});
}
//messagebox点击“确定”按钮触发的函数
function msgbox_click_yes(msgboxid)
{
	if(_fun_click_yes)
	{	
		$("#messagebox_yes_btn").attr('onclick','');//把确定按钮的功能给去掉，以免重复点击。
		_fun_click_yes();
		//执行完以后，将_fun_click_yes清空。
		_fun_click_yes = '';
		$.jBox.close(msgboxid);
		return;
	}
	else
	{
		$.jBox.close(msgboxid);
		return;
	}
}
//messagebox点击“取消”按钮触发的函数
function msgbox_click_no(msgboxid)
{
	$.jBox.close(msgboxid);
}

//登录弹出对话框。
function login_dialog_show()
{
	$.jBox("iframe:index.php?mod=login&code=dialog", {
		id:"",
	    title: "快速登录",
	    width: 350,
	    height: 380
	});

}

//当一个格子cell发生鼠标移上去的事件时，要显示操作者能对该格子进行的操作按钮，比如删除按钮、修改新增便利贴的按钮等等。
function gezi_cell_mouseover(t, gzid)
{
	var gz_del_btn = $(t).find('[name=gezicell_del_button]');
	if(gz_del_btn.length > 0)//说明有删除格子的按钮。
	{
		gz_del_btn.show();
	}
	var gz_add_post = $(t).find('[name=gezicell_add_modify_postnote]');
	if(gz_add_post.length > 0)//说明有添加便利贴的按钮。
	{
		gz_add_post.show();
	}
	
}
function gezi_cell_mouseout(t, gzid)
{
	var gz_del_btn = $(t).find('[name=gezicell_del_button]');
	if(gz_del_btn.length > 0)//说明有删除格子的按钮。
	{
		gz_del_btn.hide();
	}
	var gz_add_post = $(t).find('[name=gezicell_add_modify_postnote]');
	if(gz_add_post.length > 0)//说明有添加便利贴的按钮。
	{
		gz_add_post.hide();
	}
}

//软删除一个格子前的确认提示（软删除就是放入回收站，只有管理员可以硬删除。）
function soft_del_gezi_confirm(gzid)
{
	options = {
            'onClickYes':function (){
            	$.ajax({
            		type:"post",
            		async : true,//false表示同步，true表示异步
            		url:"ajax.php?mod=topic&code=do_soft_del_mt",
            		data:{gzid:gzid},
            		beforeSend: function(XMLHttpRequest){
            			
            		},
            		success: function(data){//data是json格式
            			
            			data = jQuery.parseJSON( data );
            			if(data.done)//删除成功
            			{
            				$.jBox.tip('删除成功', 'success');
            				
            				//页面的那条格子要消除
            				$("[name=gezi_cell_display][operdata="+gzid+"]").fadeOut();
            				setTimeout(function () {
            					$("[name=timeline_cell_display][operdata="+gzid+"]").remove();
                            }, 500);//毫秒，自己定义延迟时间
            				
            				return;
            			}
            			else//删除失败
            			{				
            				$.jBox.tip(data.msg, 'error');
            				return;
            			}
            		}
            		});
           }
     };

	MessageBox('warn','确定要删除这个格子以及格子里的评论吗?删除之后不可恢复哦!','提示', options);

}

//删除一个架子
function del_jiazi_confirm(jid)
{
	options = {
            'onClickYes':function (){
            	var loginpassword = $("#messagebox_password").val();//密码千万不要trim。
            	$.ajax({
            		type:"post",
            		async : true,//false表示同步，true表示异步
            		url:"ajax.php?mod=topic&code=do_del_jiazi",
            		data:{jid:jid, loginpassword:loginpassword},
            		beforeSend: function(XMLHttpRequest){
            			
            		},
            		success: function(data){//data是json格式
            			
            			data = jQuery.parseJSON( data );
            			if(data.done)//删除成功
            			{
            				$.jBox.tip('删除成功', 'success');
            				
            				//页面的那条cell要消除
            				$("[name=jiazi_cell_display][operdata="+jid+"]").fadeOut();
            				setTimeout(function () {
            					$("[name=jiazi_cell_display][operdata="+jid+"]").remove();
                            }, 500);//毫秒，自己定义延迟时间
            				
            				return;
            			}
            			else//删除失败
            			{				
            				//$.jBox.tip(data.msg, 'error');
            				MessageBox('error',data.msg,'提示');
            				return;
            			}
            		}
            		});
           }
     };

	MessageBox('password','确定要删除这个架子吗？删除前，请确保该架子内的格子都已删除。<br />删除后不可恢复哦！','提示', options);
}

//“去购买”按钮点击事件。跳转到分成说明页面。但这里是模拟post来做的跳转。如果用get直接跳转，用户可以看到地址，可能会修改地址参数，那么后续入库的数据就有可能乱套。
//参数gzid是要购买的宝贝id或者说格子id；
//from指明在哪个页面点击了“去购买”，可能是gzview、jzview、list
function pro_gobuy_click(gzid, from)
{
	var inputstr = '<input type="hidden" name="gz" value="'+gzid+'" />';
	inputstr +=    '<input type="hidden" name="from" value="'+from+'" />';
	
	var formstr = '<form style="display:none;" action="index.php?mod=go&code=des_pro" method="post" id="form_buy" target="_blank">'+inputstr+'</form>';

	var formobj = $(formstr);
	
	$('body').append(formobj);
	formobj.submit();
	$("#form_buy").remove();
}

//跳到index.php?mod=go&code=jump
function pro_goto_jump(gzid, jumptype, from, mt_type )
{
	var inputstr = '<input type="hidden" name="gz" value="'+gzid+'" />';
	inputstr +=    '<input type="hidden" name="jumptype" value="'+jumptype+'" />';
	inputstr +=    '<input type="hidden" name="from" value="'+from+'" />';
	inputstr +=    '<input type="hidden" name="mt_type" value="'+mt_type+'" />';

	var formstr = '<form action="index.php?mod=go&code=jump" method="post" target="_self">'+inputstr+'</form>';

	var formobj = $(formstr);
	
	$('body').append(formobj);
	formobj.submit();
}

//参数areaid是一个地区id，它可以是任何级别的地区。
//函数里会以areaid为基础，找到它上面所有的父级地区，以及它下面的一级地区，并放到aimid元素下。
function getAreaList(areaid, aimid)
{
	$.ajax({
		type:"post",
		async : true,//false表示同步，true表示异步
		url:"ajax.php?mod=topic&code=getarealist",
		data:{areaid:areaid},
		beforeSend: function(XMLHttpRequest){
			
		},
		success: function(data){
			
			if(!data)//获取失败
			{
				return;
			}
			else//获取成功，传过来的就是地区下拉列表的html页面。
			{
				$("#"+aimid).append(data);
			}
		}
		});
}

//////////////////////////亲客新加//////////////////////////////////////////




function fanhui_click(cur_dialogbox_id, p_dialogbox_id)
{
	$.jBox.close(cur_dialogbox_id);
	$("#"+p_dialogbox_id).show();

}
function xuanze(vl, saveid, cur_dialogbox_id, p_dialogbox_id)
{
	$("#"+saveid).html(vl);
	$.jBox.close(cur_dialogbox_id);
	$("#"+p_dialogbox_id).show();
}

function close_dialog(diaid)
{
	$.jBox.close(diaid);
}

function loadingbox()
{
	$.jBox('<style type="text/css">#loadingjbox div.jbox-title-panel{display:none;}</style><div style="margin:auto;margin-top:10px;width: 220px;"><img src="templates/admin/images/indicator_medium.gif" style="float: left;margin-left: 30px;"/><div id="tip" style="font-size: 12px;color: #666;margin-top: 5px;text-align: center;float: left;margin-left: 10px;">正在加载...</div></div>',
				{
					id: 'loadingjbox',
					showClose:false,
				    width: 180,
				    height: 110
				});
}

function close_loadingbox()
{
	$.jBox.close('loadingjbox');
}

//用于图片出错时。<img src="http://www.shopnum1.com/images/logo3.png" onerror="javascript:logoError(this);"/>
function logoError(t)
{
	$(t).attr('src','templates/admin/images/nopic.jpg');
	$(t).css('width','200px');
}

