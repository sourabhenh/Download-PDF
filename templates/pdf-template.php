<?php
// Variables available: $post, $content, $toc_html, $header_logo, $header_title, $footer_text, $rtl, $include_featured, $custom_css
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo esc_html( get_the_title( $post ) ); ?></title>
<?php
// Ensure style is printed
wp_print_styles(['spdf-pdf']);
?>
<style>
<?php echo $custom_css; ?>
/* Exclude selectors */
<?php echo esc_html( (string) get_option('spdf_exclude_selectors','') ); ?> { display:none !important; }
</style>
</head>
<body>
<div class="spdf-container <?php echo $rtl === 'rtl' ? 'rtl' : 'ltr'; ?>">
  <div class="spdf-header">
    <?php if ( $header_logo ) : ?>
      <div class="spdf-logo">
        <img src="<?php echo esc_url($header_logo); ?>" alt="<?php echo esc_attr( $header_title ); ?>" style="max-height:60px;">
      </div>
    <?php endif; ?>
    <h1 class="spdf-title"><?php echo esc_html( get_the_title( $post ) ); ?></h1>
    <div class="spdf-meta">
      <?php echo esc_html( get_bloginfo('name') ); ?> • <?php echo esc_html( get_the_date('', $post) ); ?> • <?php echo esc_url( get_permalink($post) ); ?>
    </div>
  </div>

  <div class="spdf-body">
    <?php if ( ! empty( $toc_html ) ) : ?>
      <?php echo $toc_html; ?>
    <?php endif; ?>

    <?php if ( $include_featured && has_post_thumbnail($post) ) : ?>
      <div class="spdf-featured"><?php echo get_the_post_thumbnail($post, 'large'); ?></div>
    <?php endif; ?>

    <div class="spdf-content">
      <?php echo $content; ?>
    </div>
  </div>

  <div class="spdf-footer">
    <?php echo esc_html( $footer_text ); ?>
  </div>
</div>

<?php if ( ! class_exists('\Dompdf\Dompdf') || intval( get_option('spdf_use_server_pdf',1)) !== 1 ) : ?>
<script>
  // Auto-open print dialog in fallback mode
  window.addEventListener('load', function(){
    if (window.matchMedia) {
      // give a small delay for images
      setTimeout(function(){ window.print(); }, 400);
    }
  });
</script>
<?php endif; ?>
</body>
</html>
