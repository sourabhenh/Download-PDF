<?php
// Variables available: $post, $content, $toc_html, $header_logo, $header_title, $footer_text, 
// $rtl, $include_featured, $custom_css, $page_styles
?>
<!DOCTYPE html>
<html lang="<?php echo esc_attr(get_locale()); ?>" dir="<?php echo esc_attr($rtl); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc_html( get_the_title( $post ) ); ?></title>

    <?php
    // Ensure PDF styles are printed
    wp_print_styles(['spdf-pdf']);
    ?>

    <style type="text/css">
        /* Base PDF Styles - Always include these */
        body { 
            margin: 0; 
            padding: 20px;
            background: white !important;
            color: #111 !important;
            font-family: system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif;
            font-size: 12pt;
            line-height: 1.5;
        }

        .spdf-container {
            max-width: 100%;
            margin: 0 auto;
        }

        /* Ensure images fit */
        img {
            max-width: 100% !important;
            height: auto !important;
        }

        /* Better text rendering */
        p, div, span {
            font-size: 12pt;
            line-height: 1.5;
        }

        /* Hide elements that shouldn't appear in PDF */
        .wp-block-navigation,
        .site-header,
        .site-footer,
        .comments-area,
        .post-navigation,
        .related-posts,
        .share-buttons,
        .social-media,
        .spdf-button,
        .no-print {
            display: none !important;
        }

        <?php 
        // Include page styles if enabled - properly escaped
        if (!empty($page_styles)): 
            echo "/* === THEME STYLES === */\n";
            // Make sure page styles are properly sanitized
            echo wp_strip_all_tags($page_styles);
            echo "\n/* === END THEME STYLES === */\n\n";
        endif; 
        ?>

        <?php 
        // Include custom CSS - properly escaped and validated
        if (!empty($custom_css)): 
            echo "/* === CUSTOM PDF STYLES === */\n";
            // The custom CSS is already sanitized in the renderer
            echo $custom_css;
            echo "\n/* === END CUSTOM STYLES === */\n";
        endif; 
        ?>
    </style>
</head>
<body>
    <div class="spdf-container <?php echo esc_attr($rtl); ?>">

        <!-- Enhanced Header -->
        <div class="spdf-header">
            <?php if ( !empty($header_logo) ) : ?>
                <div class="spdf-header-logo">
                    <img src="<?php echo esc_url($header_logo); ?>" alt="<?php echo esc_attr($header_title); ?>" />
                </div>
            <?php endif; ?>

            <div class="spdf-header-title">
                <?php echo esc_html( $header_title ); ?>
            </div>
        </div>

        <!-- Post Title -->
        <h1 class="spdf-title"><?php echo esc_html( get_the_title( $post ) ); ?></h1>

        <!-- Post Meta -->
        <div class="spdf-meta">
            <?php echo esc_html( get_bloginfo('name') ); ?> • 
            <?php echo esc_html( get_the_date('', $post) ); ?> • 
            <?php echo esc_url( get_permalink($post) ); ?>
        </div>

        <!-- Table of Contents -->
        <?php if ( ! empty( $toc_html ) ) : ?>
            <?php echo $toc_html; ?>
        <?php endif; ?>

        <!-- Featured Image -->
        <?php if ( $include_featured && has_post_thumbnail($post) ) : ?>
            <div class="spdf-featured">
                <?php echo get_the_post_thumbnail($post, 'large'); ?>
            </div>
        <?php endif; ?>

        <!-- Main Content -->
        <div class="spdf-content">
            <?php echo $content; ?>
        </div>

        <!-- Enhanced Footer -->
        <div class="spdf-footer">
            <?php echo esc_html( $footer_text ); ?>
            <br>
            <small><?php echo esc_html( sprintf( __('Generated on %s', 'smart-pdf-for-wp'), date_i18n(get_option('date_format') . ' ' . get_option('time_format')) ) ); ?></small>
        </div>

    </div>

    <?php if ( ! class_exists('\\Dompdf\\Dompdf') || intval( get_option('spdf_use_server_pdf',1)) !== 1 ) : ?>
    <script>
        // Auto-print for browser PDF generation
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
    <?php endif; ?>

</body>
</html>
