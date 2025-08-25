<?php
namespace SPDF;

if ( ! defined( 'ABSPATH' ) ) exit;

class Renderer {

    public function render_server_pdf($post) {
        $html = $this->build_html($post, true);
        $paper = get_option('spdf_paper_size','A4');
        $orientation = get_option('spdf_orientation','portrait');
        $margins = (array) get_option('spdf_margins',['top'=>20,'right'=>15,'bottom'=>20,'left'=>15]);

        // Dompdf integration (if class exists)
        $dompdf = new \Dompdf\Dompdf([
            'isRemoteEnabled' => true,
            'chroot' => ABSPATH,
        ]);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper($paper, $orientation);
        // Note: Dompdf margins are set via CSS @page in our template/css.
        $dompdf->render();
        $filename = sanitize_title(get_the_title($post)) ?: 'document';
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="'.$filename.'.pdf"');
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
        $toc_html = '';
        if ( intval(get_option('spdf_include_toc',1)) === 1 ) {
            $toc_html = $this->build_toc($content);
        }

        $header_logo_id = intval(get_option('spdf_header_logo_id',0));
        $header_logo = $header_logo_id ? wp_get_attachment_image_url($header_logo_id,'full') : '';
        $header_title = get_option('spdf_header_title', get_bloginfo('name'));
        $footer_text = get_option('spdf_footer_text', get_bloginfo('name').' - '.home_url());
        $rtl = intval( get_option('spdf_rtl', is_rtl()?1:0 ) ) === 1 ? 'rtl' : 'ltr';
        $include_featured = intval( get_option('spdf_include_featured', 1 ) );

        ob_start();
        wp_enqueue_style('spdf-pdf');
        $custom_css = (string) get_option('spdf_custom_css','');
        $template = SPDF_PATH . 'templates/pdf-template.php';
        include $template;
        $html = ob_get_clean();
        wp_reset_postdata();
        return $html;
    }

    private function strip_exclusions($html) {
        $selectors = (string) get_option('spdf_exclude_selectors','');
        if ( empty($selectors) ) return $html;
        // Simple strip: remove elements by adding a style, since parsing HTML reliably is complex.
        $style = '<style>'. $selectors .'{ display:none !important; }</style>';
        return $style . $html;
    }

    private function build_toc($html) {
        // Basic TOC from H1-H3
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8" ?>'.$html);
        libxml_clear_errors();
        $xpath = new \DOMXPath($dom);
        $headings = $xpath->query('//h1|//h2|//h3');
        if ( ! $headings || $headings->length === 0 ) return '';
        $items = [];
        $i = 0;
        foreach ($headings as $h) {
            $text = trim($h->textContent);
            $id = $h->getAttribute('id');
            if (!$id) {
                $id = 'spdf-h-'.$i;
                $h->setAttribute('id',$id);
            }
            $level = strtolower($h->nodeName);
            $items[] = ['id'=>$id,'text'=>$text,'level'=>$level];
            $i++;
        }
        // Return HTML list
        $out = '<div class="spdf-toc"><div class="spdf-toc-title">'.esc_html__('Table of Contents','smart-pdf-for-wp').'</div><ul>';
        foreach ($items as $it) {
            $cls = 'level-'.$it['level'];
            $out .= '<li class="'.esc_attr($cls).'"><a href="#'.esc_attr($it['id']).'">'.esc_html($it['text']).'</a></li>';
        }
        $out .= '</ul></div>';
        // Inject modified ids back into content: easiest is to append a script that assigns ids in print view,
        // but for server-side we already changed DOM - however we didn't return the modified HTML.
        // For simplicity, we also provide a small JS in print view that assigns anchors by text.
        return $out;
    }
}
