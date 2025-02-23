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
        add_shortcode('wvp_playlist', [$this, 'playlist_shortcode']);
    }

    /**
     * Playlist shortcode handler
     */
    public function playlist_shortcode($atts)
    {
        $atts = shortcode_atts([
            'playlist' => 0,
        ], $atts);

        if (empty($atts['playlist'])) {
            return '';
        }

        $playlist_id = $atts['playlist'];

        $tracks = get_post_meta($playlist_id, '_playlist_tracks', true);

        if (empty($tracks)) {
            return '';
        }

        $ids_array = [];
        foreach ($tracks as $key => $track) {
            $att_id = $this->attachment_url_to_postid($track['audio']);
            if ($att_id) {
                $ids_array[] = $att_id;
            }
        }

        $ids = implode(',', $ids_array);

        // Convert to main plugin's format
        return do_shortcode(sprintf(
            '[waveplayer ids="%d"]',
            $ids,
        ));
    }

    public function attachment_url_to_postid($url)
    {
        if (strpos($url, 'http') !== 0 && strpos($url, '//') !== 0) {
            $upload_path = trailingslashit(str_replace(site_url(), '', wp_upload_dir()['baseurl']));
            if (0 === strpos($url, $upload_path)) {
                $url = site_url() . $url;
            }
        }

        return (int) attachment_url_to_postid($url);
    }
}
