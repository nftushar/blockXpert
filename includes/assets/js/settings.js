/**
 * Admin settings JS for BlockXpert
 * (Renamed from admin-settings.js)
 */
(function($){
    $(document).ready(function(){
        var $tabs = $('.blockxpert-tab');
        var $search = $('#blockxpert-search');
        var $cards = $('.blockxpert-block-card');
        var debounceTimer = null;

        function getActiveTab() {
            var $active = $tabs.filter('.active');
            return $active.length ? $active.data('tab') : 'all';
        }

        function normalize(str){
            return (str || '').toString().toLowerCase();
        }

        function filterCards(){
            var q = normalize($search.val());
            var activeTab = getActiveTab();

            $cards.each(function(){
                var $card = $(this);
                var name = normalize($card.data('block-name'));
                // status may be stored in data-status or reflected from checkbox
                var status = normalize($card.data('status')) || ($card.find('input[type="checkbox"]').is(':checked') ? 'active' : 'inactive');

                var matchesQuery = !q || name.indexOf(q) !== -1;
                var matchesTab = (activeTab === 'all') || (activeTab === status);

                if (matchesQuery && matchesTab) {
                    $card.show();
                } else {
                    $card.hide();
                }
            });
        }

        // Tab click handling
        $tabs.on('click', function(e){
            e.preventDefault();
            $tabs.removeClass('active');
            $(this).addClass('active');
            filterCards();
        });

        // Debounced search
        $search.on('input', function(){
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(function(){
                filterCards();
            }, 180);
        });

        // Initialize yes/no text and data-status from checkboxes
        $cards.find('.blockxpert-toggle-switch input').each(function(){
            var $cb = $(this);
            var $card = $cb.closest('.blockxpert-block-card');
            var yesno = $cb.is(':checked') ? 'Yes' : 'No';
            $card.find('.blockxpert-toggle-yesno').text(yesno);
            $card.attr('data-status', $cb.is(':checked') ? 'active' : 'inactive');
        });

        // On toggle change keep visible state in sync and re-filter
        $cards.on('change', '.blockxpert-toggle-switch input', function(){
            var $cb = $(this);
            var $card = $cb.closest('.blockxpert-block-card');
            var yesno = $cb.is(':checked') ? 'Yes' : 'No';
            $card.find('.blockxpert-toggle-yesno').text(yesno);
            $card.attr('data-status', $cb.is(':checked') ? 'active' : 'inactive');
            filterCards();
        });

        // Initial filter to apply default tab/search state
        filterCards();
    });
})(jQuery);
