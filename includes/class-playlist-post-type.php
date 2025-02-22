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
    const POST_TYPE = 'wvp_playlist';

    /**
     * Initialize the class
     */
    public function __construct()
    {
        add_action('init', [$this, 'register_post_type']);
        add_action('add_meta_boxes', [$this, 'add_meta_boxes']);
        add_action('save_post', [$this, 'save_playlist_meta']);
    }

    /**
     * Register the playlist post type
     */
    public function register_post_type()
    {
        register_post_type('wp_playlist', [
            'labels'    => [
                'name'          => 'Playlists',
                'singular_name' => 'Playlist',
            ],
            'public'    => true,
            'supports'  => ['title'],
            'menu_icon' => 'dashicons-playlist-audio',
        ]);
    }

    /**
     * Add meta boxes to the playlist post type
     */
    public function add_meta_boxes()
    {
        add_meta_box(
            'wp_playlist_tracks',
            'Playlist Tracks',
            [$this, 'render_tracks_meta_box'],
            'wp_playlist'
        );
    }

    /**
     * Render the tracks meta box
     */
    public function render_tracks_meta_box($post)
    {
        wp_nonce_field('save_playlist_tracks', 'playlist_tracks_nonce');

        // Get saved tracks
        $tracks = get_post_meta($post->ID, '_playlist_tracks', true);
        // echo "<pre style='direction: ltr; text-align: left;'>";
        // var_dump($tracks);
        // die();
        ?>
        <div id="playlist-tracks">
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
}
