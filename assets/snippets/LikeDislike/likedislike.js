(function($){
    $(document).ready(function(){
        $('.likedislike').on('click','a',function(e){
            e.preventDefault();
            var _this = $(this);
            var outer = _this.parent();
            var rid = outer.data('id');
            var action = _this.hasClass('like') ? 'like' : 'dislike';
            $.post('/assets/snippets/LikeDislike/ajax.php',{
                action:action,
                rid:rid
            },function(response){
                $('.like > span',outer).text(response.like);
                $('.dislike > span',outer).text(response.dislike);
                $('.like,.dislike',outer).replaceWith(function() {
                    var cls = $(this).attr('class');
                    return $('<span/>').addClass(cls).append($(this).html());
                });
                $.jGrowl("Спасибо за вашу оценку!",{theme:"likedislike-success"});
            },'json').fail(function(){
                $.jGrowl("Произошла ошибка.",{theme:"likedislike-error"});
            });
        });
    });
})(jQuery);
