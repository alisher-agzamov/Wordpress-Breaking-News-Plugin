<?php
if( ! defined('BREAKING_NEWS_PLUGIN_BASENAME')) {
    exit;
}

/**
 * Show the current breaking news active post
 */
function get_the_breaking_news_active_post() {

    $breaking_news_data = get_option( Breaking_News_Post_Editor::$options_content_data_key );

    // if breaking news post is not set
    if( empty( $breaking_news_data['post_id'] )
        || ! ($breaking_news_post = get_post( $breaking_news_data['post_id'] ) ) ) {
        return;
    }

    // if set expiration date
    if( isset($breaking_news_data['expiration_date'])
        && $breaking_news_data['expiration_date'] > 0
        && current_time('timestamp') > $breaking_news_data['expiration_date'] ) {
        return;
    }

    // display post title if custom title is not set
    $title = empty($breaking_news_data['custom_post_title']) ? get_the_title($breaking_news_post) : $breaking_news_data['custom_post_title'];

    //block area settings (e.g. text and background colors)
    $breaking_news_options = get_option(Breaking_News_Options_Page::$options_settings_key);
    ?>
    <div class="breaking_news" style="background-color: <?php echo $breaking_news_options['background_color'];?>; text-align: center; width: 100%;">
        <h2 class="entry-title" style="color: <?php echo $breaking_news_options['text_color'];?>;">
            <?php echo $breaking_news_options['title'];?>:
            <a href="<?php echo get_the_permalink($breaking_news_post)?>" style="color: <?php echo $breaking_news_options['text_color'];?>;"><?php echo $title; ?></a>
        </h2>
    </div>
    <?php
}

add_shortcode('breaking_news', 'get_the_breaking_news_active_post');