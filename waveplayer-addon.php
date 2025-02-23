<?php
/**
 * Plugin Name: WavePlayer Addon
 * Plugin URI: https://example.com/waveplayer-addon
 * Description: Adds playlist management capabilities to WavePlayer
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://example.com
 * Text Domain: waveplayer-addon
 */

if (!defined('ABSPATH')) {
    exit;
}

class WavePlayer_Addon
{
    /**
     * Plugin instance
     */
    private static $instance = null;

    /**
     * Get plugin instance
     */
    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initialize plugin
     */
    private function __construct()
    {
        $this->define_constants();
        $this->load_dependencies();
        $this->init_hooks();
    }

    /**
     * Define plugin constants
     */
    private function define_constants()
    {
        define('WVP_ADDON_VERSION', '1.0.0');
        define('WVP_ADDON_PATH', trailingslashit(plugin_dir_path(__FILE__)));
        define('WVP_ADDON_URL', trailingslashit(plugin_dir_url(__FILE__)));
    }

    /**
     * Load required files
     */
    private function load_dependencies()
    {
        require_once WVP_ADDON_PATH . 'includes/class-playlist-post-type.php';
        require_once WVP_ADDON_PATH . 'includes/class-track-manager.php';
        require_once WVP_ADDON_PATH . 'includes/class-waveplayer-integration.php';
    }

    /**
     * Initialize hooks and classes
     */
    private function init_hooks()
    {
        // Check if WavePlayer is active
        if (!is_plugin_active('waveplayer/waveplayer.php')) {
            add_action('admin_notices', [$this, 'waveplayer_missing_notice']);
            return;
        }

        // Initialize classes
        new WavePlayer_Playlist_Post_Type();
        new WavePlayer_Integration();

        $track_manager = new WavePlayer_Addon_Track_Manager();
        add_action('wp_ajax_wpa_add_track', [$track_manager, 'handle_ajax_add_track']);
        add_action('wp_ajax_wpa_get_track', [$track_manager, 'handle_ajax_get_track']);

        // Admin assets
        add_action('admin_enqueue_scripts', [$this, 'admin_assets']);
    }

    /**
     * Display notice if WavePlayer is not active
     */
    public function waveplayer_missing_notice()
    {
        ?>
        <div class="notice notice-error">
            <p><?php _e('WavePlayer Addon requires WavePlayer plugin to be installed and activated.', 'waveplayer-addon'); ?></p>
        </div>
        <?php
    }

    /**
     * Enqueue admin assets
     */
    public function admin_assets($hook)
    {
        $screen = get_current_screen();

        if ($screen->post_type !== 'waveplayer_playlist') {
            return;
        }

        wp_enqueue_style(
            'waveplayer-addon-admin',
            WVP_ADDON_URL . 'admin/css/track-manager.css',
            [],
            WVP_ADDON_VERSION
        );

        wp_enqueue_script(
            'waveplayer-addon-admin',
            WVP_ADDON_URL . 'admin/js/track-manager.js',
            ['jquery', 'jquery-ui-sortable'],
            WVP_ADDON_VERSION,
            true
        );

        wp_localize_script('waveplayer-addon-admin', 'wvpAddon', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('wp_playlist_tracks'),
        ]);
    }
}

// Initialize plugin
WavePlayer_Addon::get_instance();