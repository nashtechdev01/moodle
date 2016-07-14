define(['jquery'], function($) {

    function equalHeight(group) {
        var tallest = 0;
        group.height('auto');
        group.each(function() {
            var thisHeight = $(this).height();
            if(thisHeight > tallest) {
                tallest = thisHeight;
            }
        });
        group.height(tallest);
    }

    return {
        init: function() {
            var $dt = $('.path-mod-workshop .userplan dt');
            var $dd = $('.path-mod-workshop .userplan dd');
            equalHeight($dt);
            equalHeight($dd);
            $(window).on("resize", function() {
                equalHeight($dt);
                equalHeight($dd);
            });
        }
    };
});