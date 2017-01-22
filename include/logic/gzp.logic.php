<?php


class GzpLogic{

	function GzpLogic(){

	}

	//要使用该函数，fields字段要么为*，要么是字段名。参数uids必需是数组。
	function GetUserbyIds($uids, $fields="*")
	{
		
		if(!$uids){
			return '';
		}
		
		if(!is_array($uids))
		{
			return '';
		}
		$uidstr = jimplode($uids);
		
		$sql = "select ".$fields.",uid, num_url from user where uid in (".$uidstr.") order by field(uid,".$uidstr.")";
//		$dbhandle = new SqlClass();
//		$dbhandle->connect();

		$dbhandle = GLX()->db;
		$query = $dbhandle->Query($sql);
		
		$list = array();
		while($userrow = $dbhandle->GetRow($query))
		{
			//一个$userrow是一个用户信息
			//将该用户的头像封装进去（两个头像b和m的都封装）。
			$fb = face_path_b($userrow['num_url']);
			if(!is_file($fb))
			{
				$fb = 'templates/images/noavatar.gif';
			}
			$userrow['face_b'] = $fb;
			$fm = face_path_m($userrow['num_url']);
			if(!is_file($fm))
			{
				$fm = 'templates/images/noavatar.gif';
			}
			$userrow['face_m'] = $fm;
			
			//将该用户的海报封装进去（两个海报b和s，都封装）。
			$pb = poster_path_b($userrow['num_url']);
			if(!is_file($pb))
			{
				$pb = 'templates/images/noposter.jpg';
			}
			$userrow['poster_b'] = $pb;
			$ps = poster_path_s($userrow['num_url']);
			if(!is_file($ps))
			{
				$ps = 'templates/images/noposter.jpg';
			}
			$userrow['poster_s'] = $ps;
			
			//将该用户的一些字符串信息给进行htmlspecialchars转换，因为有可能用于展示在页面上。万一这些信息中包含标签，有可能会影响页面结构的。
			$userrow['cellphone_nospecial'] = htmlspecialchars($userrow['cellphone']);
			$userrow['school_nospecial'] = htmlspecialchars($userrow['school']);
			$userrow['major_nospecial'] = htmlspecialchars($userrow['major']);
			$userrow['company_nospecial'] = htmlspecialchars($userrow['company']);
			$userrow['duty_nospecial'] = htmlspecialchars($userrow['duty']);
			$userrow['QQ_nospecial'] = htmlspecialchars($userrow['QQ']);
			$userrow['personal_msg_nospecial'] = htmlspecialchars($userrow['personal_msg']);
			
			//将地区全称中的>，换成空格
			if($userrow['area_full_name'])
			{
				$userrow['area_full_name'] = str_replace('>',' ', $userrow['area_full_name']);
				$userrow['area_full_name'] = htmlspecialchars($userrow['area_full_name']);
			}
			
			
			$list[$userrow['uid']] = $userrow;
		}
		//并不是所有uids里的用户都存在，因为可能有些用户注销了。
		$result = array();
		foreach ($uids as $id){
			if($list[$id]){
				$result[$id] = $list[$id];
			}
		}
		
		//$dbhandle->close();不能close，否则mod里的db连接都会被close掉。
		
		return $result;
	}
	
	//要使用该函数，fields字段要么为*，要么是字段名。参数jids必需是数组。
	function GetJiazibyIds($jids, $fields="*")
	{
		if(!$jids){
			return '';
		}
		
		if(!is_array($jids))
		{
			return '';
		}
		$jidstr = jimplode($jids);
		
		$sql = "select ".$fields.",id,uid from jiazi where id in (".$jidstr.") order by field(id,".$jidstr.")";
//		$dbhandle = new SqlClass();
//		$dbhandle->connect();

		$dbhandle = GLX()->db;
		$query = $dbhandle->Query($sql);
		
		$list = array();
		
		$uids = array();
		while($jzrow = $dbhandle->GetRow($query))
		{
			//架子封面图的id，转换成图片路径，该架子中最新的6个格子的商品图
			$imgidstr = $jzrow['fengmian_imgids'];
			if($imgidstr)
			{
				$imgidarr = explode(',', $imgidstr);
				$imgidarr = array_filter($imgidarr);
				foreach((array)$imgidarr as $oneimgid)
				{
					$jzrow['fm_img'][] = proimg_path_b($oneimgid);
				}
			}
			
			$jzrow['create_time'] = date("Y-m-d H:i",$jzrow['create_dateline']);//架子创建时间
			$jzrow['fresh_time'] = date("Y-m-d H:i",$jzrow['lastfresh_dateline']);//架子最后一次更新时间
			
			$jzrow['name_nospecial'] = htmlspecialchars($jzrow['name']);
			$jzrow['jiazi_intro_nospecial'] = htmlspecialchars($jzrow['jiazi_intro']);
			
			$list[$jzrow['id']] = $jzrow;
				
			$uids[$jzrow['id']] = $jzrow['uid'];
		}
		
		$uidstr = jimplode($uids);
		$sql = "select nickname, sex, num_url, puid, uid from user where uid in (".$uidstr.")";
		$query = $dbhandle->Query($sql);
		$userinfo = array();
		while($userrow = $dbhandle->GetRow($query))
		{
			$userinfo[$userrow['uid']] = $userrow;
		}
		$userresult = array();
		foreach($uids as $ukey => $uval)
		{
			//每个$ukey是一个架子id，每一个$uval是一个用户id
			if($userinfo[$uval]){
				$userresult[$ukey] = $userinfo[$uval];
			}
			
		}
		
		//并不是所有jids里的架子都存在，因为可能有些用户注销了。
		$result = array();
		foreach ($jids as $id){
			if($list[$id]){
				$result[$id] = $list[$id];
				$result[$id]['userinfo'] = $userresult[$id];
			}
		}
		
		//$dbhandle->close();不能close，否则mod里的db连接都会被close掉。
		return $result;
	}
	
	//要使用该函数，fields字段要么为*，要么是字段名。参数gzids必需是数组。
	//参数$gzids必需是数组，而且它的key无所谓，它的value必需是格子id。
	function GetGezibyIds($gzids, $fields="*")
	{
		if(!$gzids){
			return '';
		}
		
		if(!is_array($gzids))
		{
			return '';
		}
		$gzidstr = jimplode($gzids);
		
		$sql = "select ".$fields." , tid, jid, puid, uid from maintopic where tid in (".$gzidstr.") order by field(tid,".$gzidstr.")";
//		$dbhandle = new SqlClass();
//		$dbhandle->connect();

		$dbhandle = GLX()->db;
		$query = $dbhandle->Query($sql);
		
		$list = array();
		while($gzrow = $dbhandle->GetRow($query))
		{
			//一个$gzrow是一个格子信息
			
			//封装宝贝的图片
			$imgidstr = $gzrow['imageid'];//用逗号隔开的字符串。
			$imgidarr = '';
			if($imgidstr)
			{
				$imgidarr = explode(',', $imgidstr);
				$imgidarr = array_filter($imgidarr);
			}
			//$imgidarr可能为空，如果为空foreach会报警告，所以加了强制类型转换(array)，参考http://zhidao.baidu.com/link?url=O6d_S7oDj3yOmCTOQjuFsxtpyeCBh5a4i2KaRHrgd61GUyAupGfu85jyVqC2_CBskpufGM2EJ0fk-zgw1cSNaK
			if($imgidarr)
			{
				foreach((array)$imgidarr as $oneid)
				{
					//每个oneid都是一个图片id，每个宝贝图片都有w、b、m、s四张不同尺寸的图。
					$gzrow['proimg'][$oneid]['w'] = proimg_path_w($oneid);
					$gzrow['proimg'][$oneid]['b'] = proimg_path_b($oneid);
					$gzrow['proimg'][$oneid]['m'] = proimg_path_m($oneid);
					$gzrow['proimg'][$oneid]['s'] = proimg_path_s($oneid);
					//还要得到w图的宽度，以便展示时判断要不要给img赋于width属性
					$w_size = getimagesize($gzrow['proimg'][$oneid]['w']);
					$gzrow['proimg'][$oneid]['w_width'] = $w_size[0];
				}
			}
			$gzrow['create_time'] = date("Y-m-d H:i",$gzrow['create_dateline']);//格子创建时间
			$gzrow['fresh_time'] = date("Y-m-d H:i",$gzrow['lastfresh_dateline']);//格子最后一次更新时间
			
			$gzrow['title_nospecial'] = htmlspecialchars($gzrow['title']);
			$gzrow['content_nospecial'] = htmlspecialchars($gzrow['content']);
			
			$list[$gzrow['tid']] = $gzrow;
			
		}
		//并不是所有gzids里的用格子存在，因为可能有些用户注销了。
		$result = array();
		foreach ($gzids as $id){
			if($list[$id]){
				$result[$id] = $list[$id];
			}
		}
		
		//$dbhandle->close();不能close，否则mod里的db连接都会被close掉。
		return $result;
	}
	
	//要使用该函数，fields字段要么为*，要么是字段名。参数gzids必需是数组。
	//参数$gzids必需是数组，而且它的key无所谓，它的value必需是格子id。
	//得到回收站中格子的信息
	function GetGeziRecycbyIds($gzids, $fields="*")
	{
		if(!$gzids){
			return '';
		}
	
		if(!is_array($gzids))
		{
			return '';
		}
		$gzidstr = jimplode($gzids);
	
		$sql = "select ".$fields." , tid, jid, puid, uid from maintopic_recycler where tid in (".$gzidstr.") order by field(tid,".$gzidstr.")";
//		$dbhandle = new SqlClass();
//		$dbhandle->connect();

		$dbhandle = GLX()->db;
		$query = $dbhandle->Query($sql);
	
		$list = array();
		while($gzrow = $dbhandle->GetRow($query))
		{
			//一个$gzrow是一个格子信息
				
			//封装宝贝的图片
			$imgidstr = $gzrow['imageid'];//用逗号隔开的字符串。
			$imgidarr = '';
			if($imgidstr)
			{
				$imgidarr = explode(',', $imgidstr);
				$imgidarr = array_filter($imgidarr);
			}
			//$imgidarr可能为空，如果为空foreach会报警告，所以加了强制类型转换(array)，参考http://zhidao.baidu.com/link?url=O6d_S7oDj3yOmCTOQjuFsxtpyeCBh5a4i2KaRHrgd61GUyAupGfu85jyVqC2_CBskpufGM2EJ0fk-zgw1cSNaK
			if($imgidarr)
			{
				foreach((array)$imgidarr as $oneid)
				{
					//每个oneid都是一个图片id，每个宝贝图片都有w、b、m、s四张不同尺寸的图。
					$gzrow['proimg'][$oneid]['w'] = proimg_path_w($oneid);
					$gzrow['proimg'][$oneid]['b'] = proimg_path_b($oneid);
					$gzrow['proimg'][$oneid]['m'] = proimg_path_m($oneid);
					$gzrow['proimg'][$oneid]['s'] = proimg_path_s($oneid);
					//还要得到w图的宽度，以便展示时判断要不要给img赋于width属性
					$w_size = getimagesize($gzrow['proimg'][$oneid]['w']);
					$gzrow['proimg'][$oneid]['w_width'] = $w_size[0];
				}
			}
			$gzrow['create_time'] = date("Y-m-d H:i",$gzrow['create_dateline']);//格子创建时间
			$gzrow['fresh_time'] = date("Y-m-d H:i",$gzrow['lastfresh_dateline']);//格子最后一次更新时间
			$gzrow['softdel_time'] = date("Y-m-d H:i",$gzrow['softdel_dateline']);//格子被软删除的时间
			
			$gzrow['title_nospecial'] = htmlspecialchars($gzrow['title']);
			$gzrow['content_nospecial'] = htmlspecialchars($gzrow['content']);
			$gzrow['origin_url_nospecial'] = htmlspecialchars($gzrow['origin_url']);
				
			$list[$gzrow['tid']] = $gzrow;
				
		}
		//并不是所有gzids里的用格子存在，因为可能有些用户注销了。
		$result = array();
		foreach ($gzids as $id){
			if($list[$id]){
				$result[$id] = $list[$id];
			}
		}
	
		//$dbhandle->close();不能close，否则mod里的db连接都会被close掉。
		return $result;
	}
	
	//要使用该函数，fields字段要么为*，要么是字段名。参数puids必需是数组。
	//参数$puids必需是数组，而且它的key无所谓，它的value必需是铺的id。
	function GetPubyIds($puids, $fields="*")
	{
		if(!$puids){
			return '';
		}
		
		if(!is_array($puids))
		{
			return '';
		}
		$puidstr = jimplode($puids);
		
		$sql = "select ".$fields." , id, uid from gezipu where id in (".$puidstr.") order by field(id,".$puidstr.")";
//		$dbhandle = new SqlClass();
//		$dbhandle->connect();

		$dbhandle = GLX()->db;
		$query = $dbhandle->Query($sql);
		
		$list = array();
		while($purow = $dbhandle->GetRow($query))
		{
			//一个$purow是一个铺的信息
			if($purow['open_dateline'])
			{
				$purow['open_time'] = date("Y-m-d H:i",$purow['open_dateline']);//铺的创建时间
			}
			if($purow['lastfresh_dateline'])
			{
				$purow['fresh_time'] = date("Y-m-d H:i",$purow['lastfresh_dateline']);//铺最后一次更新时间
			}
			
			$list[$purow['id']] = $purow;
				
		}
		//并不是所有puids里的用铺都存在，因为可能有些用户注销了。
		$result = array();
		foreach ($puids as $id){
			if($list[$id]){
				$result[$id] = $list[$id];
			}
		}
		
		//$dbhandle->close();不能close，否则mod里的db连接都会被close掉。
		return $result;
	}
	
	//得到一个普通topic的所有w型图片，传入的只有一个tid
	//成功，返回一个数组；失败返回空
	function GetTopicWImgbytid($tid)
	{
		if(!$tid || $tid <= 0)
		{
			return '';
		}
		//找topic表中tid相应的记录。
		$sql = "select imageid from topic where tid = '".$tid."'";
//		$dbhandle = new SqlClass();
//		$dbhandle->connect();

		$dbhandle = GLX()->db;
		$query = $dbhandle->Query($sql);
		$topicrow = $dbhandle->GetRow($query);
		
		if(!$topicrow)
		{
			return '';
		}
		$imgidstr = $topicrow['imageid'];
		$imgidarr = '';
		if($imgidstr)
		{
			$imgidarr = explode(',', $imgidstr);
			$imgidarr = array_filter($imgidarr);
		}
		
		//$imgidarr可能为空
		if($imgidarr)
		{
			foreach((array)$imgidarr as $oneid)
			{
				//每个oneid都是一个图片id，每个topic图片都有w、b、m、s四张不同尺寸的图。
				$topicrow['topicimg'][$oneid]['w'] = topicimg_path_w($oneid);
			}
		}
		if($topicrow['topicimg'])
		{
			return $topicrow['topicimg'];
		}
		else {
			return '';
		}
		
	}
	
	
}



















?>