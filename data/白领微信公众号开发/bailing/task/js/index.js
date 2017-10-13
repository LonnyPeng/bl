$(function(){
	var windowH=$(window).height();
	$(".task_item").height(windowH/2);
	
//	任务中心
	$(".read_btn").click(function(){
		$(this).addClass("on").html("已参加")
	})
//	阅读详情
	$(".answer_xz p span").click(function(){
		$(this).addClass("on").siblings().removeClass("on")
	})
	var dltop=$(".dialog").height()/2
	$(".dialog").css("margin-top",-dltop)
	$(".answer_btn").click(function(){
		$(".dialog").fadeIn()
		$(".mask").fadeIn()
		$("body").css("overflow-y","hidden")
	})
	$(".cancel").click(function(){
		$(".dialog").fadeOut()
		$(".mask").fadeOut()
		$("body").css("overflow-y","auto")
	})
//	答题详情
	$(".dial").height(windowH);
	var ww=$(".ljcj").width()-13
	var hh=$(".ljcj").height()-5
	$(".ljcj").css("margin-left",-ww/2)
	$(".ljcj").css("margin-top",-hh/2)
	$(".jfdhcj").click(function(){
		$(".dialog_jf").show().siblings().hide()
		$(".dialog").fadeIn()
		$(".mask").fadeIn()
	})
	$(".qdjfdh").click(function(){
		
		var jfnum=1;
		if(jfnum==1){
			$(".dialog_ok").show().siblings().hide()
			
		}else{
			$(".dialog_no").show().siblings().hide()
		}
		 $(".dialog").fadeIn()
		$(".mask").fadeIn()
	})
	$(".dial_top").height($(".dial_top").height())
//	转盘
})

