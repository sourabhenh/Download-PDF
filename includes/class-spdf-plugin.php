<?php
namespace SPDF;

if ( ! defined( 'ABSPATH' ) ) exit;

require_once SPDF_PATH . 'includes/class-spdf-admin.php';
require_once SPDF_PATH . 'includes/class-spdf-renderer.php';

class Plugin {

    private static $instance = null;
    public $admin;
    public $renderer;

    public static function instance() {
        if ( self::$instance === null ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        $this->admin = new Admin();
        $this->renderer = new Renderer();

        add_action('init', [$this, 'register_rewrite']);
        add_filter('query_vars', [$this, 'add_query_vars']);
        add_action('template_redirect', [$this, 'maybe_generate_pdf']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_filter('the_content', [$this, 'inject_button_auto']);
        add_shortcode('spdf', [$this, 'shortcode_button']);
    }

    public function activate() {
        $this->register_rewrite();
        flush_rewrite_rules();
        // Set defaults if not set
        $defaults = [
            'enabled_post_types' => ['post','page'],
            'button_position'    => 'after',
            'header_logo_id'     => 0,
            'header_title'       => get_bloginfo('name'),
            'footer_text'        => get_bloginfo('name') . ' - ' . home_url(),
            'paper_size'         => 'A4',
            'orientation'        => 'portrait',
            'margins'            => ['top'=>20,'right'=>15,'bottom'=>20,'left'=>15],
            'include_featured'   => 1,
            'include_toc'        => 1,
            'rtl'                => ( is_rtl() ? 1 : 0 ),
            'custom_css'         => '',
            'exclude_selectors'  => '.no-print,.elementor-hidden-desktop',
            'use_server_pdf'     => 1,
        ];
        foreach ($defaults as $k=>$v) {
            if ( get_option("spdf_$k", null) === null ) {
                update_option("spdf_$k", $v);
            }
        }
    }

    public function deactivate() {
        flush_rewrite_rules();
    }

    public function register_rewrite() {
        add_rewrite_endpoint('spdf', EP_ALL);
    }

    public function add_query_vars($vars) {
        $vars[] = 'spdf';
        return $vars;
    }

    public function enqueue_assets() {
        wp_register_style('spdf-pdf', SPDF_URL . 'assets/css/pdf.css', [], SPDF_VERSION, 'all');
        wp_register_script('spdf-frontend', SPDF_URL . 'assets/js/frontend.js', ['jquery'], SPDF_VERSION, true);
    }

    public function is_supported_post_type($type) {
        $enabled = (array) get_option('spdf_enabled_post_types', ['post','page']);
        return in_array($type, $enabled, true);
    }

    public function inject_button_auto($content) {
        if ( ! is_singular() || ! in_the_loop() || ! is_main_query() ) return $content;
        global $post;
        if ( ! $post ) return $content;
        if ( ! $this->is_supported_post_type($post->post_type) ) return $content;
        $pos = get_option('spdf_button_position','after');
        $button = $this->get_button_html($post->ID);
        if ( $pos === 'before' ) return $button . $content;
        if ( $pos === 'after' ) return $content . $button;
        return $content;
    }

    public function get_button_html($post_id) {
        $url = add_query_arg( ['spdf'=>1, 'spdf_nonce'=>wp_create_nonce('spdf_'.$post_id)], get_permalink($post_id) );
        wp_enqueue_style('spdf-pdf');
        wp_enqueue_script('spdf-frontend');
        return '<div class="spdf-wrap"><a class="spdf-button" href="'.esc_url($url).'">'.esc_html__('Download PDF','smart-pdf-for-wp').'</a></div>';
    }

    public function shortcode_button($atts) {
        $atts = shortcode_atts(['id'=>0], $atts, 'spdf');
        $post_id = $atts['id'] ? intval($atts['id']) : get_the_ID();
        if ( ! $post_id ) return '';
        return $this->get_button_html($post_id);
    }

    public function maybe_generate_pdf() {
        $spdf = get_query_var('spdf');
        if ( empty($spdf) ) return;

        $post = get_queried_object();
        if ( ! $post || empty($post->ID) ) return;

        $nonce = isset($_GET['spdf_nonce']) ? sanitize_text_field($_GET['spdf_nonce']) : '';
        if ( ! wp_verify_nonce($nonce, 'spdf_'.$post->ID) ) {
            wp_die(__('Invalid request','smart-pdf-for-wp'));
        }

        $use_server_pdf = intval( get_option('spdf_use_server_pdf', 1 ) );
        $has_dompdf = class_exists('\Dompdf\Dompdf');

        if ( $use_server_pdf && $has_dompdf ) {
            $this->renderer->render_server_pdf($post);
        } else {
            $this->renderer->render_print_view($post);
        }
        exit;
    }
}
