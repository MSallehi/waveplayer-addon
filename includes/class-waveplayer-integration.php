<?php
/**
 * Class WavePlayer_Integration
 * Handles the integration between the addon and WavePlayer
 */
class WavePlayer_Integration
{
    /**
     * Initialize the class
     */
    public function __construct()
    {
        add_filter('waveplayer_playlist_data', [$this, 'add_playlist_data'], 10, 2);
        add_filter('waveplayer_track_data', [$this, 'modify_track_data'], 10, 2);
        add_shortcode('wvp_playlist', [$this, 'playlist_shortcode']);
    }

    /**
     * Add custom playlist data to WavePlayer
     */
    public function add_playlist_data($data, $playlist_id)
    {
        $tracks = get_post_meta($playlist_id, '_playlist_tracks', true);

        if (!empty($tracks)) {
            $data['tracks'] = $tracks;
        }

        return $data;
    }

    /**
     * Modify track data before it's passed to WavePlayer
     */
    public function modify_track_data($track_data, $track)
    {
        // Add any custom track modifications here
        return $track_data;
    }

    /**
     * Playlist shortcode handler
     */
    public function playlist_shortcode($atts)
    {
        $atts = shortcode_atts([
            'id'    => 0,
            'theme' => 'default',
            'width' => '100%',
        ], $atts);

        if (empty($atts['id'])) {
            return '';
        }

        // Get playlist tracks
        $tracks = get_post_meta($atts['id'], '_playlist_tracks', true);

        if (empty($tracks)) {
            return '';
        }

        // Generate unique player ID
        $player_id = 'wvp-' . $atts['id'] . '-' . uniqid();

        ob_start();
        ?>
        <div id="<?php echo esc_attr($player_id); ?>"
             class="waveplayer-addon-player"
             style="width: <?php echo esc_attr($atts['width']); ?>">
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                new WavePlayer('#<?php echo esc_js($player_id); ?>', {
                    theme: '<?php echo esc_js($atts['theme']); ?>',
                    tracks: <?php echo wp_json_encode($tracks); ?>
                });
            });
        </script>
        <?php
return ob_get_clean();
    }
}