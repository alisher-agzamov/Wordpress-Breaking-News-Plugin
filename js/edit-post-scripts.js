jQuery(document).ready(function() {

    set_breaking_news_visibility_blocks();

    jQuery('#breaking_news_make').change(function(){
        set_breaking_news_visibility_blocks();
    });

    jQuery('#breaking_news_use_date').change(function(){
        set_breaking_news_visibility_blocks();
    });

    jQuery('#breaking_news_date').datetimepicker({
        format: breaking_news_datetime_format
    });

});

function set_breaking_news_visibility_blocks() {
    if (jQuery('#breaking_news_make').is(':checked')) {
        jQuery('.breaking_news_option').show();
    } else {
        jQuery('.breaking_news_option').hide();
    }

    if (jQuery('#breaking_news_use_date').is(':checked')) {
        jQuery('#breaking_news_date').parent().show();
    } else {
        jQuery('#breaking_news_date').parent().hide();
    }
}