<?php
/**
 * Class WavePlayer_Playlist_Post_Type
 * Handles the custom post type registration and management
 */
class WavePlayer_Playlist_Post_Type
{
    /**
     * Post type name
     */
    const POST_TYPE = 'waveplayer_playlist';

    /**
     * Initialize the class
     */
    public function __construct()
    {
        add_action('init', [$this, 'register_post_type']);
        add_action('add_meta_boxes', [$this, 'add_meta_boxes']);
        add_action('save_post', [$this, 'save_playlist_meta']);
        add_filter('manage_' . self::POST_TYPE . '_posts_columns', [$this, 'playlist_columns']);
        add_action('manage_' . self::POST_TYPE . '_posts_custom_column', [$this, 'playlist_custom_columns'], 10, 2);
    }

    /**
     * Register the playlist post type
     */
    public function register_post_type()
    {
        register_post_type(self::POST_TYPE, [
            'labels'       => [
                'name'          => 'Playlists',
                'singular_name' => 'Playlist',
                'add_new'       => 'Add New Playlist',
                'add_new_item'  => 'Add New Playlist',
                'edit_item'     => 'Edit Playlist',
                'view_item'     => 'View Playlist',
                'search_items'  => 'Search Playlists',
            ],
            'public'       => true,
            'supports'     => ['title'],
            'menu_icon'    => 'dashicons-playlist-audio',
            'show_in_menu' => true,
            'has_archive'  => false,
        ]);
    }

    /**
     * Add meta boxes to the playlist post type
     */
    public function add_meta_boxes()
    {
        add_meta_box(
            self::POST_TYPE . '_tracks',
            'Playlist Tracks',
            [$this, 'render_tracks_meta_box'],
            self::POST_TYPE
        );
    }

    /**
     * Render the tracks meta box
     */
    public function render_tracks_meta_box($post)
    {
        wp_nonce_field('save_playlist_tracks', 'playlist_tracks_nonce');
        wp_nonce_field('get_playlist_tracks', 'get_playlist_tracks_nonce');
        ?>
        <div id="playlist-spinner">
            <div class="playlist-spinner-container">
                <span class="playlist-spinner-icon"></span>
            </div>
        </div>
        <div id="playlist-tracks">
            <input type="hidden" id="playlist_post_id" value="<?php echo esc_attr($post->ID); ?>" />
            <div class="tracks-container">
                <!-- Track list will go here -->
            </div>
            <button type="button" class="button add-track">Add Track</button>
        </div>
        <?php
}

    /**
     * Save playlist meta data
     */
    public function save_playlist_meta($post_id)
    {
        if (!isset($_POST['playlist_tracks_nonce']) ||
            !wp_verify_nonce($_POST['playlist_tracks_nonce'], 'save_playlist_tracks')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        if (isset($_POST['playlist_tracks'])) {
            update_post_meta($post_id, '_playlist_tracks', $_POST['playlist_tracks']);
        }
    }

    /**
     * Add custom columns to the playlist list
     */
    public function playlist_columns($columns)
    {
        $new_columns = [];
        foreach ($columns as $key => $value) {
            $new_columns[$key] = $value;
            if ($key === 'title') {
                $new_columns['shortcode'] = 'Shortcode';
            }
        }
        return $new_columns;
    }

    /**
     * Display custom column content
     */
    public function playlist_custom_columns($column_name, $post_id)
    {
        if ($column_name === 'shortcode') {
            echo '<code>[wvp_playlist playlist="' . $post_id . '"]</code>';
        }
    }
}
