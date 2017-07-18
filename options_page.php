<?php
if( ! defined('BREAKING_NEWS_PLUGIN_BASENAME')) {
    exit;
}

/**
 * The Breaking_News_Options_Page Class.
 */
class Breaking_News_Options_Page
{
    /**
     * Options settings key
     */
    public static $options_settings_key = 'breaking_news_options';

    /**
     * Plugin options page name
     */
    public static $options_page_name = 'breaking_news_options';

    /**
     * Current option values
     */
    private $_options;

    /**
     * Start up
     */
    public function __construct()
    {
        $this->_options = get_option( self::$options_settings_key );

        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'page_init' ) );
        add_filter( 'plugin_action_links_' . BREAKING_NEWS_PLUGIN_BASENAME, array( $this, 'add_settings_link' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'color_picker_scripts' ) );
    }

    /**
     * Default option values
     */
    public static function default_option_values()
    {
        return array(
            'title'             => __( 'Breaking News', 'breaking_news' ),
            'background_color'  => '#ffffff',
            'text_color'        => '#000000'
        );
    }

    /**
     * Add options page
     */
    public function add_plugin_page()
    {
        $breaking_news_page = add_options_page(
            __( 'Breaking News Options', 'breaking_news' ),
            __( 'Breaking News', 'breaking_news' ),
            'manage_options',
            self::$options_page_name,
            array( $this, 'create_options_page' )
        );

        // Adds options_page_help_tab when breaking news options page loads
        add_action('load-' . $breaking_news_page, array( $this, 'options_page_help_tab' ));
    }

    /**
     * Options page callback
     */
    public function create_options_page()
    {
        ?>
        <div class="wrap">
            <h1><?php _e( 'Breaking News Options', 'breaking_news' ) ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields( 'breaking_news_block_section' );
                do_settings_sections( self::$options_settings_key );
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Register and add settings
     */
    public function page_init()
    {
        register_setting(
            'breaking_news_block_section',
            self::$options_settings_key,
            array( $this, 'sanitize' )
        );

        add_settings_section(
            'breaking_news_block_section',
            __( 'Block Area Settings', 'breaking_news' ),
            array( $this, 'print_block_section_info' ),
            self::$options_settings_key
        );

        add_settings_field(
            'title',
            __( 'Title', 'breaking_news' ),
            array( $this, 'title_callback' ),
            self::$options_settings_key,
            'breaking_news_block_section'
        );

        add_settings_field(
            'background_color',
            __( 'Background Color', 'breaking_news' ),
            array( $this, 'background_color_callback' ),
            self::$options_settings_key,
            'breaking_news_block_section'
        );

        add_settings_field(
            'text_color',
            __( 'Text Color', 'breaking_news' ),
            array( $this, 'text_color_callback' ),
            self::$options_settings_key,
            'breaking_news_block_section'
        );

        add_settings_section(
            'breaking_news_content_section',
            __( 'Content Settings', 'breaking_news' ),
            array( $this, 'print_content_section_info' ),
            self::$options_settings_key
        );

        add_settings_field(
            'post_id',
            __( 'Active Post', 'breaking_news' ),
            array( $this, 'active_post_callback' ),
            self::$options_settings_key,
            'breaking_news_content_section'
        );
    }

    /**
     * Add settings link into plugins page
     * @param $links
     * @return array
     */
    public function add_settings_link($links)
    {
        $settings_link = array(
            '<a href="' . admin_url( 'options-general.php?page=' . self::$options_page_name ) . '">' . __( 'Settings', 'breaking_news' ) . '</a>',
        );

        return array_merge( $links, $settings_link );
    }

    /**
     * Sanitize each setting field as needed
     * @param array $input Contains all settings fields as array keys
     * @return array
     */
    public function sanitize( $input )
    {
        $new_input = array();

        if( !empty( $input['title'] ) ) {
            $new_input['title'] = sanitize_text_field($input['title']);
        } else {
            // Set the error message
            add_settings_error( 'breaking_news_title', 'breaking_news_error', __( 'The Title field is required.', 'breaking_news' ), 'error' );
            $new_input['title'] = $this->_options['title'];
        }

        if( isset( $input['background_color'] ) ) {
            // Check if is a valid hex color
            if( $this->_check_color( $input['background_color'] ) ) {
                $new_input['background_color'] = $input['background_color'];
            } else {
                // Set the error message
                add_settings_error( 'breaking_news_bg_color', 'breaking_news_error', __( 'Insert a valid color for Background', 'breaking_news' ), 'error' );
                $new_input['background_color'] = $this->_options['background_color'];
            }
        }

        if( isset( $input['text_color'] ) ) {
            // Check if is a valid hex color
            if( $this->_check_color( $input['text_color'] ) ) {
                $new_input['text_color'] = $input['text_color'];
            } else {
                // Set the error message
                add_settings_error( 'breaking_news_text_color', 'breaking_news_error', __( 'Insert a valid color for text', 'breaking_news' ), 'error' );
                $new_input['text_color'] = $this->_options['text_color'];
            }
        }

        return apply_filters( 'breaking_news_validate_options', $new_input, $input);
    }

    /**
     * Method that will check if value is a valid HEX color.
     */
    private function _check_color( $value ) {

        if ( preg_match( '/^#[a-f0-9]{6}$/i', $value ) ) { // if user insert a HEX color with #
            return true;
        }

        return false;
    }

    /**
     * Print the Section text
     */
    public function print_block_section_info() {}

    /**
     * Print the Section text
     */
    public function print_content_section_info() {}

    /**
     * Get the title field callback
     */
    public function title_callback()
    {
        printf(
            '<input type="text" id="breaking_news_title" name="%s[title]" value="%s" class="regular-text code" />
                <p class="description" id="tagline-description">%s</p>',
            self::$options_settings_key,
            isset( $this->_options['title'] ) ? esc_attr( $this->_options['title']) : '',
            __( 'A text field for the title of the breaking news area', 'breaking_news' )
        );
    }

    /**
     * Get the backbground color field callback
     */
    public function background_color_callback()
    {
        printf(
            '<input type="text" id="breaking_news_bg_color" name="%s[background_color]" value="%s" />
                <p class="description" id="tagline-description">%s</p>',
            self::$options_settings_key,
            isset( $this->_options['background_color'] ) ? esc_attr( $this->_options['background_color']) : '',
            __( 'A color for the background color of the breaking news area', 'breaking_news' )
        );
    }

    /**
     * Get the color field callback
     */
    public function text_color_callback()
    {
        printf(
            '<input type="text" id="breaking_news_text_color" name="%s[text_color]" value="%s" />
                <p class="description" id="tagline-description">%s</p>',
            self::$options_settings_key,
            isset( $this->_options['text_color'] ) ? esc_attr( $this->_options['text_color']) : '',
            __( 'A color for the text color of the breaking news', 'breaking_news' )
        );
    }

    /**
     * Get the active post block callback
     */
    public function active_post_callback()
    {
        $active_breaking_news_post = get_option( Breaking_News_Post_Editor::$options_content_data_key );

        if( empty( $active_breaking_news_post['post_id'] ) ) {
            echo __( 'The breaking new post is not set', 'breaking_news' );
        } else {
            echo '<a " href="' . get_edit_post_link($active_breaking_news_post['post_id']) . '">' . get_the_title($active_breaking_news_post['post_id']) . '</a>';

            // check if the active post already expired
            if( isset( $active_breaking_news_post['expiration_date'] )
                && $active_breaking_news_post['expiration_date'] > 0
                && current_time('timestamp') > $active_breaking_news_post['expiration_date'] ) {

                echo ' - ' . __( 'expired', 'breaking_news' );
            }

            echo '<p class="description" id="tagline-description">' . __( 'By clicking on the link above you can edit the post', 'breaking_news' ) . '</p>';
        }
    }

    /**
     * Enqueue color picker scripts
     */
    public function color_picker_scripts()
    {
        wp_enqueue_script( 'wp-color-picker' );
        wp_enqueue_style( 'wp-color-picker' );

        // add javascript inline color picker init
        wp_add_inline_script( 'wp-color-picker', "jQuery(document).ready(function($){ $('input[name*=\"color\"]').wpColorPicker(); });");
    }

    /**
     * Adds help tab into options page
     */
    public function options_page_help_tab ()
    {
        $screen = get_current_screen();

        $screen->add_help_tab( array(
            'id'	=> 'breaking_news_help_tab_overview',
            'title'	=> __( 'Overview', 'breaking_news' ),
            'content'	=> '<p>' . __( 'The plugin allows you to feature the individual post as “breaking news.”.', 'breaking_news' ) . '</p>'
                . '<p>' . __( 'Default title of the breaking news will be taken from the post title. But you can also set a custom title for breaking news that will be shown instead of the post title.', 'breaking_news' ) . '</p>'
                . '<p>' . __( 'For breaking news post you can set an expiration date. When the required time expires, the post will not be shown as “breaking news” at the site.', 'breaking_news' ) . '</p>'
                . '<p>' . __( 'There can be only one active breaking news post at a time, which should be the post that was activated last.', 'breaking_news' ) . '</p>'
                . '<p>' . __( 'On this screen, you can change the title and background color of the breaking news area, and set a custom color of the text.', 'breaking_news' ) . '</p>'
        ) );

        $screen->add_help_tab( array(
            'id'	=> 'breaking_news_help_tab_how_to_use',
            'title'	=> __( 'How to use', 'breaking_news' ),
            'content'	=> '<p>' . __( 'To display the breaking news active post on your site, you need to edit the template of your theme and paste the code that retrieves active post. There are several ways to do this:', 'breaking_news' ) . '</p><ul>'
                . '<li>' . __( 'Insert a shortcode [breaking_news] into the template. <br /> Example: <strong>', 'breaking_news' ) . htmlentities("<?php do_shortcode ('[breaking_news]'); ?>") . '</strong></li>'
                . '<li>' . __( 'Run the function get_the_breaking_news_active_post. <br /> Example: <strong>', 'breaking_news' ) . htmlentities("<?php get_the_breaking_news_active_post ();?>") . '</strong></li>'
                . '<li>' . __( 'You can also display breaking news active post on any page or individual post, you need to edit the page or post and insert a shortcode <strong>[breaking_news]</strong> into the editor.', 'breaking_news' ) . '</li></ul>'
        ) );
    }
}