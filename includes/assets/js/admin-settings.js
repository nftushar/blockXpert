jQuery(document).ready(function($) {
    // Show initial tab
    $("#blocks-section").show();
    
    // Handle tab clicks
    $(".nav-tab").on("click", function(e) {
        e.preventDefault();
        var tab = $(this).data("tab");
        
        // Update tabs
        $(".nav-tab").removeClass("nav-tab-active");
        $(this).addClass("nav-tab-active");
        
        // Update sections
        $(".blockxpert-section").removeClass("active").hide();
        if(tab === "blocks") {
            $("#blocks-section").addClass("active").show();
        }
    });
    
    // Tabs
    const tabs = document.querySelectorAll(".blockxpert-tab");
    const blockCards = document.querySelectorAll(".blockxpert-block-card");
    tabs.forEach(tab => {
        tab.addEventListener("click", function() {
            tabs.forEach(t => t.classList.remove("active"));
            this.classList.add("active");
            const tabType = this.getAttribute("data-tab");
            blockCards.forEach(card => {
                if (tabType === "all") {
                    card.style.display = "flex";
                } else if (tabType === "active") {
                    card.style.display = card.getAttribute("data-status") === "active" ? "flex" : "none";
                } else if (tabType === "inactive") {
                    card.style.display = card.getAttribute("data-status") === "inactive" ? "flex" : "none";
                }
            });
        });
    });
    
    // Search
    const searchInput = document.getElementById("blockxpert-search");
    if (searchInput) {
        searchInput.addEventListener("input", function() {
            const val = this.value.toLowerCase();
            blockCards.forEach(card => {
                const name = card.getAttribute("data-block-name").replace(/-/g, " ").toLowerCase();
                card.style.display = name.includes(val) ? "flex" : "none";
            });
        });
    }
    
    // Toggle Yes/No
    document.querySelectorAll(".blockxpert-toggle-switch input[type=checkbox]").forEach(function(input) {
        input.addEventListener("change", function() {
            var span = this.closest(".blockxpert-block-card").querySelector(".blockxpert-toggle-yesno");
            span.textContent = this.checked ? "Yes" : "No";
            this.closest(".blockxpert-block-card").setAttribute("data-status", this.checked ? "active" : "inactive");
        });
    });
    

}); 