<?php
//敏感词汇集合
return array(
		
'yemian' => array(  //这里的key：yemian，指明一个一级菜单，不能重复。
        'name' => '页面', //一级导航名称
        'url' => 'admin.php?mod=nav',
        'level2' => array(
                array(
                    'name' => '导航', //二级导航名称
                    'level3' => array(
                        'daohang' => array( //这里的key：daohang，指明一个三级菜单，不能重复。
                                'name' => '导航管理', //三级导航名称
                                'url' => 'admin.php?mod=nav',
                            ),
                    ),
                ),

                array(
                    'name' => '自定义页面',
                    'level3' => array(
                        'zidingyi' => array(
                            'name' => '自定义页管理',
                            'url' => 'admin.php?mod=cpl',
                        ),
                    ),
                ),

                array(
                    'name' => '页面元素',
                    'level3' => array(
                        'element_logo' => array(
                            'name' => 'Logo图片管理',
                            'url' => 'admin.php?mod=element&act=logo',
                        ),
                        'element_boardpic' => array(
                            'name' => '轮播大图管理',
                            'url' => 'admin.php?mod=element&act=boardpic',
                        ),
                    ),
                ),
            ),//二三级导航结束
        ),//一个1级导航结束

'products' => array(
    'name' => '产品',
    'url' => 'admin.php?mod=pl',
    'level2' => array(
        array(
            'name' => '产品管理',
            'level3' => array(
                'productlist' => array(
                    'name' => '产品列表',
                    'url' => 'admin.php?mod=pl',
                ),
                'searchloglist' => array(
                    'name' => '搜索产品关键词列表',
                    'url' => 'admin.php?mod=pl&act=search_log_product',
                ),
            ),
        ),

        array(
            'name' => '产品配置',
            'level3' => array(
                'prodcat' => array(
                    'name' => '产品分类',
                    'url' => 'admin.php?mod=pcat',
                ),
                'comparekey' => array(
                    'name' => '产品对比属性',
                    'url' => 'admin.php?mod=pl&act=comparekey',
                ),
            ),
        ),

    ),

        ),

'wenzhang' => array(
    'name' => '文章',
    'url' => 'admin.php?mod=al',
    'level2' => array(
        array(
            'name' => '文章管理',
            'level3' => array(
                'articlelist' => array(
                    'name' => '文章列表',
                    'url' => 'admin.php?mod=al',
                ),
            ),
        ),

        array(
            'name' => '文章配置',
            'level3' => array(
                'articlecat' => array(
                    'name' => '文章分类',
                    'url' => 'admin.php?mod=acat',
                ),
            ),
        ),

    ),

    ),

'liuyan' => array(
    'name' => '留言',
    'url' => 'admin.php?mod=adl',
    'level2' => array(
        array(
            'name' => '用户留言',
            'level3' => array(
                'adviselist' => array(
                    'name' => '留言列表',
                    'url' => 'admin.php?mod=adl',
                ),
            ),
        ),



    ),

    ),

'youlian' => array(
    'name' => '友链',
    'url' => 'admin.php?mod=fll',
    'level2' => array(
        array(
            'name' => '友情链接',
            'level3' => array(
                'friendlinklist' => array(
                    'name' => '友链列表',
                    'url' => 'admin.php?mod=fll',
                ),
            ),
        ),



    ),

    ),

'xitong' => array(
    'name' => '系统',
    'url' => 'admin.php?mod=system&act=shopname',
    'level2' => array(
        array(
            'name' => '店铺信息',
            'level3' => array(
                'shopname' => array(
                    'name' => '店铺名称',
                    'url' => 'admin.php?mod=system&act=shopname',
                ),
            ),
        ),



    ),

    ),

'xiazai' => array(
    'name' => '下载',
    'url' => 'admin.php?mod=download',
    'level2' => array(
        array(
            'name' => '下载管理',
            'level3' => array(
                'downloadlist' => array( //这里的key：nav_manage，指明一个三级菜单，不能重复。
                    'name' => '下载列表',
                    'url' => 'admin.php?mod=download',
                ),
            ),
        ),
    ),

    ),


'kaquan' => array(
    'name' => '卡券',
    'url' => 'admin.php?mod=thcard',
    'level2' => array(
        array(
            'name' => '卡券管理',
            'level3' => array(
                'tihuoka' => array( //这里的key：nav_manage，指明一个三级菜单，不能重复。
                    'name' => '提货卡',
                    'url' => 'admin.php?mod=thcard',
                ),
            ),
        ),
    ),

),


'member' => array(
    'name' => '会员',
    'url' => 'admin.php?mod=member',
    'level2' => array(
        array(
            'name' => '会员管理',
            'level3' => array(
                'memberlist' => array( //这里的key：nav_manage，指明一个三级菜单，不能重复。
                    'name' => '会员列表',
                    'url' => 'admin.php?mod=member',
                ),

            ),
        ),
    ),

    ),

);

?>