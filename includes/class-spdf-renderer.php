<?php
namespace SPDF;

if ( ! defined( 'ABSPATH' ) ) exit;

class Renderer {

    public function render_server_pdf($post) {
        $html = $this->build_html($post, true);
        $paper = get_option('spdf_paper_size','A4');
        $orientation = get_option('spdf_orientation','portrait');

        // Dompdf integration (if class exists)
        $dompdf = new \Dompdf\Dompdf([
            'isRemoteEnabled' => true,
            'chroot' => ABSPATH,
        ]);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper($paper, $orientation);
        $dompdf->render();

        $filename = sanitize_title(get_the_title($post)) ?: 'document';

        // MAIN FIX: Change from 'inline' to 'attachment' to force download
        $auto_download = intval(get_option('spdf_auto_download', 1));
        $disposition = $auto_download ? 'attachment' : 'inline';

        header('Content-Type: application/pdf');
        header('Content-Disposition: ' . $disposition . '; filename="'.$filename.'.pdf"');
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');

        echo $dompdf->output();
    }

    public function render_print_view($post) {
        $html = $this->build_html($post, false);
        status_header(200);
        nocache_headers();
        header('Content-Type: text/html; charset=utf-8');
        echo $html;
    }

    private function build_html($post, $for_pdf = true) {
        setup_postdata($post);
        $content = apply_filters('the_content', $post->post_content);
        $content = $this->strip_exclusions($content);

        // Enhanced: Get current page styles if enabled
        $page_styles = '';
        if (intval(get_option('spdf_include_page_styles', 1)) === 1) {
            $page_styles = $this->get_page_styles();
        }

        $toc_html = '';
        if ( intval(get_option('spdf_include_toc',1)) === 1 ) {
            $toc_html = $this->build_toc($content);
        }

        // Enhanced header logo handling
        $header_logo_id = intval(get_option('spdf_header_logo_id',0));
        $header_logo = '';
        if ($header_logo_id) {
            $logo_data = wp_get_attachment_image_src($header_logo_id, 'medium');
            if ($logo_data) {
                $header_logo = $logo_data[0];
            }
        }

        // Enhanced header title - auto-pull site title if not set
        $header_title = get_option('spdf_header_title', '');
        if (empty($header_title)) {
            $header_title = get_bloginfo('name');
            update_option('spdf_header_title', $header_title);
        }

        // Enhanced footer text
        $footer_text = get_option('spdf_footer_text', '');
        if (empty($footer_text)) {
            $footer_text = get_bloginfo('name') . ' - ' . home_url();
            update_option('spdf_footer_text', $footer_text);
        }

        $rtl = intval( get_option('spdf_rtl', is_rtl()?1:0 ) ) === 1 ? 'rtl' : 'ltr';
        $include_featured = intval( get_option('spdf_include_featured', 1 ) );

        // FIXED: Properly sanitize and prepare custom CSS
        $custom_css = $this->sanitize_custom_css(get_option('spdf_custom_css',''));

        ob_start();
        wp_enqueue_style('spdf-pdf');

        $template = SPDF_PATH . 'templates/pdf-template.php';
        include $template;

        $html = ob_get_clean();
        wp_reset_postdata();
        return $html;
    }

    // NEW: Sanitize custom CSS to prevent HTML breaking
    private function sanitize_custom_css($css) {
        if (empty($css)) {
            return '';
        }

        // Remove any potential HTML tags that might break the style block
        $css = strip_tags($css);

        // Remove any </style> tags that could break our style block
        $css = str_replace(['</style>', '<style>', '<Style>', '</Style>'], '', $css);

        // Remove any script tags for security
        $css = preg_replace('/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi', '', $css);

        // Ensure CSS ends properly (add semicolon if missing)
        $css = trim($css);
        if (!empty($css) && !str_ends_with($css, ';') && !str_ends_with($css, '}')) {
            $css .= ';';
        }

        return $css;
    }

    // Get current page styles
    private function get_page_styles() {
        global $wp_styles;

        if (!$wp_styles) return '';

        $styles = '';

        // Get theme stylesheet
        $theme_style = get_stylesheet_uri();
        if ($theme_style) {
            $theme_css = $this->fetch_css_content($theme_style);
            if ($theme_css) {
                $styles .= "/* Theme Styles */\n" . $theme_css . "\n\n";
            }
        }

        // Get additional stylesheets (limited to prevent bloat)
        $allowed_handles = ['wp-block-library', 'classic-theme-styles'];
        foreach ($allowed_handles as $handle) {
            if (isset($wp_styles->registered[$handle])) {
                $style_url = $wp_styles->registered[$handle]->src;
                if ($style_url && !str_contains($style_url, 'admin')) {
                    $css_content = $this->fetch_css_content($style_url);
                    if ($css_content) {
                        $styles .= "/* $handle */\n" . $css_content . "\n\n";
                    }
                }
            }
        }

        return $styles;
    }

    private function fetch_css_content($url) {
        // Convert relative URLs to absolute
        if (strpos($url, '//') === 0) {
            $url = (is_ssl() ? 'https:' : 'http:') . $url;
        } elseif (strpos($url, '/') === 0) {
            $url = home_url($url);
        }

        // Use WordPress HTTP API for better compatibility
        $response = wp_remote_get($url, [
            'timeout' => 10,
            'sslverify' => false
        ]);

        if (is_wp_error($response)) {
            return '';
        }

        $css = wp_remote_retrieve_body($response);

        // Clean up CSS for PDF compatibility
        $css = $this->clean_css_for_pdf($css);

        return $css;
    }

    private function clean_css_for_pdf($css) {
        // Remove problematic CSS rules for PDF
        $css = preg_replace('/@media\s+screen[^{]*{[^{}]*({[^{}]*}[^{}]*)*}/', '', $css);
        $css = preg_replace('/position\s*:\s*fixed[^;]*;/', '', $css);
        $css = preg_replace('/position\s*:\s*sticky[^;]*;/', '', $css);
        $css = str_replace('!important', '', $css); // Remove !important to allow PDF overrides

        return $css;
    }

    private function strip_exclusions($html) {
        $selectors = (string) get_option('spdf_exclude_selectors','');
        if ( empty($selectors) ) return $html;

        // Enhanced exclusion handling
        $selector_array = array_map('trim', explode(',', $selectors));
        $style = '<style type="text/css">';
        foreach ($selector_array as $selector) {
            if (!empty($selector)) {
                $style .= $selector . ' { display: none !important; } ';
            }
        }
        $style .= '</style>';

        return $style . $html;
    }

    private function build_toc($html) {
        // Enhanced TOC generation with better HTML parsing
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);

        // Better HTML encoding handling
        $html_with_encoding = '<?xml encoding="utf-8" ?>' . $html;
        $dom->loadHTML($html_with_encoding, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $xpath = new \DOMXPath($dom);
        $headings = $xpath->query('//h1|//h2|//h3|//h4');

        if ( ! $headings || $headings->length === 0 ) return '';

        $items = [];
        $i = 0;
        foreach ($headings as $h) {
            $text = trim($h->textContent);
            if (empty($text)) continue;

            $id = $h->getAttribute('id');
            if (!$id) {
                $id = 'spdf-h-'.$i;
                $h->setAttribute('id',$id);
            }
            $level = strtolower($h->nodeName);
            $items[] = ['id'=>$id,'text'=>$text,'level'=>$level];
            $i++;
        }

        if (empty($items)) return '';

        // Generate enhanced TOC HTML
        $out = '<div class="spdf-toc">';
        $out .= '<div class="spdf-toc-title">'.esc_html__('Table of Contents','smart-pdf-for-wp').'</div>';
        $out .= '<ul class="spdf-toc-list">';
        foreach ($items as $it) {
            $cls = 'level-'.$it['level'];
            $out .= '<li class="'.$cls.'"><a href="#'.esc_attr($it['id']).'">'.esc_html($it['text']).'</a></li>';
        }
        $out .= '</ul></div>';

        return $out;
    }
}
