<?php


class PagedataLogic{

	function PagedataLogic(){

	}
	
	//准备页面的数据，尤其是页头和页脚
	//返回 一个数组，格式如下：
	/*$pagedata = array(
	 * 'logo' => '/attachment_frontweb/logo_pic/logo.jpg',
	 * 'board_pic' => array(
	 *       '0' => '/attachment_frontweb/board_pic/0.jpg',
	 *       '1' => '/attachment_frontweb/board_pic/1.jpg',
	 *       '2' => '/attachment_frontweb/board_pic/2.jpg',
	 *     ),
	 * 'nav' => array(
	 *       '15' => array(
	 *           'nav_id' => '15',
	 *           'name' => '产品中心',
	 *           'link_url' => 'index.php?mod=pl&pc=0',
	 *           'open_type' => '_blank',
	 *           'level' => '1',
	 *           'child_nav' => array(
	 *             
	 *             
	 *             
	 *             ),
	 *         ),
	 *     
	 *     ),
	 * 
	 *   )
	 * 
	 */

	function PrepareViewData()
	{	
		//logo数据
		$logo_path = get_logo_img_path();//前台网站logo地址。
		
		//大图轮播图片地址
		$boardpics = get_boardpic_path();//前台网站轮播大图地址。
		
		//页头中的导航栏数据
//		$dbhandle = new SqlClass();
//		$dbhandle->connect();

		$sql = "select * from qkdb_nav order by paixu_num ASC";
		
		$query = GLX()->db->Query($sql);
		
		$navlist = array();
		while($nav_row = GLX()->db->GetRow($query))
		{
			//每个$nav_row就是一个导航项目，一级到四级都有可能。
			if('1' == $nav_row['level'])
			{
				$navlevel1[$nav_row['nav_id']] = $nav_row;
			}
			else if('2' == $nav_row['level'])
			{
				$navlevel2[$nav_row['nav_id']] = $nav_row;
			}
			else if('3' == $nav_row['level'])
			{
				$navlevel3[$nav_row['nav_id']] = $nav_row;
			}
			else if('4' == $nav_row['level'])
			{
				$navlevel4[$nav_row['nav_id']] = $nav_row;
			}
		}
		$navlist['navlevel1'] = $navlevel1;
		$navlist['navlevel2'] = $navlevel2;
		$navlist['navlevel3'] = $navlevel3;
		$navlist['navlevel4'] = $navlevel4;
		
		$pagedata['navlist'] = $navlist;
		
		$pagedata['logo'] = $logo_path;
		
		
		$pagedata['boardpics'] = (array)$boardpics;
		
		ksort($pagedata['boardpics']);
		
		return $pagedata;
	}

	
}



















?>