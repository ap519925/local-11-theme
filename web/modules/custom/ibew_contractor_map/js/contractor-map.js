/**
 * @file
 * IBEW Contractor Map JavaScript.
 */

(function ($, Drupal, drupalSettings) {
    'use strict';

    // Global map variable.
    var map;
    var markers = [];

    // Initialize map when Google Maps API is loaded.
    window.initMap = function () {
        if ($('#contractor-map').length === 0) {
            return;
        }

        // Get contractor data.
        var contractors = drupalSettings.ibewContractorMap.contractors || [];

        if (contractors.length === 0) {
            console.log('No contractors with coordinates found.');
            return;
        }

        // Calculate center of all contractors.
        var bounds = new google.maps.LatLngBounds();

        contractors.forEach(function (contractor) {
            if (contractor.lat && contractor.lng) {
                bounds.extend({ lat: parseFloat(contractor.lat), lng: parseFloat(contractor.lng) });
            }
        });

        // Initialize map.
        map = new google.maps.Map(document.getElementById('contractor-map'), {
            zoom: 8,
            center: bounds.getCenter(),
            scrollWheelZoom: true
        });

        // Fit map to show all markers.
        map.fitBounds(bounds);

        // Create markers for each contractor.
        contractors.forEach(function (contractor) {
            if (contractor.lat && contractor.lng) {
                var marker = new google.maps.Marker({
                    position: { lat: parseFloat(contractor.lat), lng: parseFloat(contractor.lng) },
                    map: map,
                    title: contractor.title,
                    animation: google.maps.Animation.DROP
                });

                // Build info window content.
                var contentString = '<div class="contractor-info-window">' +
                    '<h4>' + contractor.title + '</h4>';

                if (contractor.address) {
                    contentString += '<p class="address">' + contractor.address + '</p>';
                }

                if (contractor.phone) {
                    contentString += '<p class="phone"><a href="tel:' + contractor.phone + '">' + contractor.phone + '</a></p>';
                }

                if (contractor.website) {
                    contentString += '<p class="website"><a href="' + contractor.website + '" target="_blank">Visit Website</a></p>';
                }

                contentString += '</div>';

                var infoWindow = new google.maps.InfoWindow({
                    content: contentString
                });

                marker.addListener('click', function () {
                    // Close all other info windows first.
                    infoWindow.open(map, marker);
                });

                markers.push(marker);
            }
        });
    };

    // Initialize when DOM is ready if Google Maps is already loaded.
    $(document).ready(function () {
        if (typeof google !== 'undefined' && google.maps) {
            window.initMap();
        }
    });

})(jQuery, Drupal, drupalSettings);
