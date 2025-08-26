=== Smart PDF for WP (Enhanced) ===
Contributors: enhanced-team
Requires at least: 5.6
Tested up to: 6.6
Stable tag: 1.1.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Generate clean, styled PDFs with auto-download, site logo, and preserved page styles. FIXED: JavaScript error in admin resolved.

== Description ==

This enhanced version of Smart PDF for WP has been specifically designed to address common issues and add powerful new features to make your PDFs look professional and match your site's branding.

**LATEST FIX (v1.1.2):**
🔧 **FIXED: JavaScript Error in Admin** - Resolved JavaScript syntax error that was displaying code as text
✅ **FIXED: Custom CSS Issue** - Custom CSS now renders properly in PDFs instead of appearing as text
✅ Enhanced admin interface stability and compatibility
✅ Improved CSS validation with better error handling
✅ Better browser compatibility for admin features

**Key Features:**
✅ **Auto-Download Fix** - PDFs download automatically instead of opening in browser
✅ **Site Logo Integration** - Easy logo upload with media library integration  
✅ **Automatic Site Title** - Pulls site title automatically for headers
✅ **Enhanced Footer** - Customizable footer text with generation timestamp
✅ **Page Style Preservation** - Includes your theme's CSS styling in PDFs
✅ **Custom CSS Support** - Add your own CSS with validation and guidance
✅ **Better Table of Contents** - Enhanced TOC with better formatting
✅ **Stable Admin Interface** - All JavaScript errors resolved

== Enhanced Features ==

* **Fixed JavaScript Issues**: Admin interface now works without JavaScript errors
* **Fixed Custom CSS**: Properly renders custom styles in PDFs with validation
* **Auto-Download**: PDFs download directly instead of opening in browser
* **Logo Integration**: Upload and display your site logo in PDF headers
* **Style Preservation**: Include your theme's styles for consistent branding
* **Professional Headers**: Site logo, title, and customizable elements
* **Smart Footer**: Customizable footer text with generation timestamp
* **Enhanced TOC**: Automatic table of contents from H1-H4 headings
* **Content Filtering**: Hide specific elements using CSS selectors
* **CSS Validation**: Real-time validation and guidance for custom CSS
* **Multi-Format Support**: Various paper sizes and orientations
* **RTL Support**: Full right-to-left language support

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/smart-pdf-for-wp-enhanced/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure settings in Settings → Smart PDF
4. Upload your logo and customize header/footer text
5. Add custom CSS if needed (with built-in validation)
6. Enable "Auto-Download" for direct PDF downloads

== Custom CSS Guidelines ==

When adding custom CSS for PDFs:
* Use standard CSS properties only (no HTML tags or JavaScript)
* Avoid `position: fixed` and `position: sticky` 
* Use pt, px, or mm units for better PDF rendering
* Target these classes: `.spdf-container`, `.spdf-header`, `.spdf-content`, `.spdf-footer`, `.spdf-title`
* The plugin validates your CSS in real-time

**Example Custom CSS:**
```css
body { font-family: Georgia, serif; }
.spdf-title { color: #2c3e50; font-size: 24pt; }
.spdf-content p { text-align: justify; }
.spdf-header { border-bottom: 3px solid #3498db; }
```

== Shortcode ==

`[spdf]` - Renders the download button for the current post
`[spdf id="123"]` - Renders a button for a specific post ID

== Configuration ==

The enhanced version includes these settings:

* **Logo Upload**: Visual logo picker with media library integration  
* **Auto-Download**: Force PDF download instead of browser preview
* **Page Styles**: Include theme CSS for visual consistency
* **Custom CSS**: Add your own styles with real-time validation
* **Enhanced Exclusions**: Better control over hidden elements
* **Professional Headers**: Automatic site title pulling
* **Smart Footer**: Customizable footer with timestamps

== Changelog ==

= 1.1.2 (JavaScript Fix) =
* **FIXED**: JavaScript error in admin interface that displayed code as text
* **IMPROVED**: Better browser compatibility for admin features
* **IMPROVED**: Enhanced error handling in CSS validation
* **IMPROVED**: More stable admin interface experience
* **IMPROVED**: Better quote and HTML escaping in JavaScript

= 1.1.1 (CSS Fix Version) =
* **FIXED**: Custom CSS now renders properly in PDFs instead of appearing as text
* **NEW**: Real-time CSS validation in admin interface
* **NEW**: Enhanced CSS sanitization and security
* **IMPROVED**: Template structure for better CSS handling

= 1.1.0 (Enhanced Version) =
* **FIXED**: PDF now downloads automatically instead of opening in browser
* **NEW**: Logo upload with media library integration
* **NEW**: Automatic site title detection and display
* **NEW**: Enhanced footer with generation timestamp
* **NEW**: Page style preservation option
* **IMPROVED**: Table of contents formatting and functionality

= 1.0.0 =
* Initial release

== Support ==

If you encounter any issues:
1. Ensure you're using the latest version (1.1.2)
2. Check the admin interface for validation messages
3. Clear browser cache after updating
4. Test with a simple post first

== License ==

GPL-2.0-or-later
