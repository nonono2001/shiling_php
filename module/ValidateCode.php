<?php 
session_start();
$width = 50; // 图像宽度
$height = 20; // 图像高度
$vcodes = createCheckCode();
$_SESSION['VCODE'] = $vcodes;
$img = ImageCreateTrueColor( $width, $height );
$white = ImageColorAllocate($img, 254, 253, 252); //RGB值 
$blue = ImageColorAllocate($img, 0, 0, 255);
$red = ImageColorAllocate($img,255,0,0);
$black = ImageColorAllocate($img,0,0,0);
ImageFill($img, 10, 0, $black);  //往背景图里填充$blue 
for($count=0;$count<10;$count++){
ImageLine($img, 0, 1, getx(), gety(), $red); /*这里出现了2对坐标，“0, 0”是起始点的坐标，而$width, $height是终点的坐标，最后一个参数就是颜色。*/
ImageLine($img, 1, 2, getx(), gety(), $blue);
}
ImageString($img, 20, 5, 3, $vcodes, $white);//图像上书写字符串
header( 'Content-type: image/png' ); //指定图像的MIME类型 
imagepng($img);  //输出图像数据

?>
<?php
function getx(){
	Global $whidth,$height;
	return rand($whidth,$height);
}
function gety(){
	Global $whidth,$height;
	return rand($height,$whidth);
}
function createCheckCode(){
	$char = "abcdefghijkmnpqrstwxyzABCDEFGHIJKLMNPRSTWXYZ";		
	$length = 4;
	$code="";		
	for($index=0;$index<$length;$index++){
		$temp = rand(0,26);			
		if(rand(1,26)%2 == 0){
			$code .= strtolower(substr($char,$temp,1));
		}else if(rand(1,26)%2 == 1){
			$code .= strtoupper(substr($char,$temp,1));
		}
	}
	for($count = strlen($code);$count <= 3;$count++){
		$temp = rand(1,9);
		$code .= $temp;
	}
	return $code;
}
?>