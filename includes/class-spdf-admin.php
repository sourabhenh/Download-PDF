<?php
namespace SPDF;

if ( ! defined( 'ABSPATH' ) ) exit;

class Admin {

    public function __construct() {
        add_action('admin_menu', [$this, 'add_menu']);
        add_action('admin_init', [$this, 'register_settings']);
        add_filter('plugin_action_links_' . plugin_basename(SPDF_PATH.'smart-pdf-for-wp.php'), [$this, 'settings_link']);
    }

    public function settings_link($links) {
        $links[] = '<a href="'.esc_url( admin_url('options-general.php?page=spdf') ).'">'.__('Settings','smart-pdf-for-wp').'</a>';
        return $links;
    }

    public function add_menu() {
        add_options_page(
            __('Smart PDF','smart-pdf-for-wp'),
            __('Smart PDF','smart-pdf-for-wp'),
            'manage_options',
            'spdf',
            [$this, 'settings_page']
        );
    }

    public function register_settings() {
        register_setting('spdf','spdf_enabled_post_types');
        register_setting('spdf','spdf_button_position');
        register_setting('spdf','spdf_header_logo_id');
        register_setting('spdf','spdf_header_title');
        register_setting('spdf','spdf_footer_text');
        register_setting('spdf','spdf_paper_size');
        register_setting('spdf','spdf_orientation');
        register_setting('spdf','spdf_margins');
        register_setting('spdf','spdf_include_featured');
        register_setting('spdf','spdf_include_toc');
        register_setting('spdf','spdf_rtl');
        register_setting('spdf','spdf_custom_css');
        register_setting('spdf','spdf_exclude_selectors');
        register_setting('spdf','spdf_use_server_pdf');
    }

    public function settings_page() {
        $post_types = get_post_types(['public'=>true],'objects');
        $enabled = (array) get_option('spdf_enabled_post_types',['post','page']);
        $button_position = get_option('spdf_button_position','after');
        $header_logo_id = intval( get_option('spdf_header_logo_id',0) );
        $header_title = get_option('spdf_header_title', get_bloginfo('name') );
        $footer_text = get_option('spdf_footer_text', get_bloginfo('name').' - '.home_url());
        $paper_size = get_option('spdf_paper_size','A4');
        $orientation = get_option('spdf_orientation','portrait');
        $margins = (array) get_option('spdf_margins',['top'=>20,'right'=>15,'bottom'=>20,'left'=>15]);
        $include_featured = intval( get_option('spdf_include_featured',1) );
        $include_toc = intval( get_option('spdf_include_toc',1) );
        $rtl = intval( get_option('spdf_rtl', is_rtl()?1:0 ) );
        $custom_css = get_option('spdf_custom_css','');
        $exclude_selectors = get_option('spdf_exclude_selectors','.no-print,.elementor-hidden-desktop');
        $use_server_pdf = intval( get_option('spdf_use_server_pdf',1) );
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Smart PDF for WP','smart-pdf-for-wp'); ?></h1>
            <form method="post" action="options.php">
                <?php settings_fields('spdf'); ?>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><?php esc_html_e('Enable on post types','smart-pdf-for-wp'); ?></th>
                        <td>
                            <?php foreach ($post_types as $pt) : ?>
                                <label>
                                    <input type="checkbox" name="spdf_enabled_post_types[]" value="<?php echo esc_attr($pt->name); ?>"
                                        <?php checked(in_array($pt->name,$enabled,true)); ?> />
                                    <?php echo esc_html($pt->labels->singular_name); ?>
                                </label><br>
                            <?php endforeach; ?>
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e('Button position','smart-pdf-for-wp'); ?></th>
                        <td>
                            <select name="spdf_button_position">
                                <option value="before" <?php selected($button_position,'before'); ?>><?php esc_html_e('Before content','smart-pdf-for-wp'); ?></option>
                                <option value="after" <?php selected($button_position,'after'); ?>><?php esc_html_e('After content','smart-pdf-for-wp'); ?></option>
                                <option value="manual" <?php selected($button_position,'manual'); ?>><?php esc_html_e('Manual (use [spdf])','smart-pdf-for-wp'); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e('Header logo','smart-pdf-for-wp'); ?></th>
                        <td>
                            <input type="number" name="spdf_header_logo_id" value="<?php echo esc_attr($header_logo_id); ?>" />
                            <p class="description"><?php esc_html_e('Media Attachment ID. Leave 0 for none.','smart-pdf-for-wp'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e('Header title','smart-pdf-for-wp'); ?></th>
                        <td><input type="text" name="spdf_header_title" value="<?php echo esc_attr($header_title); ?>" class="regular-text" /></td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e('Footer text','smart-pdf-for-wp'); ?></th>
                        <td><input type="text" name="spdf_footer_text" value="<?php echo esc_attr($footer_text); ?>" class="regular-text" /></td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e('Paper','smart-pdf-for-wp'); ?></th>
                        <td>
                            <select name="spdf_paper_size">
                                <?php foreach (['A4','Letter','Legal','A5'] as $size): ?>
                                    <option value="<?php echo esc_attr($size); ?>" <?php selected($paper_size,$size); ?>><?php echo esc_html($size); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <select name="spdf_orientation">
                                <option value="portrait" <?php selected($orientation,'portrait'); ?>><?php esc_html_e('Portrait','smart-pdf-for-wp'); ?></option>
                                <option value="landscape" <?php selected($orientation,'landscape'); ?>><?php esc_html_e('Landscape','smart-pdf-for-wp'); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e('Margins (mm)','smart-pdf-for-wp'); ?></th>
                        <td>
                            <label><?php esc_html_e('Top','smart-pdf-for-wp'); ?> <input type="number" name="spdf_margins[top]" value="<?php echo esc_attr($margins['top']??20); ?>" class="small-text" /></label>
                            <label><?php esc_html_e('Right','smart-pdf-for-wp'); ?> <input type="number" name="spdf_margins[right]" value="<?php echo esc_attr($margins['right']??15); ?>" class="small-text" /></label>
                            <label><?php esc_html_e('Bottom','smart-pdf-for-wp'); ?> <input type="number" name="spdf_margins[bottom]" value="<?php echo esc_attr($margins['bottom']??20); ?>" class="small-text" /></label>
                            <label><?php esc_html_e('Left','smart-pdf-for-wp'); ?> <input type="number" name="spdf_margins[left]" value="<?php echo esc_attr($margins['left']??15); ?>" class="small-text" /></label>
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e('Include featured image','smart-pdf-for-wp'); ?></th>
                        <td><input type="checkbox" name="spdf_include_featured" value="1" <?php checked($include_featured,1); ?> /></td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e('Include Table of Contents','smart-pdf-for-wp'); ?></th>
                        <td><input type="checkbox" name="spdf_include_toc" value="1" <?php checked($include_toc,1); ?> /></td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e('RTL content','smart-pdf-for-wp'); ?></th>
                        <td><input type="checkbox" name="spdf_rtl" value="1" <?php checked($rtl,1); ?> /></td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e('Exclude CSS selectors','smart-pdf-for-wp'); ?></th>
                        <td><input type="text" name="spdf_exclude_selectors" value="<?php echo esc_attr($exclude_selectors); ?>" class="regular-text" />
                        <p class="description"><?php esc_html_e('Comma-separated list of selectors to hide in PDF/print (e.g., .no-print,.site-footer)','smart-pdf-for-wp'); ?></p></td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e('Custom CSS for PDF','smart-pdf-for-wp'); ?></th>
                        <td><textarea name="spdf_custom_css" rows="6" class="large-text code"><?php echo esc_textarea($custom_css); ?></textarea></td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e('Use server-side PDF (Dompdf if available)','smart-pdf-for-wp'); ?></th>
                        <td><input type="checkbox" name="spdf_use_server_pdf" value="1" <?php checked($use_server_pdf,1); ?> />
                        <p class="description"><?php esc_html_e('If Dompdf is installed (via another plugin or /vendor), it will be used. Otherwise it falls back to print-to-PDF.','smart-pdf-for-wp'); ?></p></td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
}
