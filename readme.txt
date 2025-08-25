=== Smart PDF for WP (Lite) ===
Contributors: your-team
Requires at least: 5.6
Tested up to: 6.6
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Generate clean, styled PDFs of posts, pages, and custom post types. Uses server-side Dompdf if available, else print-to-PDF fallback.

== Description ==

This plugin adds a "Download PDF" button to supported post types. It builds a printer-optimized HTML view with header, footer, optional featured image and Table of Contents. If the Dompdf library is available on your site (e.g., via a separate plugin or by placing it in /vendor), the plugin will stream a server-generated PDF. Otherwise, it falls back to the browser's print-to-PDF, which works reliably across modern browsers.

== Features ==
* Button auto-injected before/after content or via [spdf] shortcode.
* Header logo, title, and footer text.
* Featured image support.
* Table of Contents generated from H1–H3 headings.
* Exclude CSS selectors (hide parts of the page).
* Custom CSS for PDF/print.
* Paper size and orientation.
* RTL support.
* Works with posts, pages, and any public CPT.

== Server-side PDF (optional) ==
If you want server-side PDF generation:
1. Install a plugin that provides Dompdf, or
2. Place Dompdf in `wp-content/plugins/smart-pdf-for-wp/vendor` and include its autoloader via a small mu-plugin, or
3. Load Dompdf via your theme's composer autoload.

If `\Dompdf\Dompdf` is available and "Use server-side PDF" is enabled in Settings → Smart PDF, server PDFs will be generated.

== Shortcode ==
`[spdf]` renders the button for the current post.
`[spdf id="123"]` renders a button for a specific post ID.

== License ==
GPL-2.0-or-later
