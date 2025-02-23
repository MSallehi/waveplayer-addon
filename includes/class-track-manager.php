<?php
class WavePlayer_Addon_Track_Manager
{
    private function render_track_row($track = [], $index = 0)
    {
        ?>
        <div class="track-row">
            <div class="track-handle">
                <span class="dashicons dashicons-menu"></span>
            </div>
            <div class="track-fields">
                <p>
                    <label><?php _e('Title:', 'waveplayer-addon'); ?></label>
                    <input type="text"
                           name="playlist_tracks[<?php echo $index; ?>][title]"
                           value="<?php echo esc_attr($track['title'] ?? ''); ?>"
                           class="widefat">
                </p>
                <p>
                    <label><?php _e('Audio File:', 'waveplayer-addon'); ?></label>
                    <input type="text"
                           name="playlist_tracks[<?php echo $index; ?>][audio]"
                           value="<?php echo esc_attr($track['audio'] ?? ''); ?>"
                           class="audio-url widefat">
                    <button type="button" class="button select-audio"><?php _e('Select Audio', 'waveplayer-addon'); ?></button>
                </p>
            </div>
            <div class="track-actions">
                <button type="button" class="button remove-track">
                    <span class="dashicons dashicons-trash"></span>
                </button>
            </div>
        </div>
        <?php
    }

    public function handle_ajax_add_track()
    {
        check_ajax_referer('save_playlist_tracks', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Permission denied');
        }

        $index = isset($_POST['index']) ? intval($_POST['index']) : 0;
        ob_start();
        $this->render_track_row([], $index);
        $html = ob_get_clean();

        wp_send_json_success(['html' => $html]);
    }

    public function handle_ajax_get_track()
    {
        check_ajax_referer('get_playlist_tracks', 'nonce');

        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : false;

        if (!$post_id) {
            wp_send_json_error('Invalid post ID');
        }

        $tracks = get_post_meta($post_id, '_playlist_tracks', true);

        if (!is_array($tracks)) {
            $tracks = [];
        }

        $html = '';
        if (!empty($tracks)) {
            ob_start();
            foreach ($tracks as $index => $track) {
                $this->render_track_row($track, $index);
            }
            $html = ob_get_clean();
        }

        wp_send_json_success(['html' => $html]);
    }
}