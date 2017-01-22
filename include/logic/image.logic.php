<?php


class ImageLogic{

	function ImageLogic(){

	}

	//剪裁图片，一张上传过来的图片，经本函数，得到whole、big、middle、small四张图。并在数据库中得到一个imageid。
	//whole：等比图。只做等比缩小，不做剪裁。跟原图比例一致，原图宽度如果大于800，则被等比缩放到宽等于800。如果原图宽小于等于800，则宽高不变。但不管有没有等比缩放，容量一定要变小。
	//big：大图。big、middle、small，三个都是剪裁后的正方形图片。大图的长宽为400。
	//middle：中图。中图的长宽120*120。
	//small：小图。长宽为50*50。
	//参数$source_path，是上传到服务器后的图片临时文件夹中的图片。$source_info是临时文件的信息，包括name、tmp_name、size
	//成功返回$image_id,失败返回false。
	function proimg_makethumb( $source_path, $source_info)
	{
		//先申请一个image_id，在数据库中占个位置。只有得到id号，才能进行将处理后的图片放到正确的目录下。
		$sourcedata = getimagesize($source_path);
		$source_width  = $sourcedata[0];
		$source_height = $sourcedata[1];
		
		if($sourcedata[2]!=1 && $sourcedata[2]!=2 && $sourcedata[2]!=3)//1代表gif，2代表jpg，3代表png
		{
			return false;
		}
		
		$insert_data = array(
				'origin_name' => $source_info['name'],
				'origin_filesize' => $source_info['size'],//单位“字节”
				'origin_width' => $source_width,
				'origin_height' => $source_height,
				'description' => '',
				'ftp_url' => '',
				'path' => '',//得到id以后，再修改
				'uid' => '',//uid和tid，需要等到topic真正生成了以后再补上。即上传图片的时候，topic是尚未生成的。
				'tid' => '',
				'dateline' => TIMESTAMP,
		);
		
//		$dbhandle = new SqlClass();
//		$dbhandle->connect();

		$dbhandle = GLX()->db;
		$image_id = $dbhandle->insert('maintopic_image', $insert_data, true);
		if( $image_id <= 0 || !$image_id)
		{
			return false;
		}
		//得到了一个image的id。
		
		$image_path =  proimg_path($image_id);
		
		//////////////////进行上传操作，得到temp图片，temp图片跟源始图片一模一样/////////////////////////////////
		//利用move_uploaded_file
		$image_file_temp = $image_path . $image_id . "_t.jpg";
		move_uploaded_file($source_info["tmp_name"],$image_file_temp);
		
		///////////////////////////////等比缩放开始，最终得到whole//////////////////
		$image_file_whole = $image_path . $image_id . "_w.jpg";
		$this->image_equalratio($image_file_temp, $image_file_whole, 800);
		///////////////////////////////等比缩放结束/////////////////////////////////
		
		///////////////////////////////剪裁开始，最终得到big、middle、small图/////////////////////////////////////
		$image_file_small = $image_path . $image_id . "_s.jpg";//50*50
		$image_file_middle = $image_path . $image_id . "_m.jpg";//120*120
		$image_file_big = $image_path . $image_id . "_b.jpg";//400*400
		
		$target_height = 50;
		$target_width = 50;
		$this->imagecropper($image_file_temp, $image_file_small, $target_width, $target_height);
		
		$target_height = 120;
		$target_width = 120;
		$this->imagecropper($image_file_temp, $image_file_middle, $target_width, $target_height);
		
		$target_height = 400;
		$target_width = 400;
		$this->imagecropper($image_file_temp, $image_file_big, $target_width, $target_height);
		
		//别忘记修改数据库里的path字段
		$update_data = array(
				'path' => $image_path,
		);
		$dbhandle->update('maintopic_image', $update_data, "id='".$image_id."'");
		
		//别忘记删除$image_file_temp文件。
		unlink($image_file_temp);
		
		return $image_id;
	}
	
	//剪裁图片，一张上传过来的图片，经本函数，得到whole、big、middle、small四张图。并在数据库中得到一个imageid。
	//whole：等比图。只做等比缩小，不做剪裁。跟原图比例一致，原图宽度如果大于800，则被等比缩放到宽等于800。如果原图宽小于等于800，则宽高不变。但不管有没有等比缩放，容量一定要变小。
	//big：大图。big、middle、small，三个都是剪裁后的正方形图片。大图的长宽为400。
	//middle：中图。中图的长宽120*120。
	//small：小图。长宽为50*50。
	//参数$source_path，是上传到服务器后的图片临时文件夹中的图片。$source_info是临时文件的信息，包括name、tmp_name、size
	//成功返回$image_id（topic_image表的id）,失败返回false。
	function topicimg_makethumb( $source_path, $source_info)
	{
		//先申请一个image_id，在数据库中占个位置。只有得到id号，才能进行将处理后的图片放到正确的目录下。
		$sourcedata = getimagesize($source_path);
		$source_width  = $sourcedata[0];
		$source_height = $sourcedata[1];
	
		if($sourcedata[2]!=1 && $sourcedata[2]!=2 && $sourcedata[2]!=3)//1代表gif，2代表jpg，3代表png
		{
			return false;
		}
	
		$insert_data = array(
				'origin_name' => $source_info['name'],
				'origin_filesize' => $source_info['size'],//单位“字节”
				'origin_width' => $source_width,
				'origin_height' => $source_height,
				'description' => '',
				'ftp_url' => '',
				'path' => '',//得到id以后，再修改
				'from' => 'local',
				'uid' => '',//uid和tid，需要等到topic真正生成了以后再补上。即上传图片的时候，topic是尚未生成的。
				'tid' => '',
				'dateline' => TIMESTAMP,
				'img_url' => '',
		);
	
//		$dbhandle = new SqlClass();
//		$dbhandle->connect();

		$dbhandle = GLX()->db;
		$image_id = $dbhandle->insert('topic_image', $insert_data, true);
		if( $image_id <= 0 || !$image_id)
		{
			return false;
		}
		//得到了一个image的id。topic_image表。
	
		$image_path =  topicimg_path($image_id);
	
		//////////////////进行上传操作，得到temp图片，temp图片跟源始图片一模一样/////////////////////////////////
		//利用move_uploaded_file
		$image_file_temp = $image_path . $image_id . "_t.jpg";
		move_uploaded_file($source_info["tmp_name"],$image_file_temp);
	
		///////////////////////////////等比缩放开始，最终得到whole//////////////////
		$image_file_whole = $image_path . $image_id . "_w.jpg";
		$this->image_equalratio($image_file_temp, $image_file_whole, 800);
		///////////////////////////////等比缩放结束/////////////////////////////////
	
		///////////////////////////////剪裁开始，最终得到big、middle、small图/////////////////////////////////////
		$image_file_small = $image_path . $image_id . "_s.jpg";//50*50
		$image_file_middle = $image_path . $image_id . "_m.jpg";//120*120
		$image_file_big = $image_path . $image_id . "_b.jpg";//400*400
	
		$target_height = 50;
		$target_width = 50;
		$this->imagecropper($image_file_temp, $image_file_small, $target_width, $target_height);
	
		$target_height = 120;
		$target_width = 120;
		$this->imagecropper($image_file_temp, $image_file_middle, $target_width, $target_height);
	
		$target_height = 400;
		$target_width = 400;
		$this->imagecropper($image_file_temp, $image_file_big, $target_width, $target_height);
	
		//别忘记修改数据库里的path字段
		$update_data = array(
				'path' => $image_path,
		);
		$dbhandle->update('topic_image', $update_data, "id='".$image_id."'");
	
		//别忘记删除$image_file_temp文件。
		unlink($image_file_temp);
	
		return $image_id;
	}
	
	//等比缩放函数
	//如果源图的宽度>$max_width，则进行等比缩小成新的图像，新图像的宽就是$max_width，高就是源图片等比缩小的结果。
	//如果源图的宽度<=$max_width，则不进行放大（因为会有锯齿）。
	//参数dogif，为false时，表示不对gif文件进行缩放，为保持gif图是动态的；为true时，表示对gif进行缩放，结果图片是不能动的。
	//成功返回新图片路径，失败返回空。
	//对于gif比较特殊，它不能进行缩放，一旦缩放gif就不能动了。所以不管gif的宽高是多少，容量有多大，均不作任何处理。
	function image_equalratio($source_path, $target_path, $max_width, $dogif=false)
	{
		if (!is_file($source_path))
		{
			return '';
		}
		
		$source_info   = getimagesize($source_path);
		if(!$source_info)
		{
			return '';
		}
		
		$source_width  = $source_info[0];
		
		$source_height = $source_info[1];
		
		if($source_info[2] == 1)//gif
		{
			if($dogif == false)//不对gif做缩放，保持gif的动态性
			{
				$source_image = imagecreatefromgif($source_path);
				//对于gif文件，比较特殊，不能经过imagecopyresampled，因为一旦经过它，gif就会不动了。
				//只能做一个原封不动的拷贝。
				if(!copy($source_path, $target_path))
				{
					return '';
				}
				else
				{
					return $target_path;
				}
			}
			else //对gif做缩放。
			{
				$source_image = imagecreatefromgif($source_path);
			}
		}
		elseif($source_info[2] == 2)//jpg
		{
			$source_image = imagecreatefromjpeg($source_path);
				
		}
		elseif($source_info[2] == 3)//png
		{
			$source_image = imagecreatefrompng($source_path);
				
		}
		else
		{
			return '';
		}
		
		if($source_width > $max_width)
		{
			$target_width = $max_width;
			
			$target_height = round( $source_height / $source_width * $target_width );
		}
		else
		{
			$target_width = $source_width;
			
			$target_height = $source_height;
		}
		
		$target_image = imagecreatetruecolor($target_width, $target_height);
		//让png的透明处变白，参考http://www.myexception.cn/php/1276371.html
		$white = imagecolorallocate($target_image,255,255,255);
		imagefilledrectangle($target_image,0,0,$target_width,$target_height,$white);
		imagecolortransparent($target_image,$white);
		
		imagecopyresampled($target_image, $source_image, 0, 0, 0, 0, $target_width, $target_height, $source_width, $source_height);
		
		imagejpeg($target_image, $target_path);
		
		imagedestroy($source_image);
		imagedestroy($target_image);
		
		if(!is_file($target_path))
		{
			return '';
		}
		else
		{
			return $target_path;
		}
	}
	
	//参考http://www.oschina.net/code/snippet_1043199_25783
	//将图片按新的高和宽，剪裁成的缩略图
	//失败返回空，成功返回生成的缩略图路径
	function imagecropper($source_path, $thum_path, $thumbwidth, $thumbheight )
	{
		if (!is_file($source_path))
		{
			return '';
		}
		
		$target_width = (int) $thumbwidth;
		$target_height = (int) $thumbheight;
		
		$source_info   = getimagesize($source_path);
		if(!$source_info)
		{
			return '';
		}
		
		$source_width  = $source_info[0];
		
		$source_height = $source_info[1];
		
		$source_mime   = $source_info['mime'];
		
		$source_ratio  = $source_height / $source_width;
		
		$target_ratio  = $target_height / $target_width;
		
		// 源图过高
		if ($source_ratio > $target_ratio)
		{
			$cropped_width  = $source_width;
			$cropped_height = $source_width * $target_ratio;
			$source_x = 0;
			$source_y = ($source_height - $cropped_height) / 2;
		}
		// 源图过宽
		elseif ($source_ratio < $target_ratio)
		{
			$cropped_width  = $source_height / $target_ratio;
			$cropped_height = $source_height;
			$source_x = ($source_width - $cropped_width) / 2;
			$source_y = 0;
		}
		// 源图适中
		else
		{
			$cropped_width  = $source_width;
			$cropped_height = $source_height;
			$source_x = 0;
			$source_y = 0;
		}
		
		//$source_info[2]就是文件类型，代表了它是jpg还是gif还是png等。
		//很多例子用的是$source_info['mime']或者$_FILES["file"]["type"]来判断文件类型。它的值有：'image/gif'、'image/jpeg'、'image/png'等。
		//但比如一个png文件，当后缀名被改为jpg后，那用mime就会当成'image/jpeg'，从而识别错误。
		//而用$source_info[2]就不会出错。
		
		if($source_info[2] == 1)//gif
		{
			$source_image = imagecreatefromgif($source_path);
			
		}
		elseif($source_info[2] == 2)//jpg
		{
			$source_image = imagecreatefromjpeg($source_path);
			
		}
		elseif($source_info[2] == 3)//png
		{
			$source_image = imagecreatefrompng($source_path);
			
		}
		else 
		{
			return '';
		}
		
		// 裁剪
		$cropped_image = imagecreatetruecolor($cropped_width, $cropped_height);
		//让png的透明处变白，参考http://www.myexception.cn/php/1276371.html
		$white = imagecolorallocate($cropped_image,255,255,255);
		imagefilledrectangle($cropped_image,0,0,$cropped_width,$cropped_height,$white);
		imagecolortransparent($cropped_image,$white);
		
		imagecopy($cropped_image, $source_image, 0, 0, $source_x, $source_y, $cropped_width, $cropped_height);
		//现在$cropped_image里就得到了剪裁后的图像。
		
		
		// 缩放
		//我们这里只缩小，不放大。因为放大会产生锯齿。
		//判断$cropped_image的宽高是否小于目标宽高，若小于，则目标宽高调节为$cropped_image的宽高；
		//若$cropped_image的宽高大于等于目标宽高，则进行正常缩小。
		if($cropped_width < $target_width)
		{
			$target_width = $cropped_width;
			$target_height = $cropped_height;
		}
		$target_image  = imagecreatetruecolor($target_width, $target_height);
		imagecopyresampled($target_image, $cropped_image, 0, 0, 0, 0, $target_width, $target_height, $cropped_width, $cropped_height);
		
		imagejpeg($target_image, $thum_path);
		
		imagedestroy($source_image);
		imagedestroy($target_image);
		imagedestroy($cropped_image);
		
		if(!is_file($thum_path))
		{
			return '';
		}
		else
		{
			return $thum_path;
		}
		
	}
	
	//划框剪裁。即jcrop这种在图片上划框的剪裁。用于头像上传。
	//头像剪裁后，得到2个图：b（180*180），m（50*50）。
	//成功返回true，失败返回false。
	function jcropcut_face($source_path, $x, $y, $w, $h)
	{
		//宽和高不能为0
		if($w<=0 || $h<=0)
		{
			return false;
		}
		
		if (!is_file($source_path))
		{
			return false;
		}
		
		$x = (int)$x;
		$y = (int)$y;
		$cropped_width = (int)$w;
		$cropped_height = (int)$h;
		
		$source_info   = getimagesize($source_path);
		if(!$source_info)
		{
			return false;
		}
		
		if($source_info[2] == 1)//gif
		{
			$source_image = imagecreatefromgif($source_path);
				
		}
		elseif($source_info[2] == 2)//jpg
		{
			$source_image = imagecreatefromjpeg($source_path);
				
		}
		elseif($source_info[2] == 3)//png
		{
			$source_image = imagecreatefrompng($source_path);
				
		}
		else
		{
			return false;
		}
		
		// 裁剪
		$cropped_image = imagecreatetruecolor($cropped_width, $cropped_height);
		//让png的透明处变白，参考http://www.myexception.cn/php/1276371.html
		$white = imagecolorallocate($cropped_image,255,255,255);
		imagefilledrectangle($cropped_image,0,0,$cropped_width,$cropped_height,$white);
		imagecolortransparent($cropped_image,$white);
		
		imagecopy($cropped_image, $source_image, 0, 0, $x, $y, $cropped_width, $cropped_height);
		//现在$cropped_image里就得到了剪裁后的图像。
		
		// 缩放
		//我们这里只缩小，不放大。因为放大会产生锯齿。
		//判断$cropped_image的宽高是否小于目标宽高，若小于，则目标宽高调节为$cropped_image的宽高；
		//若$cropped_image的宽高大于等于目标宽高，则进行正常缩小。
		
		//生成180*180的b图
		$target_width = 180;
		$target_height = 180;
		if($cropped_width < $target_width)
		{
			$target_width = $cropped_width;
			$target_height = $cropped_height;
		}
		$target_image  = imagecreatetruecolor($target_width, $target_height);
		imagecopyresampled($target_image, $cropped_image, 0, 0, 0, 0, $target_width, $target_height, $cropped_width, $cropped_height);
		
		$big_thum_path = face_path(MEMBER_NUMURL) . "face_". MEMBER_NUMURL . "_b.jpg";
		imagejpeg($target_image, $big_thum_path);//得到头像的b图。
		imagedestroy($target_image);
		
		//生成50*50的m图
		$target_width = 50;
		$target_height = 50;
		if($cropped_width < $target_width)
		{
			$target_width = $cropped_width;
			$target_height = $cropped_height;
		}
		$target_image  = imagecreatetruecolor($target_width, $target_height);
		imagecopyresampled($target_image, $cropped_image, 0, 0, 0, 0, $target_width, $target_height, $cropped_width, $cropped_height);
		
		$mid_thum_path = face_path(MEMBER_NUMURL) . "face_". MEMBER_NUMURL . "_m.jpg";
		imagejpeg($target_image, $mid_thum_path);//得到头像的m图。
		imagedestroy($target_image);
		
		imagedestroy($source_image);
		
		imagedestroy($cropped_image);
		
		if(!is_file($big_thum_path))
		{
			return false;
		}
		
		if(!is_file($mid_thum_path))
		{
			return false;
		}
			
		return true;
		
	}
	
	
	//划框剪裁。即jcrop这种在图片上划框的剪裁。用于海报上传。
	//海报剪裁后，得到2个图：b（尺寸就是剪裁下来的尺寸），s（360*110）。
	//成功返回true，失败返回false。
	function jcropcut_poster($source_path, $x, $y, $w, $h)
	{
		//宽和高不能为0
		if($w<=0 || $h<=0)
		{
			return false;
		}
		
		if (!is_file($source_path))
		{
			return false;
		}
		
		$x = (int)$x;
		$y = (int)$y;
		$cropped_width = (int)$w;
		$cropped_height = (int)$h;
		
		$source_info   = getimagesize($source_path);
		if(!$source_info)
		{
			return false;
		}
		
		if($source_info[2] == 1)//gif
		{
			$source_image = imagecreatefromgif($source_path);
		
		}
		elseif($source_info[2] == 2)//jpg
		{
			$source_image = imagecreatefromjpeg($source_path);
		
		}
		elseif($source_info[2] == 3)//png
		{
			$source_image = imagecreatefrompng($source_path);
		
		}
		else
		{
			return false;
		}
		
		// 裁剪
		$cropped_image = imagecreatetruecolor($cropped_width, $cropped_height);
		//让png的透明处变白，参考http://www.myexception.cn/php/1276371.html
		$white = imagecolorallocate($cropped_image,255,255,255);
		imagefilledrectangle($cropped_image,0,0,$cropped_width,$cropped_height,$white);
		imagecolortransparent($cropped_image,$white);
		
		imagecopy($cropped_image, $source_image, 0, 0, $x, $y, $cropped_width, $cropped_height);
		//现在$cropped_image里就得到了剪裁后的图像。
		
		$big_thum_path = poster_path_b(MEMBER_NUMURL);
		imagejpeg($cropped_image, $big_thum_path);//得到海报的b图。
		
		// 缩放
		//我们这里只缩小，不放大。因为放大会产生锯齿。
		//判断$cropped_image的宽高是否小于目标宽高，若小于，则目标宽高调节为$cropped_image的宽高；
		//若$cropped_image的宽高大于等于目标宽高，则进行正常缩小。
		
		//生成360*110的b图
		$target_width = 360;
		$target_height = 110;
		if($cropped_width < $target_width)
		{
			$target_width = $cropped_width;
			$target_height = $cropped_height;
		}
		$target_image  = imagecreatetruecolor($target_width, $target_height);
		imagecopyresampled($target_image, $cropped_image, 0, 0, 0, 0, $target_width, $target_height, $cropped_width, $cropped_height);
		
		$small_thum_path = poster_path_s(MEMBER_NUMURL);
		imagejpeg($target_image, $small_thum_path);//得到海报的s图。
		imagedestroy($target_image);
		
		
		imagedestroy($cropped_image);
		
		imagedestroy($source_image);
		
		if(!is_file($big_thum_path))
		{
			return false;
		}
		if(!is_file($small_thum_path))
		{
			return false;
		}
		
		return true;
		
	}
	
	

}


?>