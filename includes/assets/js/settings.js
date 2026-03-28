/**
 * Admin settings JS for BlockXpert
 * Handles AJAX form submission with toast notifications
 */
(function($){
    'use strict';
    
    $(document).ready(function(){
        // Wait a bit to ensure DOM is fully ready
        setTimeout(function() {
            initBlockXpertSettings();
            initFormSubmission();
        }, 100);
    });

    /**
     * Toast notification helper
     */
    function showToast(message, type) {
        type = type || 'success'; // success, error, info, warning
        var $toast = $('<div class="blockxpert-toast blockxpert-toast-' + type + '"><span class="blockxpert-toast-icon"></span><span class="blockxpert-toast-message">' + message + '</span><button type="button" class="blockxpert-toast-close">&times;</button></div>');
        
        $('body').append($toast);
        
        // Animate in
        setTimeout(function() {
            $toast.addClass('blockxpert-toast-show');
        }, 10);
        
        // Close button
        $toast.on('click', '.blockxpert-toast-close', function(e) {
            e.preventDefault();
            $toast.removeClass('blockxpert-toast-show');
            setTimeout(function() {
                $toast.remove();
            }, 300);
        });
        
        // Auto-remove after 5 seconds
        setTimeout(function() {
            if ($toast.parent().length) {
                $toast.removeClass('blockxpert-toast-show');
                setTimeout(function() {
                    $toast.remove();
                }, 300);
            }
        }, 5000);
    }

    /**
     * Initialize form submission handler
     */
    function initFormSubmission() {
        var $form = $('form[action="options.php"]');
        var $submitButton = $form.find('input[type="submit"], button[type="submit"]');
        
        if ($form.length === 0) {
            console.warn('⚠️ No settings form found');
            return;
        }

        // Disable traditional form submission
        $form.on('submit', function(e) {
            e.preventDefault();
            
            // Disable submit button to prevent multiple submissions
            $submitButton.prop('disabled', true);
            var originalText = $submitButton.val() || $submitButton.text();
            $submitButton.val('Saving...').text('Saving...');
            
            // Get checked blocks
            var blocks = [];
            $form.find('input[name="blockxpert_blocks_active[]"]:checked').each(function() {
                blocks.push($(this).val());
            });
            
            // Prepare data
            var data = {
                blocks: blocks,
            };
            
            console.log('💾 Saving blocks:', blocks);
            
            // Get nonce from form
            var nonce = $form.find('input[name="_wpnonce"]').val();
            
            // Send AJAX request
            $.ajax({
                url: '/wp-json/blockxpert/v1/save-settings',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(data),
                headers: {
                    'X-WP-Nonce': nonce,
                },
                success: function(response) {
                    console.log('✅ Success:', response);
                    showToast(response.message || 'Settings saved successfully!', 'success');
                    $submitButton.prop('disabled', false).val(originalText).text(originalText);
                },
                error: function(xhr) {
                    console.error('❌ Error:', xhr);
                    var errorMsg = 'Failed to save settings';
                    
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    } else if (xhr.statusText) {
                        errorMsg = xhr.statusText;
                    }
                    
                    showToast(errorMsg, 'error');
                    $submitButton.prop('disabled', false).val(originalText).text(originalText);
                },
                complete: function() {
                    $submitButton.prop('disabled', false).val(originalText).text(originalText);
                }
            });
        });
    }

    function initBlockXpertSettings() {
        var $searchInput = $('#blockxpert-search');
        var $tabButtons = $('.blockxpert-tab');
        var $blockCards = $('.blockxpert-block-card');
        var searchTimeout = null;

        console.log('🔍 BlockXpert Search Initialized');
        console.log('   Search input found:', $searchInput.length);
        console.log('   Tab buttons found:', $tabButtons.length);
        console.log('   Block cards found:', $blockCards.length);

        // Check if elements exist
        if ($blockCards.length === 0) {
            console.error('❌ No block cards found!');
            return;
        }

        // Get active tab
        function getActiveTab() {
            var activeTab = $tabButtons.filter('.active').data('tab');
            return activeTab || 'all';
        }

        // Perform filtering
        function performSearch() {
            var searchQuery = $searchInput.val().toLowerCase().trim();
            var activeTab = getActiveTab();
            var visibleCount = 0;

            console.log('🔎 Search:', searchQuery, '| Tab:', activeTab);

            $blockCards.each(function() {
                var $card = $(this);
                var blockName = $card.data('block-name');
                var $checkbox = $card.find('input[type="checkbox"]');
                var isActive = $checkbox.is(':checked');
                
                // Get display text
                var displayName = blockName ? blockName.replace(/-/g, ' ').toLowerCase() : '';
                
                // Check if block name matches search
                var nameMatch = !searchQuery || displayName.indexOf(searchQuery) > -1;
                
                // Check if status matches tab filter
                var statusMatch = activeTab === 'all' || 
                                  (activeTab === 'active' && isActive) || 
                                  (activeTab === 'inactive' && !isActive);
                
                // Show or hide
                if (nameMatch && statusMatch) {
                    $card.show();
                    visibleCount++;
                } else {
                    $card.hide();
                }
            });

            console.log('✓ Visible cards:', visibleCount);
        }

        // Search input event
        $searchInput.on('keyup paste input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                performSearch();
            }, 150);
        });

        // Tab click event
        $tabButtons.on('click', function(e) {
            e.preventDefault();
            
            // Remove active class from all tabs
            $tabButtons.removeClass('active');
            
            // Add active class to clicked tab
            $(this).addClass('active');
            
            console.log('📋 Tab clicked:', $(this).data('tab'));
            
            // Perform search with new tab
            performSearch();
        });

        // Initial search
        performSearch();
    }
})(jQuery);
