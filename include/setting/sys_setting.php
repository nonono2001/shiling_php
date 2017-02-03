<?php
 
				
return array (
 
// 	'site_name' => '我爱鲜生',
 		
//	'db_host' => '54.251.82.112',
	'db_host' => '47.91.179.68',//阿里云食令小程序的服务器
 	'db_user' => 'shiling',
//  	'db_pass' =>  'sgezi50602', //阿里云DB的密码
//     'db_pass' =>  'mysqlweiyang123',//亚马逊EC2的DB密码
 	'db_pass' =>  'Shiling123!',  //本机三星DB密码
	'db_name' => 'tt_shiling',//测试用db
// 	'db_name' => 'qinke_site',//测试用db
//     'db_name' => 'qinke_offical_web',//亲客官网三星机测试对应的db
//  	'db_name' => 'offical_qinke_web',//亲客官网在亚马逊上的db名
//		'db_name' => 'qinke_site',   //亚马逊上聚众力中文版（建站之星蓝色主题，结诚不用）的db名
//		'db_name' => 'qinke_site_jp',//亚马逊上聚众力日文版（建站之星蓝色主题，结诚不用）的db名
//		'db_name' => 'qinke_site_en',//亚马逊上聚众力英文版（建站之星蓝色主题，结诚不用）的db名
//		'db_name' => 'qinke_site_jzlit_cn',//亚马逊上聚众力中文版（怡如老的蓝色主题）的db名
// 		'db_name' => 'qinke_site_jzlit_jp',//亚马逊上聚众力日文版（怡如老的蓝色主题）的db名
// 		'db_name' => 'qinke_site_jzlit_en',//亚马逊上聚众力英文版（怡如老的蓝色主题）的db名
 		
//		'db_name' => 'qinke_site_jiecheng_cn',//亚马逊上结诚中文版（建站之星绿色主题，结诚不用）的db名
//		'db_name' => 'qinke_site_jiecheng_jp',//亚马逊上结诚日文版（建站之星绿色主题，结诚不用）的db名
//		'db_name' => 'qinke_site_yuusei_cn',  //亚马逊上结诚中文版（怡如老的蓝色主题）的db名
//		'db_name' => 'qinke_site_yuusei_jp',  //亚马逊上结诚日文版（怡如老的蓝色主题）的db名

// 		'db_name' => 'qinke_site_jiecheng_cn',//结诚电子对应的数据库。
// 		'db_name' => 'qinke_site_jiecheng_jp',//结诚电子对应的数据库，日文版。

//	'db_host' => '192.168.0.114',
// 	'db_user' => 'root',
// 	'db_pass' => '',
//	'db_name' => 'tuiarv4',
	
//	'db_port' => '',
//	'db_type' => 'mysql',
	

 	//'site_url' => 'http://www.qinketest.com',
//  	'product_url' => 'http://www.qinke.com:8012',
 		
//  	'cookie_domain' => '.qinke.com',
//  	'cookie_expire' => '2592000',//自动登录时间，30天，对应秒数2592000
//  	'cookie_path' => '/',
 		
//  	'ta' =>	array('U' => 'TA',
//  				  'M' => '他',
//  				  'F' => '她',			
//  	),

	'appid'=>'wxbf4eea839f0b7aba',
	'appsecret'=>'a4c3e102e98eefac4ef2fe0af7ba9da9',
 	'token_key'=>'fsdffjklj234kfoo5',

 	'admin_uid' => array('15',
 				  
 	),//网站管理员的uid，可以有多个。这是一种简易的权限管理，在这里可以设置超级admin。
    //目前项目中，把员工只分为两种权限，要么是ADMIN，要么不是。
    //主要原因是原先把员工表和会员表，用一个qkdb_user表存放。而表中并没有区别是否是员工的字段，
    //所以在这里加的admin_uid，用于区别员工和会员。
    //同样可以再搞几个比如member_staff_uid，用于专门管理会员的员工角色；
    //financial_staff_uid用于管理财务的员工角色。在代码中就可以轻易获取一个staff的权限角色，并用于权限控制。


);
?>
