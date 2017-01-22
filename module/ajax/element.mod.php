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
		//必需要已登录，并且是管理员身份
		if(!MEMBER_ID || MEMBER_ID <= 0)
		{
			exit('请先登录。');
		}
		if(!IS_MEMBER_ADMIN || IS_MEMBER_ADMIN <= 0)//虽然登录了，但不是管理员
		{
			exit('只有管理员才可以操作。');
		}
		
		switch($this->Act)
		{
			case 'do_save_logo': //保存一个新logo
				$this->Do_save_logo();
				break;
				
			case 'do_save_boardpic':
				$this->Do_save_boardpic();
				break;
				
			case 'delete_one_boardpic':
				$this->Delete_one_boardpic();
				break;
				
			default:
				$this->Common();
				break;
		}

	}
	
	function Common()
	{
		echo '';
		exit();	
	}
	
	function Do_save_logo()
	{
		/*
		 * $_FILES: array (
  'logo_pic' => 
  array (
    'name' => 'T1uJ2IXdFg_!!61700707-0-tstar.jpg',
    'type' => 'image/jpeg',
    'tmp_name' => 'C:\\Windows\\Temp\\php9FFA.tmp',
    'error' => 0,
    'size' => 61845,
  ),
)
		 */
		
		//检查传过来的图片信息是否正确
		$inputname = 'logo_pic';
		$files_error_code = $_FILES[$inputname]['error'];
		//error code等于4，代表没有选择文件。
		if($files_error_code != '4')//代表肯定是上传图片了
		{
			if($files_error_code>0)
			{
				echo '<script type="text/javascript">';
				echo 'window.parent.afterupload("err","logo图片上传失败！请重新上传！");  ';
				echo '</script>';
				exit();
			}
			else //error code为0 ，代表上传成功。
			{
				//检查上传图片的大小，不能超出1M。
				$maxfilesize = 1*1024*1024;//1M换成字节。
				if($_FILES[$inputname]["size"] > $maxfilesize)
				{
					echo '<script type="text/javascript">';
					echo 'window.parent.afterupload("err","上传的logo图片大小不能超过1M，请重新上传。"); ';
					echo '</script>';
					exit();
				}
					
				//检查上传图片的格式，jpg、png、gif。
				$source_info = getimagesize($_FILES[$inputname]["tmp_name"]);
				if($source_info[2] != 1 && $source_info[2] != 2 && $source_info[2] != 3)//1代表gif，2代表jpg，3代表png
				{
					echo '<script type="text/javascript">';
					echo 'window.parent.afterupload("err","图片格式出错，请重新选择图片。"); ';
					echo '</script>';
					exit();
				}
					
				//能走到这里，说明通过了各项检查。
				//把上传的logo图片移到相应文件夹中。
// 				$pimage_path = 'attachment_frontweb/logo_pic/';

				
				$templatename = getConfKV('template_name');//得到当前模板的名称。
				$pimage_path = 'templates/'.$templatename.'/images/attachment/logo_pic/';
				
				if(!is_dir($pimage_path))
				{
					mkdir($pimage_path,0777,true);
				}
				if(!is_dir($pimage_path))
				{
					echo '<script type="text/javascript">';
					echo 'window.parent.afterupload("err","logo图片目录创建失败，请重试！"); ';
					echo '</script>';
					exit();
				}
				//$ok_upload_img里保存的是检查合格的主图编号:0~7。也可能为空
				
// 				//获得文件扩展名
// 				$temp_arr = explode(".", $_FILES[$inputname]["name"]);
// 				$file_ext = array_pop($temp_arr);
// 				$file_ext = trim($file_ext);
// 				$file_ext = strtolower($file_ext);
				
// 				$image_full_name = $pimage_path . 'logo' . '.' . $file_ext;
				$image_full_name = $pimage_path . 'logo' . '.' . 'jpg';//不管上传的是gif、png还是jpg，统统用jpg为后缀。只有这样才不会有1.jpg、1.png、1.gif同时存在于文件夹中。
				
				move_uploaded_file($_FILES[$inputname]["tmp_name"],$image_full_name);
				
				echo '<script type="text/javascript">';
				echo 'window.parent.afterupload("succ",""); ';
				echo '</script>';
				exit();
				
			}
		}
		else 
		{
			//$files_error_code == '4'代表没有上传图片
			echo '<script type="text/javascript">';
			echo 'window.parent.afterupload("err","请选择图片！"); ';
			echo '</script>';
			exit();
		}
	}
	
	function Do_save_boardpic()
	{
		/*
		 * $_FILES: array (
  'board_pic' => 
  array (
    'name' => 
    array (
      0 => '',
      1 => '0b7b02087bf40ad1e06752e3552c11dfa9ecce2b.jpg',
      2 => '0d338744ebf81a4c54c59cc8d52a6059252da63f.jpg',
      3 => '',
      4 => '',
      5 => '0b7b02087bf40ad18094b2e2552c11dfa9eccebb.jpg',
      6 => '',
      7 => '',
    ),
    'type' => 
    array (
      0 => '',
      1 => 'image/jpeg',
      2 => 'image/jpeg',
      3 => '',
      4 => '',
      5 => 'image/jpeg',
      6 => '',
      7 => '',
    ),
    'tmp_name' => 
    array (
      0 => '',
      1 => 'C:\\Windows\\Temp\\phpE29F.tmp',
      2 => 'C:\\Windows\\Temp\\phpE2B0.tmp',
      3 => '',
      4 => '',
      5 => 'C:\\Windows\\Temp\\phpE2D2.tmp',
      6 => '',
      7 => '',
    ),
    'error' => 
    array (
      0 => 4,
      1 => 0,
      2 => 0,
      3 => 4,
      4 => 4,
      5 => 0,
      6 => 4,
      7 => 4,
    ),
    'size' => 
    array (
      0 => 0,
      1 => 415285,
      2 => 48011,
      3 => 0,
      4 => 0,
      5 => 109323,
      6 => 0,
      7 => 0,
    ),
  ),
)
		 * 
		 */
		
		//检查上传的产品主图，大小、格式，是否正确。
		$inputname = 'board_pic';
		$ok_upload_img = array();//用于保存检查过关的上传图片序号。
		for($i=0;$i<8;$i++)
		{
			$files_error_code = $_FILES[$inputname]['error'][$i];
			//error code等于4，代表没有选择文件。
			if($files_error_code != '4')//代表肯定是上传图片了
			{
				if($files_error_code>0)
				{
					echo '<script type="text/javascript">';
					echo 'window.parent.afterupload("err","轮播大图'.($i+1).'上传失败！");  ';
					echo '</script>';
					exit();
				}
				else //error code为0 ，代表上传成功。
				{
					//检查上传图片的大小，不能超出1M。
					$maxfilesize = 1*1024*1024;//1M换成字节。
					if($_FILES[$inputname]["size"][$i] > $maxfilesize)
					{
						echo '<script type="text/javascript">';
						echo 'window.parent.afterupload("err","图片'.($i+1).'大小不能超过1M，请重新上传。"); ';
						echo '</script>';
						exit();
					}
															
					//检查上传图片的格式，jpg、png、gif。
					$source_info = getimagesize($_FILES[$inputname]["tmp_name"][$i]);
					if($source_info[2] != 1 && $source_info[2] != 2 && $source_info[2] != 3)//1代表gif，2代表jpg，3代表png
					{
						echo '<script type="text/javascript">';
						echo 'window.parent.afterupload("err","图片格式出错，请重新选择图片。"); ';
						echo '</script>';
						exit();
					}
								
					//能走到这里，说明通过了各项检查。
					$ok_upload_img[] = $i;
				}
			}
		}
		
		//把轮播图片移到attachment文件夹中。
// 		$pimage_path = 'attachment_frontweb/board_pic/';

		$templatename = getConfKV('template_name');//得到当前模板的名称。
		$pimage_path = 'templates/'.$templatename.'/images/attachment/board_pic/';
		
		if(!is_dir($pimage_path))
		{
			mkdir($pimage_path,0777,true);
		}
		if(!is_dir($pimage_path))
		{
			echo '<script type="text/javascript">';
			echo 'window.parent.afterupload("err","轮播图片目录创建失败，请重试！"); ';
			echo '</script>';
			exit();
		}
		//$ok_upload_img里保存的是检查合格的主图编号:0~7。也可能为空
		foreach($ok_upload_img as $oneimgbn)
		{
// 			//获得文件扩展名
// 			$temp_arr = explode(".", $_FILES[$inputname]["name"][$oneimgbn]);
// 			$file_ext = array_pop($temp_arr);
// 			$file_ext = trim($file_ext);
// 			$file_ext = strtolower($file_ext);
				
// 			$image_full_name = $pimage_path . $oneimgbn . '.' . $file_ext;
			$image_full_name = $pimage_path . $oneimgbn . '.' . 'jpg';//不管上传的是gif、png还是jpg，统统用jpg为后缀。只有这样才不会有1.jpg、1.png、1.gif同时存在于文件夹中。
				
			move_uploaded_file($_FILES[$inputname]["tmp_name"][$oneimgbn],$image_full_name);
		}
		
		echo '<script type="text/javascript">';
		echo 'window.parent.afterupload("succ","");  ';
		echo '</script>';
		exit();
		
	}
	
	
	//删除一个轮播大图。
	function Delete_one_boardpic()
	{
		$picindex = getPG('picindex');
		
		$boardpic_arr = get_boardpic_path();
		
		unlink($boardpic_arr[$picindex]);
		
		json_result();
		
	}
	
}
?>