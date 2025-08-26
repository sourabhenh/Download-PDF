(function($) {
    'use strict';

    $(document).ready(function() {

        // Enhanced PDF button functionality
        $(document).on('click', '.spdf-button', function(e) {
            var $button = $(this);
            var originalText = $button.text();

            // Add loading state
            $button.text('Generating PDF...').css('opacity', '0.7');

            // For print view, we want to open in a new window
            var url = $button.attr('href');

            // Check if this will generate a server PDF or print view
            // If it's a print view, open in new window and trigger print
            if (!url.includes('use_server_pdf=1')) {
                e.preventDefault();

                var printWindow = window.open(url, 'pdf_print', 'width=800,height=600,scrollbars=yes,resizable=yes');

                if (printWindow) {
                    printWindow.onload = function() {
                        // Small delay to ensure content is loaded
                        setTimeout(function() {
                            printWindow.print();
                        }, 500);
                    };
                }

                // Reset button state
                setTimeout(function() {
                    $button.text(originalText).css('opacity', '1');
                }, 1500);

                return false;
            }

            // For server PDF, let it proceed normally but reset button after delay
            setTimeout(function() {
                $button.text(originalText).css('opacity', '1');
            }, 2000);
        });

        // Add smooth scroll for TOC links in print view
        $(document).on('click', '.spdf-toc-list a', function(e) {
            var href = $(this).attr('href');
            if (href && href.startsWith('#')) {
                e.preventDefault();
                var target = $(href);
                if (target.length) {
                    $('html, body').animate({
                        scrollTop: target.offset().top - 20
                    }, 300);
                }
            }
        });

        // Auto-trigger print if this is a print view page
        if (window.location.search.includes('spdf=1') && 
            !window.location.search.includes('use_server_pdf=1')) {

            // Wait for page to fully load
            $(window).on('load', function() {
                setTimeout(function() {
                    window.print();
                }, 1000);
            });
        }

    });

})(jQuery);
