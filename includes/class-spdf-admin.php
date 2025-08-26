<?php
namespace SPDF;

if ( ! defined( 'ABSPATH' ) ) exit;

class Admin {

    public function __construct() {
        add_action('admin_menu', [$this, 'add_menu']);
        add_action('admin_init', [$this, 'register_settings']);
        add_filter('plugin_action_links_' . plugin_basename(SPDF_PATH.'smart-pdf-for-wp.php'), [$this, 'settings_link']);
        // Removed unused AJAX hook: add_action('wp_ajax_spdf_upload_logo', ...);
        // Removed redundant CSS validation filter: add_filter('pre_update_option_spdf_custom_css', ...);
    }

    public function settings_link($links) {
        $links[] = '<a href="'.admin_url('options-general.php?page=spdf').'">'.__('Settings','smart-pdf-for-wp').'</a>';
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
        register_setting('spdf','spdf_include_page_styles');
        register_setting('spdf','spdf_auto_download');
        register_setting('spdf','spdf_rtl');
        register_setting('spdf','spdf_custom_css', [
            'sanitize_callback' => [$this, 'sanitize_custom_css'] // This is the correct way to sanitize.
        ]);
        register_setting('spdf','spdf_exclude_selectors');
        register_setting('spdf','spdf_use_server_pdf');
    }

    // Removed the redundant validate_custom_css() method.

    public function sanitize_custom_css($css) {
        if (empty($css)) {
            return '';
        }

        // Remove any potential HTML tags
        $css = wp_strip_all_tags($css);

        // Remove any </style> tags that could break our style block
        $css = str_replace(['</style>', '<style>', '<Style>', '</Style>'], '', $css);

        // Remove any script tags for security
        $css = preg_replace('/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi', '', $css);

        return trim($css);
    }

    public function settings_page() {
        // Enqueue media uploader
        wp_enqueue_media();

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
        $include_page_styles = intval( get_option('spdf_include_page_styles',1) );
        $auto_download = intval( get_option('spdf_auto_download',1) );
        $rtl = intval( get_option('spdf_rtl', is_rtl()?1:0 ) );
        $custom_css = get_option('spdf_custom_css','');
        $exclude_selectors = get_option('spdf_exclude_selectors','.no-print,.elementor-hidden-desktop,.spdf-button');
        $use_server_pdf = intval( get_option('spdf_use_server_pdf',1) );

        // Get logo URL for preview
        $logo_url = '';
        if ($header_logo_id) {
            $logo_url = wp_get_attachment_image_url($header_logo_id, 'thumbnail');
        }
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Smart PDF for WP (Enhanced)','smart-pdf-for-wp'); ?></h1>

            <?php if (isset($_GET['settings-updated'])): ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php esc_html_e('Settings saved successfully!','smart-pdf-for-wp'); ?></p>
                </div>
            <?php endif; ?>

            <form method="post" action="options.php">
                <?php settings_fields('spdf'); ?>

                <table class="form-table">

                    <tr>
                        <th scope="row"><?php esc_html_e('Enable on post types','smart-pdf-for-wp'); ?></th>
                        <td>
                            <?php foreach ($post_types as $pt) : ?>
                                <label>
                                    <input type="checkbox" name="spdf_enabled_post_types[]" value="<?php echo esc_attr($pt->name); ?>" <?php checked(in_array($pt->name,$enabled,true)); ?> />
                                    <?php echo esc_html($pt->labels->singular_name); ?>
                                </label><br>
                            <?php endforeach; ?>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><?php esc_html_e('Button position','smart-pdf-for-wp'); ?></th>
                        <td>
                            <label><input type="radio" name="spdf_button_position" value="before" <?php checked($button_position,'before'); ?> /> <?php esc_html_e('Before content','smart-pdf-for-wp'); ?></label><br>
                            <label><input type="radio" name="spdf_button_position" value="after" <?php checked($button_position,'after'); ?> /> <?php esc_html_e('After content','smart-pdf-for-wp'); ?></label><br>
                            <label><input type="radio" name="spdf_button_position" value="manual" <?php checked($button_position,'manual'); ?> /> <?php esc_html_e('Manual (use [spdf])','smart-pdf-for-wp'); ?></label>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><?php esc_html_e('Header Logo','smart-pdf-for-wp'); ?></th>
                        <td>
                            <div id="spdf-logo-preview" style="margin-bottom: 10px;">
                                <?php if ($logo_url): ?>
                                    <img src="<?php echo esc_url($logo_url); ?>" style="max-width: 150px; max-height: 100px;" />
                                <?php endif; ?>
                            </div>
                            <input type="hidden" id="spdf_header_logo_id" name="spdf_header_logo_id" value="<?php echo esc_attr($header_logo_id); ?>" />
                            <button type="button" class="button" id="spdf-upload-logo"><?php esc_html_e('Choose Logo','smart-pdf-for-wp'); ?></button>
                            <button type="button" class="button" id="spdf-remove-logo" <?php echo $header_logo_id ? '' : 'style="display:none;"'; ?>><?php esc_html_e('Remove Logo','smart-pdf-for-wp'); ?></button>
                            <p class="description"><?php esc_html_e('Upload a logo to display in the PDF header.','smart-pdf-for-wp'); ?></p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><?php esc_html_e('Header title','smart-pdf-for-wp'); ?></th>
                        <td>
                            <input type="text" name="spdf_header_title" value="<?php echo esc_attr($header_title); ?>" class="regular-text" />
                            <p class="description"><?php esc_html_e('Title to display in PDF header. Defaults to site name.','smart-pdf-for-wp'); ?></p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><?php esc_html_e('Footer text','smart-pdf-for-wp'); ?></th>
                        <td>
                            <input type="text" name="spdf_footer_text" value="<?php echo esc_attr($footer_text); ?>" class="regular-text" />
                            <p class="description"><?php esc_html_e('Text to display in PDF footer.','smart-pdf-for-wp'); ?></p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><?php esc_html_e('PDF Download Behavior','smart-pdf-for-wp'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="spdf_auto_download" value="1" <?php checked($auto_download, 1); ?> />
                                <?php esc_html_e('Auto-download PDF (instead of opening in browser)','smart-pdf-for-wp'); ?>
                            </label>
                            <p class="description"><?php esc_html_e('When enabled, PDFs will download automatically instead of opening in the browser.','smart-pdf-for-wp'); ?></p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><?php esc_html_e('Include Page Styles','smart-pdf-for-wp'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="spdf_include_page_styles" value="1" <?php checked($include_page_styles, 1); ?> />
                                <?php esc_html_e('Include theme and page styles in PDF','smart-pdf-for-wp'); ?>
                            </label>
                            <p class="description"><?php esc_html_e('When enabled, the PDF will include your theme styles for better visual consistency.','smart-pdf-for-wp'); ?></p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><?php esc_html_e('Paper','smart-pdf-for-wp'); ?></th>
                        <td>
                            <?php foreach (['A4','Letter','Legal','A5'] as $size): ?>
                                <label><input type="radio" name="spdf_paper_size" value="<?php echo esc_attr($size); ?>" <?php checked($paper_size,$size); ?> /> <?php echo esc_html($size); ?></label>
                            <?php endforeach; ?>
                            <br><br>
                            <label><input type="radio" name="spdf_orientation" value="portrait" <?php checked($orientation,'portrait'); ?> /> <?php esc_html_e('Portrait','smart-pdf-for-wp'); ?></label>
                            <label><input type="radio" name="spdf_orientation" value="landscape" <?php checked($orientation,'landscape'); ?> /> <?php esc_html_e('Landscape','smart-pdf-for-wp'); ?></label>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><?php esc_html_e('Margins (mm)','smart-pdf-for-wp'); ?></th>
                        <td>
                            <?php esc_html_e('Top','smart-pdf-for-wp'); ?> <input type="number" name="spdf_margins[top]" value="<?php echo esc_attr($margins['top']??20); ?>" min="0" style="width:60px;" />
                            <?php esc_html_e('Right','smart-pdf-for-wp'); ?> <input type="number" name="spdf_margins[right]" value="<?php echo esc_attr($margins['right']??15); ?>" min="0" style="width:60px;" />
                            <?php esc_html_e('Bottom','smart-pdf-for-wp'); ?> <input type="number" name="spdf_margins[bottom]" value="<?php echo esc_attr($margins['bottom']??20); ?>" min="0" style="width:60px;" />
                            <?php esc_html_e('Left','smart-pdf-for-wp'); ?> <input type="number" name="spdf_margins[left]" value="<?php echo esc_attr($margins['left']??15); ?>" min="0" style="width:60px;" />
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><?php esc_html_e('Include featured image','smart-pdf-for-wp'); ?></th>
                        <td>
                            <input type="checkbox" name="spdf_include_featured" value="1" <?php checked($include_featured, 1); ?> />
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><?php esc_html_e('Include Table of Contents','smart-pdf-for-wp'); ?></th>
                        <td>
                            <input type="checkbox" name="spdf_include_toc" value="1" <?php checked($include_toc, 1); ?> />
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><?php esc_html_e('RTL content','smart-pdf-for-wp'); ?></th>
                        <td>
                            <input type="checkbox" name="spdf_rtl" value="1" <?php checked($rtl, 1); ?> />
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><?php esc_html_e('Exclude CSS selectors','smart-pdf-for-wp'); ?></th>
                        <td>
                            <input type="text" name="spdf_exclude_selectors" value="<?php echo esc_attr($exclude_selectors); ?>" class="large-text" />
                            <p class="description"><?php esc_html_e('Comma-separated list of selectors to hide in PDF/print (e.g., .no-print,.site-footer)','smart-pdf-for-wp'); ?></p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><?php esc_html_e('Custom CSS for PDF','smart-pdf-for-wp'); ?></th>
                        <td>
                            <textarea name="spdf_custom_css" id="spdf_custom_css" rows="12" class="large-text code" placeholder="/* Add your custom CSS here - Example: */
body { font-family: Georgia, serif; }
.spdf-title { color: #2c3e50; font-size: 24pt; }
.spdf-content p { text-align: justify; }
.spdf-header { border-bottom: 3px solid #3498db; }
/* Use pt, px, or mm units for better PDF rendering */"><?php echo esc_textarea($custom_css); ?></textarea>

                            <p class="description">
                                <strong><?php esc_html_e('CSS Guidelines for PDF:','smart-pdf-for-wp'); ?></strong><br>
                                • <?php esc_html_e('Use standard CSS properties only (no JavaScript or HTML tags)','smart-pdf-for-wp'); ?><br>
                                • <?php esc_html_e('Avoid position: fixed, position: sticky, and complex animations','smart-pdf-for-wp'); ?><br>
                                • <?php esc_html_e('Use pt, px, or mm units for better PDF rendering','smart-pdf-for-wp'); ?><br>
                                • <?php esc_html_e('Target classes: .spdf-container, .spdf-header, .spdf-content, .spdf-footer, .spdf-title','smart-pdf-for-wp'); ?><br>
                            </p>

                            <div id="spdf-css-validation" style="margin-top: 10px;"></div>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><?php esc_html_e('Use server-side PDF (Dompdf if available)','smart-pdf-for-wp'); ?></th>
                        <td>
                            <input type="checkbox" name="spdf_use_server_pdf" value="1" <?php checked($use_server_pdf, 1); ?> />
                            <p class="description"><?php esc_html_e('If Dompdf is installed (via another plugin or /vendor), it will be used. Otherwise it falls back to print-to-PDF.','smart-pdf-for-wp'); ?></p>
                        </td>
                    </tr>

                </table>

                <?php submit_button(); ?>
            </form>

            <script>
            jQuery(document).ready(function($) {
                // Media uploader for logo
                var frame;

                $('#spdf-upload-logo').on('click', function(e) {
                    e.preventDefault();

                    if (frame) {
                        frame.open();
                        return;
                    }

                    frame = wp.media({
                        title: 'Choose Logo',
                        button: {
                            text: 'Use this image'
                        },
                        multiple: false,
                        library: {
                            type: 'image'
                        }
                    });

                    frame.on('select', function() {
                        var attachment = frame.state().get('selection').first().toJSON();
                        $('#spdf_header_logo_id').val(attachment.id);
                        $('#spdf-logo-preview').html('<img src="' + attachment.sizes.thumbnail.url + '" style="max-width: 150px; max-height: 100px;" />');
                        $('#spdf-remove-logo').show();
                    });

                    frame.open();
                });

                $('#spdf-remove-logo').on('click', function(e) {
                    e.preventDefault();
                    $('#spdf_header_logo_id').val('');
                    $('#spdf-logo-preview').html('');
                    $(this).hide();
                });

                // Corrected: Live CSS validation
                $('#spdf_custom_css').on('input', function() {
                    var css = $(this).val();
                    var $validation = $('#spdf-css-validation');
                    var issues = [];

                    // Check for potentially problematic patterns
                    if (/<[a-z][\s\S]*>/i.test(css)) {
                        issues.push('HTML tags (like &lt;div&gt; or &lt;script&gt;) are not allowed.');
                    }

                    if (css.toLowerCase().indexOf('position: fixed') !== -1) {
                        issues.push('`position: fixed` may not work as expected in PDFs.');
                    }

                    if (css.toLowerCase().indexOf('position: sticky') !== -1) {
                        issues.push('`position: sticky` may not work as expected in PDFs.');
                    }

                    // Display results
                    if (issues.length > 0) {
                        var errorHtml = '<div style="color: #d63638; background: #fef7f7; padding: 8px; border-left: 4px solid #d63638; margin: 5px 0;">';
                        errorHtml += '<strong>⚠️ CSS Issues Found:</strong>';
                        errorHtml += '<ul style="margin: 5px 0 0 20px; list-style: disc;">';
                        for (var i = 0; i < issues.length; i++) {
                            errorHtml += '<li>' + issues[i] + '</li>';
                        }
                        errorHtml += '</ul></div>';
                        $validation.html(errorHtml);
                    } else if (css.trim().length > 0) {
                        $validation.html('<div style="color: #2271b1; background: #f6f7f7; padding: 8px; border-left: 4px solid #2271b1; margin: 5px 0;">✅ CSS looks good for PDF!</div>');
                    } else {
                        $validation.html('');
                    }
                });
            });
            </script>

        </div>
        <?php
    }
}