<?php
if( ! defined('BREAKING_NEWS_PLUGIN_BASENAME')) {
    exit;
}

/**
 * Calls the class on the post edit screen.
 */
function call_breaking_news_post_editor() {
    new Breaking_News_Post_Editor();
}

if ( is_admin() ) {
    add_action( 'load-post.php',     'call_breaking_news_post_editor' );
    add_action( 'load-post-new.php', 'call_breaking_news_post_editor' );
}

/**
 * The Breaking_News_Post_Editor Class.
 */
class Breaking_News_Post_Editor {

    /**
     * Holds the values to be used in the fields callbacks
     */
    private $_options;

    /**
     * Nonce key
     * @var string
     */
    private $_nonce_key = 'breaking_news_custom_box_nonce';

    /**
     * Options content data key
     * @var string
     */
    public static $options_content_data_key = 'breaking_news_content_data';

    /**
     * Add meta_boxes and save_post actions when the class is constructed.
     */
    public function __construct() {

        $this->_options = get_option( self::$options_content_data_key );

        add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
        add_action( 'save_post',      array( $this, 'save'         ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_edit_post_scripts' ) );
    }

    /**
     * The breaking news meta box container.
     * @param string $post_type
     */
    public function add_meta_box( $post_type ) {
        // Limit meta box to certain post types.
        if ( in_array( $post_type, $this->_get_post_types() ) ) {
            add_meta_box(
                'breaking_news_meta_box',
                __( 'Breaking News', 'breaking_news' ),
                array( $this, 'render_meta_box_content' ),
                $post_type,
                'advanced',
                'high'
            );
        }
    }

    /**
     * Get post types to showing breaking news
     * @return array
     */
    private function _get_post_types()
    {
        $args = array(
            'public'   => true,
            '_builtin' => false
        );

        return array_merge(
            array( 'post' ),
            get_post_types($args)
        );
    }

    /**
     * Save the meta when the post is saved.
     * @param int $post_id
     * @return mixed
     */
    public function save( $post_id )
    {
        // Check if our nonce is set.
        if ( ! isset( $_POST[$this->_nonce_key] ) ) {
            return $post_id;
        }

        $nonce = $_POST[$this->_nonce_key];

        // Verify that the nonce is valid.
        if ( ! wp_verify_nonce( $nonce, 'breaking_news_custom_box' ) ) {
            return $post_id;
        }

        /*
         * If this is an autosave, our form has not been submitted,
         * so we don't want to do anything.
         */
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return $post_id;
        }

        // Check the user's permissions.
        if ( 'page' == $_POST['post_type'] ) {
            if ( ! current_user_can( 'edit_page', $post_id ) ) {
                return $post_id;
            }
        } else {
            if ( ! current_user_can( 'edit_post', $post_id ) ) {
                return $post_id;
            }
        }

        $current_breaking_news_post_id = isset( $this->_options['post_id'] ) ? $this->_options['post_id'] : 0;

        if( ! empty($_POST['breaking_news']['make'] ) ) {
            $this->_options['post_id'] = $post_id;

            if( ! empty( $_POST['breaking_news']['custom_post_title'] ) ) {
                $this->_options['custom_post_title'] = sanitize_text_field( $_POST['breaking_news']['custom_post_title'] );
            }
            else {
                $this->_options['custom_post_title'] = '';
            }

            if( isset( $_POST['breaking_news']['use_date'], $_POST['breaking_news']['date'] ) ) {


                if ( $expiration_date = date_create_from_format( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ),  $_POST['breaking_news']['date'] ) ) {
                    $this->_options['expiration_date'] = date_timestamp_get( $expiration_date );
                }
            }
            else {
                $this->_options['expiration_date'] = 0;
            }
        } elseif($current_breaking_news_post_id == $post_id) {
            $this->_options['post_id'] = 0;
        }

        // save updated options
        update_option(self::$options_content_data_key, $this->_options);
    }


    /**
     * Render Meta Box content.
     * @param WP_Post $post The post object.
     */
    public function render_meta_box_content( $post ) {

        // Add an nonce field so we can check for it later.
        wp_nonce_field( 'breaking_news_custom_box', $this->_nonce_key );

        $custom_post_title = '';
        $expiration_date = '';

        if(isset($this->_options['custom_post_title'], $this->_options['post_id']) && $post->ID == $this->_options['post_id']) {
            $custom_post_title = $this->_options['custom_post_title'];

            if( !empty( $this->_options['expiration_date'] ) ) {
                $expiration_date = date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $this->_options['expiration_date'] );
            }
        }
        ?>
        <div>
            <label for="breaking_news_make">
                <input type="checkbox" name="breaking_news[make]" id="breaking_news_make" value="yes" <?php if ( isset ( $this->_options['post_id'] ) ) checked( $this->_options['post_id'], $post->ID ); ?> />
                <?php _e( 'Make this post breaking news', 'breaking_news' )?>
            </label>
        </div>

        <table class="breaking_news_option">
            <tr>
                <td class="label">
                    <label for="breaking_news_custom_title">
                        <?php _e( 'Custom title', 'breaking_news' )?>
                    </label>
                    <p class="description"><?php _e( 'An optional text field containing a custom title that will be shown instead of the post title', 'breaking_news')?></p>
                </td>
                <td>
                    <input type="text" name="breaking_news[custom_post_title]" id="breaking_news_custom_title" value="<?php echo esc_attr($custom_post_title)?>" placeholder="<?php echo get_the_title($post)?>" class="regular-text code custom_title" />
                </td>
            </tr>
            <tr>
                <td class="label">
                    <label for="breaking_news_date">
                        <?php _e( 'Expiration date', 'breaking_news')?>
                    </label>
                    <p class="description"><?php _e( 'You could set an expiration date for the post. When the required time expires, the post will not be marked as “breaking news”.', 'breaking_news')?></p>
                </td>
                <td>
                    <label class="checkbox_label">
                        <input type="checkbox" name="breaking_news[use_date]" id="breaking_news_use_date" value="yes" <?php if( $expiration_date ):?> checked="checked" <?php endif?> />
                        <?php _e( 'Set an expiration date', 'breaking_news')?>
                    </label>

                    <label class="input_label">
                        <input type="text" name="breaking_news[date]" id="breaking_news_date" class="datepicker" value="<?php echo esc_attr($expiration_date)?>" />
                        <?php _e( 'Select the date and time', 'breaking_news')?>
                    </label>
                </td>
            </tr>
        </table>
        <?php
    }

    /**
     * Enqueue edit post scripts scripts
     */
    public function enqueue_edit_post_scripts()
    {

        // enqueue datetime picker styles
        wp_enqueue_style( 'jquery.datetimepicker', plugins_url('css/jquery.datetimepicker.css', __FILE__) );

        // enqueue datetime picker scripts
        wp_enqueue_script(
            'jquery.datetimepicker',
            plugins_url('js/jquery.datetimepicker.full.js', __FILE__),
            array('jquery'),
            false,
            1
        );

        // enqueue edit post scripts
        wp_enqueue_script(
            'breaking-news-edit-post-scripts',
            plugins_url('js/edit-post-scripts.js', __FILE__),
            array('jquery'),
            false,
            1
        );

        // add javascript var with datetime format
        wp_add_inline_script( 'breaking-news-edit-post-scripts', "var breaking_news_datetime_format = '" . get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) . "';", 'before' );

        // enqueue post-editor styles
        wp_enqueue_style( 'breaking-news-post-editor-styles', plugins_url('css/post-editor.css', __FILE__) );
    }
}