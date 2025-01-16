(function ($) {
    $(function () {
        $(document).ready(function () {
            $("#wp-prompt-new").dialog({
                 modal: true
            });

            $(".ui-dialog").css('z-index', '999999');

            $("#set-post-thumbnail").on("click", function () {
                $("#wp-prompt-img").dialog();

                $(".ui-dialog").css('z-index', '999999');
            });

            $("#publish").one("click",  function (e) {
            
                    e.preventDefault();

                    $("#wp-prompt-publish").dialog({
                        modal: true
                    });

                $(".ui-dialog").css('z-index', '999999');
                
            });
            
        });

    });

})(jQuery)