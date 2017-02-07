function doFade(){
	setTimeout(function(){
		$('.pop-error').fadeOut();

	},2000)
}
function checkMobile (mobile){
	var mobileReg = /^1[34578]\d{9}$/;

	return mobileReg.test(mobile);
}
