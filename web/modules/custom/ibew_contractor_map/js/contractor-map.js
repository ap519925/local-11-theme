/**
 * @file
 * IBEW Contractor Map JavaScript - Optimized for performance.
 */

(function ($, Drupal, drupalSettings) {
    'use strict';

    let mapInitialized = false;
    let markers = [];
    let infoWindow;

    // Global init function for Google Maps API callback
    window.initContractorMap = function () {
        console.log('IBEW Map: initContractorMap called');

        if (mapInitialized) return;

        // Ensure DOM is ready before trying to find elements
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', window.initContractorMap);
            return;
        }

        const mapElement = document.getElementById('contractorMap');
        const targetElement = mapElement || document.getElementById('contractor-map');

        if (!targetElement) {
            console.error('IBEW Map: Target element not found');
            return;
        }

        console.log('IBEW Map: Target element found', targetElement);

        // Get contractor data from drupalSettings
        const contractors = drupalSettings.ibewContractorMap && drupalSettings.ibewContractorMap.contractors || [];
        console.log('IBEW Map: Contractors data', contractors.length, 'contractors');

        // Default center (Connecticut)
        const defaultCenter = { lat: 41.50, lng: -72.80 };

        // Optimized map settings for better performance
        const map = new google.maps.Map(targetElement, {
            zoom: 9,
            center: defaultCenter,
            mapTypeId: 'roadmap',
            // Disable unnecessary controls for performance
            disableDefaultUI: false,
            streetViewControl: true,
            mapTypeControl: true,
            fullscreenControl: true,
            zoomControl: true,
            // Performance optimizations
            gestureHandling: 'greedy', // Better for touch
            optimization: true,
            // Minimal styles for faster rendering
            styles: []
        });

        infoWindow = new google.maps.InfoWindow();

        // Create bounds to fit all markers
        const bounds = new google.maps.LatLngBounds();

        // Create markers for each contractor - optimized
        contractors.forEach(function (contractor) {
            if (contractor.lat && contractor.lng) {
                const lat = parseFloat(contractor.lat);
                const lng = parseFloat(contractor.lng);

                // Add to bounds
                bounds.extend({ lat: lat, lng: lng });

                // Create Marker - optimized without animation
                const marker = new google.maps.Marker({
                    position: { lat: lat, lng: lng },
                    map: map,
                    title: contractor.title,
                    // No animation for better performance
                    animation: null,
                    optimized: true
                });

                // Store contractor data with marker
                marker.contractorData = contractor;

                // Add click listener
                marker.addListener('click', () => {
                    showContractorInfo(marker, map);
                });

                markers.push(marker);
            }
        });

        // Fit map to show all markers if we have any
        if (markers.length > 0) {
            map.fitBounds(bounds);

            // Add some padding
            google.maps.event.addListenerOnce(map, 'bounds_changed', () => {
                if (map.getZoom() > 12) {
                    map.setZoom(12);
                }
            });
        }

        // Enable scroll wheel zoom after initial load
        map.setOptions({
            scrollwheel: true
        });

        mapInitialized = true;
    };

    // Function to show contractor info 
    function showContractorInfo(marker, map) {
        const contractor = marker.contractorData;

        // Build info window content
        let contentString = `
            <div class="contractor-info-window" style="min-width: 280px; max-width: 320px; background: white; color: #333; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.2);">
                ${contractor.image ?
                `<div style="width: 100%; height: 120px; overflow: hidden; background: #f0f0f0;">
                        <img src="${contractor.image}" alt="${contractor.title}" style="width: 100%; height: 100%; object-fit: cover;">
                    </div>` :
                `<div style="width: 100%; height: 80px; background: linear-gradient(135deg, #1e3a5f 0%, #2d5a8a 100%); display: flex; align-items: center; justify-content: center;">
                        <span style="font-size: 2rem; color: white;">⚡</span>
                    </div>`
            }
                <div style="padding: 12px;">
                    <h4 style="margin: 0 0 8px; font-size: 1.1rem; font-weight: 600; color: #1e3a5f;">${contractor.title}</h4>
                    
                    ${contractor.address ? `<p style="margin: 5px 0; color: #555; font-size: 0.9rem;">📍 ${contractor.address}</p>` : ''}
                    
                    ${contractor.phone ? `<p style="margin: 5px 0; color: #555; font-size: 0.9rem;"><a href="tel:${contractor.phone}" style="color: #0066cc; text-decoration: none;">📞 ${contractor.phone}</a></p>` : ''}
                    
                    <div style="margin-top: 12px; display: flex; gap: 8px; flex-wrap: wrap;">
                        <a href="https://www.google.com/maps/dir/?api=1&destination=${encodeURIComponent(contractor.address || contractor.title)}" 
                           target="_blank" 
                           style="flex: 1; padding: 8px 12px; background: #1e3a5f; color: white; text-align: center; text-decoration: none; border-radius: 4px; font-size: 0.85rem;">
                            🚗 Directions
                        </a>
                        
                        ${contractor.website ? `<a href="${contractor.website}" target="_blank" style="flex: 1; padding: 8px 12px; background: #f7c948; color: #1e3a5f; text-align: center; text-decoration: none; border-radius: 4px; font-size: 0.85rem; font-weight: 600;">🌐 Website</a>` : ''}
                    </div>
                </div>
            </div>
        `;

        infoWindow.setContent(contentString);
        infoWindow.open(map, marker);
    }

    // Initialize when DOM is ready
    $(document).ready(function () {
        // Immediate check
        if (typeof google !== 'undefined' && google.maps && typeof window.initContractorMap === 'function') {
            window.initContractorMap();
        } else {
            // Polling fallback
            let attempts = 0;
            const interval = setInterval(function () {
                attempts++;
                if (typeof google !== 'undefined' && google.maps && typeof window.initContractorMap === 'function') {
                    window.initContractorMap();
                    clearInterval(interval);
                } else if (attempts > 50) {
                    console.warn('IBEW Map: Google Maps API failed to load within 10 seconds.');
                    clearInterval(interval);
                }
            }, 200);
        }
    });

})(jQuery, Drupal, drupalSettings);
