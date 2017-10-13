$(function(){
	var color=["#50cab7","#977cdc","#ff8007","#5cbeec","#42bebc","#e54837"];
	$(".hy_ul .weui-cell").each(function(index){
		console.log(index)
		$(this).find(".iconfont").css("color",color[index])
	})

})

