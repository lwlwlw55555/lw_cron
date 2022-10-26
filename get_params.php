<?php
require("includes/init.php");
// require(ROOT_PATH . "includes/erp_report/erp_report_function.php");

// require("Services/LeqeeDbService.php");
// use Services\LeqeeDbService;

// http=>//121.40.113.153/get_params.php?is_search=true
// echo json_encode($_REQUEST);

// $ss = ['TOPCHITU','TOPCHITU','SYCM','SYCM','SYCM','SYCM','BRANDING','BRANDING','BRANDING','BRANDING','BRANDING','BRANDING','BRANDING','BRANDING','BRANDING','BRANDING','BRANDING','BRANDING','BRANDING','BRANDING','BRANDING','BRANDING','BRANDING','BRANDING','BRANDING','BRANDING','BRANDING','BRANDING','BRANDING','BRANDING','BRANDING','BRANDING','BRANDING','BRANDING','BRANDING','BRANDING','BRANDING','BRANDING','BRANDING','BRANDING','BRANDING','BRANDING','BRANDING','BRANDING','BRANDING','BRANDING','BRANDING','BRANDING','BRANDING','BRANDING','BRANDING','BRANDING','BRANDING','BRANDING','BRANDING','SUPER-RECOMMEND','SUPER-RECOMMEND','SUPER-RECOMMEND','SUPER-RECOMMEND','SUPER-RECOMMEND','SUPER-RECOMMEND','SUBWAY','SUBWAY','SUBWAY','SUBWAY','SUBWAY','SUBWAY','TOPCHITU','TMALL-BACK','TMALL-BACK','TMALL-BACK','SYCM','SYCM','SYCM','SYCM','SYCM','SYCM','SYCM','SYCM','SYCM','SYCM','SYCM','SYCM','SYCM','SYCM','SYCM','SYCM','SYCM','SYCM','SYCM','SYCM','SYCM','SYCM','SYCM','SYCM','SYCM','SYCM','SYCM','SYCM','SYCM','SYCM','SYCM','SYCM','SYCM','SYCM','SYCM','SYCM','SYCM','TBK-ALI-MOM','SYCM','SYCM'];

// $ss = array_unique($ss);
// echo json_encode(array_values($ss));


/***
先用idea生成json产出json参数！
**/
$ss = ["salonia旗舰店" =>1000001602,
"BOTANIST海外旗舰店" =>1000001608,
"MLB Beauty旗舰店"=>1000001626,
"贝亲官方旗舰店" =>9,
"雀巢官方旗舰店" =>18,
"亨氏官方旗舰店" =>79,
"花王家清旗舰店" =>182,
"乐而雅官方旗舰店" =>183,
"Nestle雀巢官方海外旗舰店" =>245,
"花王官方海外旗舰店" =>253,
"卡夫亨氏食品旗舰店" =>283,
"pigeon官方海外旗舰店" =>284,
"雀巢母婴官方旗舰店" =>290,
"Kanebo官方海外旗舰店" =>302,
"GardenofLife海外旗舰店" =>373,
"伯特小蜜蜂海外旗舰店" =>378,
"Dolce Gusto官方旗舰店" =>381,
"gerber嘉宝旗舰店" =>496,
"尼康官方旗舰店" =>561,
"宫中秘策旗舰店" =>656,
"欧缇丽官方旗舰店" =>665,
"百事集团官方旗舰店" =>722,
"星巴克家享咖啡旗舰店" =>723,
"徐福记官方旗舰店" =>726,
"艾纯诗母婴旗舰店" =>743,
"EST海外旗舰店" =>753,
"filorga菲洛嘉官方旗舰店" =>774,
"vidivici海外旗舰店" =>794,
"vidivici旗舰店" =>799,
"花王个护海外旗舰店" =>1000000007,
"Augustinus Bader官方旗舰店" =>1000000046,
"芭妮兰官方旗舰店" =>1000000096,
"utena佑天兰旗舰店" =>1000000175,
"utena佑天兰海外旗舰店" =>1000000176,
"mido美度表官方旗舰店" =>1000001202,
"和路雪旗舰店" =>1000001211,
"dodie旗舰店" =>1000001216,
"合生元官方旗舰店" =>1000001219,
"BIOSTIME官方海外旗舰店" =>1000001220,
"swatch斯沃琪官方旗舰店" =>1000001236,
"flikflak飞菲旗舰店" =>1000001237,
"玛氏宠物食品官方旗舰店" =>1000001256,
"Serge Lutens海外旗舰店" =>1000001381,
"ascensia旗舰店" =>1000001405,
"wlab海外旗舰店" =>1000001418,
"wlab旗舰店" =>1000001419,
"FAB官方旗舰店" =>1000001423,
"FREE官方旗舰店" =>1000001448,
"Sarahchapman美妆海外旗舰店" =>1000001482,
"Elis爱璐茜旗舰店" =>1000001488,
"光明奶粉天猫旗舰店" =>1000001502,
"雀巢能恩海外旗舰店" =>1000001513,
"惠氏母婴海外旗舰店" =>1000001514,
"启赋海外旗舰店" =>1000001516,
"正官庄旗舰店" =>1000001519,
"Augustinus Bader海外旗舰店" =>1000001521,
"雀巢超级加旗舰店" =>1000001525,
"养生堂化妆品旗舰店" =>1000001546,
"梵诗柯香旗舰店" =>1000001547,
"百利官方旗舰店" =>1000001549,
"帝亚吉欧洋酒官方旗舰店" =>1000001550,
"翰格蓝爵官方旗舰店" =>1000001551,
"尊尼获加官方旗舰店" =>1000001552,
"帝亚吉欧臻藏威士忌旗舰店" =>1000001553,
"蓓甜诗旗舰店" =>1000001606,
"宝洁海外旗舰店" =>1000001648,
"swissperfection瑞珀斐官方旗舰店" =>1000001662,
"ACQUA DI PARMA帕尔玛之水官方旗舰店" =>1000001537];


$i =[];

foreach ($ss as $key => $value) {
	$i[] = ['shopName'=>$key,'shopId'=>$value];
}
// echo json_encode($ss,JSON_UNESCAPED_UNICODE);

echo json_encode($i,JSON_UNESCAPED_UNICODE);

// echo count($ss);
