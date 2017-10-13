$(function(){
//	邀请码
	$(".inv_code_pro").height($(".inv_code_pro").width());
	$(".inv_code_pro").css("line-height",$(".inv_code_pro").width()+"px")
//	领取二维码	
	$(".lqewm").height($(window).height())
//	意见反馈
	$(".feedback textarea").focus(function(){
		$(".feedback_place").hide()
	})
	$(".feedback textarea").blur(function(){
		if($(this).val()==""){
			$(".feedback_place").show()
		}	
	})	
//	等级详情
	$(".level_jdli span").height($(".level_jdli span").width());
	$(".level_jdli span").css("line-height",$(".level_jdli span").width()+"px")
	var jd="48%";
	$(".level_xian span").css("left",jd)
	$(".level_xian em").css("width",jd)
	$(".level_xian span").height($(".level_xian span").width());
	$(".level_xian span").css("margin-top",-($(".level_xian span").width()/2+3)+"px")
//	组团详情
	$(".cluster_li .img").height($(".cluster_li .img").width())
//	取货点输入地点后
	$(".qhd_item").click(function(){
		$(this).find(".qhd_ico").addClass("on").parent(".qhd_item").siblings(".qhd_item").find(".qhd_ico").removeClass("on")
	})
})
