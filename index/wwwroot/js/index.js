$(function(){
	// var swiper = new Swiper('.m_nav', {
 //        pagination: '.swiper-pagination',
 //        slidesPerView: 'auto',
 //        // paginationClickable: true,
 //        spaceBetween: 15,
 //        initialSlide :0, //这里要程序处理成当前页面的id。
 //    });
$(".m_nav").find('.swiper-slide').on('tap', function() {
	// event.preventDefault();
	$(this).siblings().removeClass('on');
	$(this).addClass('on');
});
    $(".m_banner").swiper({
        loop: true,
        autoplay: 3000
    });
    // 所在城市无商品居中
    var wh=$(window).height();
    var ww=$(window).width();
    $(".noproduct").height(wh-50);
    // 分类列表
    $(".prod_list").last().css("margin-bottom",0);
    // 点击收藏
    $(".prod_list").each(function() {
    	$(this).find('.fl').on('click', function(event) {
    	var num=$(this).find('span').html();
    		if($(this).hasClass('on')){
    			$(this).removeClass('on');
    			num--;
    			$(this).find('span').html(num);

    		}else{
    			$(this).addClass('on')
    			num++;
    			$(this).find('span').html(num)
    		}
   	 	});
    });
    // 立即领取详情页点赞
    $(".eval_box .jubao").each(function() {
        $(this).find('.fr').on('click', function(event) {
        var num=$(this).find('span').html();
            if($(this).hasClass('on')){
                $(this).removeClass('on');
                num--;
                $(this).find('span').html(num);

            }else{
                $(this).addClass('on')
                num++;
                $(this).find('span').html(num)
            }
        });
    });
})