(function ($, Drupal) {
    'use strict';

    Drupal.behaviors.contractorMapForm = {
        attach: function (context, settings) {
            // Find the street address input field.
            var $streetInput = $('input[name="field_street_address[0][value]"]', context).once('contractor-autocomplete');

            if ($streetInput.length) {
                if (typeof google !== 'undefined' && google.maps && google.maps.places) {
                    initAutocomplete($streetInput[0]);
                } else {
                    // Wait for google maps places library to load
                    var attempts = 0;
                    var interval = setInterval(function () {
                        attempts++;
                        if (typeof google !== 'undefined' && google.maps && google.maps.places) {
                            initAutocomplete($streetInput[0]);
                            clearInterval(interval);
                        } else if (attempts > 50) {
                            console.warn('Google Maps Places Library failed to load.');
                            clearInterval(interval);
                        }
                    }, 200);
                }
            }

            function initAutocomplete(inputElement) {
                if (!inputElement) return;

                var autocomplete = new google.maps.places.Autocomplete(inputElement, {
                    types: ['address'],
                    fields: ['address_components', 'geometry', 'formatted_address']
                });

                // Prevent form submission when pressing Enter in autocomplete.
                $(inputElement).keydown(function (e) {
                    if (e.which === 13 && $('.pac-container:visible').length) {
                        e.preventDefault();
                    }
                });

                autocomplete.addListener('place_changed', function () {
                    var place = autocomplete.getPlace();
                    if (!place.geometry) {
                        return;
                    }

                    var components = {
                        street_number: '',
                        route: '',
                        locality: '',
                        administrative_area_level_1: '',
                        postal_code: ''
                    };

                    for (var i = 0; i < place.address_components.length; i++) {
                        var addressType = place.address_components[i].types[0];
                        if (components[addressType] !== undefined) {
                            components[addressType] = place.address_components[i].long_name;
                            if (addressType === 'administrative_area_level_1') {
                                components[addressType] = place.address_components[i].short_name; // Keep state short name (e.g., CT, NY)
                            }
                        }
                    }

                    var streetAddress = components.route;
                    if (components.street_number) {
                        streetAddress = components.street_number + ' ' + streetAddress;
                    }

                    // Update standard fields
                    if (streetAddress) {
                        $('input[name="field_street_address[0][value]"]').val(streetAddress);
                    }
                    if (components.locality) {
                        $('input[name="field_city[0][value]"]').val(components.locality);
                    }
                    if (components.administrative_area_level_1) {
                        $('input[name="field_state[0][value]"]').val(components.administrative_area_level_1);
                    }
                    if (components.postal_code) {
                        $('input[name="field_zip[0][value]"]').val(components.postal_code);
                    }

                    // Set lat/lng explicitly
                    if (place.geometry.location) {
                        $('input[name="field_latitude[0][value]"]').val(place.geometry.location.lat());
                        $('input[name="field_longitude[0][value]"]').val(place.geometry.location.lng());
                    }
                });
            }
        }
    };
})(jQuery, Drupal);
