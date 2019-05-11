/**
 * @author Eric COURTIAL <e.courtial30@gmail.com>
 *
 * Handle the main menu and the scenario/savegame selection.
 */
define(
    ["jquery"],
    function ($) {
        "use strict";

        $('#newGame').click(function () {
            document.location="/scenarios";
        });

        $( "[id^=scenarioItem]" ).click(function () {
            var key = ($(this).attr('id')).substr(13);
            key = key.replace('"', '');
            key = key.replace('"', '');
            $('#scenarioDescription').empty();
            $('#scenarioDescription').html(scenarios[key]);
        });
    }
);