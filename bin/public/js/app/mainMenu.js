/**
 * @author Eric COURTIAL <e.courtial30@gmail.com>
 *
 * Handle the main menu and the scenario/savegame selection.
 */
define(
    ["jquery"],
    function ($) {
        "use strict";

        var selectedScenario = null;
        var selectedColor = null;

        /**
         * Click on the button new game
         */
        $('#newGame').click(function () {
            document.location="/scenarios";
        });

        /**
         * Selection of a scenario
         */
        $( "[id^=scenarioItem]" ).click(function () {
            selectedColor = null;
            var key = ($(this).attr('id')).substr(13);
            key = key.replace('"', '');
            key = key.replace('"', '');
            selectedScenario = key;

            $('#scenarioDescription').empty();
            $('#sideBoxBlue').empty();
            $('#sideBoxRed').empty();

            $('#scenarioDescription').html(scenarios[key]);

            var blueUrl = '/scenarios/' + selectedScenario + '/Description/blue.jpg';
            var redUrl = '/scenarios/' + selectedScenario + '/Description/red.jpg';
            $('#sideBoxBlue').append('<img src="' + blueUrl +'"/>');
            $('#sideBoxRed').append('<img src="' + redUrl +'"/>');
        });

        /**
         * Selection of a side in the scenario
         */
        $( "[id^=sideBox]" ).click(function () {
            selectedColor = ($(this).attr('id')).substr(7);
        });

        /**
         * Start the new game
         */
        $( "#selectScenario" ).click(function () {
            if (!selectedScenario || !selectedColor) {
                alert('You must first select a scenario and a side!');
            } else {
                var formData = "scenario=" + selectedScenario;

                $.ajax({
                    url: "save/create",
                    type: "POST",
                    data: formData,
                    dataType: "json",
                    success: function (data) {
                        if (data['success'] == true) {
                            document.location="/scenario/" + selectedScenario + "/" + selectedColor;
                        } else {
                            alert("Sorry: an error occurred during the processing");
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        alert("Sorry: an error occurred during the connexion to the back-end");
                    }
                });
            } // End side and scenario selected
        }); // End on scenario selection
    }
);