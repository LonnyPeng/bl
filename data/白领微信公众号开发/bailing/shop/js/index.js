$(function(){
	var windowH=$(window).height();
	$(".slogin").height(windowH);
	$(".login_btn").click(function(){
		if(!$(".pass_inp").val()){
			$.alert({
				  title: '提示',
				  text: '请输入密码',
				  onOK: function () {
				    //点击确认
				    $(".pass_inp").focus()
				  }
				});
		}
	})
})

