jQuery(document).ready(function ($) {

    $('.wps-vcs-is-signup-form').submit(function(){
        $.post(ajaxurl, {action: 'wps_vcs_is_signup'}, function (response) {
            window.location.reload();
        });
    });

    $('.wps-vcs-is-reset-stats').live('click', function (e) {
        e.preventDefault();

        var link = $(this);
        var row = $(this).parents('tr');
        var stat_ID = $(this).data('stat-id');

        $.post(ajaxurl, {action: 'wps_vcs_is_stat_reset', stat_ID: stat_ID}, function (response) {
            $(row).hide(300);
        });

    });

    $('.wps-vcs-is-reset-stats-all').live('click', function (e) {
        e.preventDefault();

        var link = $(this);
        var row = $(this).parents('tr');
        var split_wrapper = $(this).data('split-wrapper');

        $.post(ajaxurl, {action: 'wps_vcs_is_stat_reset_all', split_wrapper: split_wrapper}, function (response) {
            $('tr','#wps-grader-table').hide();
        });

    });

});