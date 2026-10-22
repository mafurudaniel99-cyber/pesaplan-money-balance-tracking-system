/* Dashboard enhancements — currently minimal; charts are rendered by charts.js.
   Could add live refresh of summary cards via AJAX. */
(function() {
    'use strict';
    // Hook for future AJAX refresh of dashboard summary cards
    window.refreshDashboardStats = function() {
        // Not implemented in initial version to keep dashboard stateless.
        // Page reload remains the canonical way to refresh data.
    };
})();
