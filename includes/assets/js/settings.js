/**
 * Admin settings JS for BlockXpert
 * (Renamed from admin-settings.js)
 */
(function($){
    'use strict';
    
    $(document).ready(function(){
        // Wait a bit to ensure DOM is fully ready
        setTimeout(function() {
            initBlockXpertSettings();
        }, 100);
    });

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
