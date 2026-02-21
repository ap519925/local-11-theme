/**
 * @file
 * IBEW Contractor Map JavaScript - Enhanced with auto-populate support.
 */

(function ($, Drupal, drupalSettings) {
    'use strict';

    let mapInitialized = false;
    let markers = [];
    let infoWindow;
    let map;

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

        // Get contractor data and map settings from drupalSettings
        const contractorSettings = drupalSettings.ibewContractorMap || {};
        const contractors = contractorSettings.contractors || [];
        const mapSettings = contractorSettings.mapSettings || {};
        console.log('IBEW Map: Contractors data', contractors.length, 'contractors');

        // Use configurable defaults
        const defaultCenter = {
            lat: parseFloat(mapSettings.default_lat) || 41.50,
            lng: parseFloat(mapSettings.default_lng) || -72.80
        };
        const defaultZoom = parseInt(mapSettings.default_zoom) || 9;

        // Initialize map with configurable settings
        map = new google.maps.Map(targetElement, {
            zoom: defaultZoom,
            center: defaultCenter,
            mapTypeId: 'roadmap',
            disableDefaultUI: false,
            streetViewControl: true,
            mapTypeControl: true,
            fullscreenControl: true,
            zoomControl: true,
            gestureHandling: 'greedy',
            optimization: true,
            styles: [
                {
                    featureType: 'poi',
                    elementType: 'labels',
                    stylers: [{ visibility: 'off' }]
                }
            ]
        });

        infoWindow = new google.maps.InfoWindow();

        // Create bounds to fit all markers
        const bounds = new google.maps.LatLngBounds();
        let hasMarkers = false;

        // Create markers for each contractor with coordinates
        contractors.forEach(function (contractor) {
            if (contractor.lat && contractor.lng) {
                const lat = parseFloat(contractor.lat);
                const lng = parseFloat(contractor.lng);

                if (isNaN(lat) || isNaN(lng)) return;

                bounds.extend({ lat: lat, lng: lng });
                hasMarkers = true;

                const marker = new google.maps.Marker({
                    position: { lat: lat, lng: lng },
                    map: map,
                    title: contractor.title,
                    animation: null,
                    optimized: true,
                    icon: {
                        url: 'data:image/svg+xml,' + encodeURIComponent(
                            '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="42" viewBox="0 0 32 42">' +
                            '<path d="M16 0C7.2 0 0 7.2 0 16c0 12 16 26 16 26s16-14 16-26C32 7.2 24.8 0 16 0z" fill="#1e3a5f"/>' +
                            '<circle cx="16" cy="14" r="7" fill="#f7c948"/>' +
                            '<text x="16" y="18" font-size="10" text-anchor="middle" fill="#1e3a5f" font-weight="bold">⚡</text>' +
                            '</svg>'
                        ),
                        scaledSize: new google.maps.Size(32, 42),
                        anchor: new google.maps.Point(16, 42)
                    }
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
        if (hasMarkers) {
            map.fitBounds(bounds);

            google.maps.event.addListenerOnce(map, 'bounds_changed', () => {
                if (map.getZoom() > 14) {
                    map.setZoom(14);
                }
            });
        }

        map.setOptions({ scrollwheel: true });

        mapInitialized = true;

        // Update contractor count display
        updateContractorCount(contractors.length, markers.length);

        // Add click listener to list items
        $(document).on('click', '.ibew-contractor-card', function (e) {
            if ($(e.target).closest('a, button').length > 0 && !$(e.target).closest('.stretched-link').length) {
                return;
            }

            e.preventDefault();
            const contractorId = $(this).data('id');
            const contractorName = $(this).data('name');

            let marker;

            if (contractorId) {
                marker = markers.find(m => m.contractorData && String(m.contractorData.id) === String(contractorId));
            }

            if (!marker && contractorName) {
                marker = markers.find(m => m.getTitle() === contractorName);
            }

            if (marker) {
                google.maps.event.trigger(marker, 'click');
                if (window.innerWidth < 992) {
                    targetElement.scrollIntoView({ behavior: 'smooth' });
                }
            }
        });

        // Add search/filter interaction
        setupSearchInteraction();
    };

    /**
     * Show contractor info window on map marker click.
     */
    function showContractorInfo(marker, map) {
        const contractor = marker.contractorData;

        // Build specialties pills
        let specialtiesHtml = '';
        if (contractor.specialties && contractor.specialties.length > 0) {
            specialtiesHtml = '<div style="margin: 8px 0; display: flex; flex-wrap: wrap; gap: 4px;">';
            contractor.specialties.forEach(function (specialty) {
                specialtiesHtml += `<span style="display: inline-block; padding: 2px 8px; background: #e8f0fe; color: #1e3a5f !important; border-radius: 12px; font-size: 0.75rem; font-weight: 500;">${specialty}</span>`;
            });
            specialtiesHtml += '</div>';
        }

        // Build service areas text
        let serviceAreasHtml = '';
        if (contractor.service_areas && contractor.service_areas.length > 0) {
            serviceAreasHtml = `<p style="margin: 4px 0; color: #666 !important; font-size: 0.8rem;">📍 <span style="color: #666 !important;">Service Areas: ${contractor.service_areas.join(', ')}</span></p>`;
        }

        let contentString = `
            <div class="contractor-info-window" style="min-width: 280px; max-width: 340px; background: white; color: #333; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.2);">
                ${contractor.image ?
                `<div style="width: 100%; height: 120px; overflow: hidden; background: #f0f0f0;">
                        <img src="${contractor.image}" alt="${contractor.title}" style="width: 100%; height: 100%; object-fit: cover;">
                    </div>` :
                `<div style="width: 100%; height: 60px; background: linear-gradient(135deg, #1e3a5f 0%, #2d5a8a 100%); display: flex; align-items: center; justify-content: center;">
                        <span style="font-size: 1.5rem; color: white;">⚡</span>
                    </div>`
            }
                <div style="padding: 12px;">
                    <h4 style="margin: 0 0 6px; font-size: 1.1rem; font-weight: 700; color: #1e3a5f !important;">${contractor.title}</h4>
                    
                    ${contractor.contact_person ? `<p style="margin: 4px 0; color: #555 !important; font-size: 0.85rem;">👤 <span style="color: #555 !important;">${contractor.contact_person}</span></p>` : ''}
                    
                    ${contractor.address ? `<p style="margin: 4px 0; color: #555 !important; font-size: 0.85rem;">📍 <span style="color: #555 !important;">${contractor.address}</span></p>` : ''}
                    
                    ${contractor.phone ? `<p style="margin: 4px 0; font-size: 0.85rem;"><a href="tel:${contractor.phone}" style="color: #0066cc !important; text-decoration: none;">📞 ${contractor.phone}</a></p>` : ''}
                    
                    ${contractor.email ? `<p style="margin: 4px 0; font-size: 0.85rem;"><a href="mailto:${contractor.email}" style="color: #0066cc !important; text-decoration: none;">✉️ ${contractor.email}</a></p>` : ''}
                    
                    ${specialtiesHtml}
                    ${serviceAreasHtml}
                    
                    <div style="margin-top: 10px; display: flex; gap: 6px; flex-wrap: wrap;">
                        <a href="https://www.google.com/maps/dir/?api=1&destination=${encodeURIComponent(contractor.address || contractor.title)}" 
                           target="_blank" 
                           style="flex: 1; padding: 8px 10px; background: #1e3a5f; color: white !important; text-align: center; text-decoration: none; border-radius: 6px; font-size: 0.8rem; font-weight: 600;">
                            🚗 Directions
                        </a>
                        
                        ${contractor.website ? `<a href="${contractor.website}" target="_blank" style="flex: 1; padding: 8px 10px; background: #f7c948; color: #1e3a5f !important; text-align: center; text-decoration: none; border-radius: 6px; font-size: 0.8rem; font-weight: 600;">🌐 Website</a>` : ''}
                        
                        ${contractor.url ? `<a href="${contractor.url}" style="flex: 1; padding: 8px 10px; background: #e8f0fe; color: #1e3a5f !important; text-align: center; text-decoration: none; border-radius: 6px; font-size: 0.8rem; font-weight: 600;">📋 Profile</a>` : ''}
                    </div>
                </div>
            </div>
        `;

        infoWindow.setContent(contentString);
        infoWindow.open(map, marker);
    }

    /**
     * Update the contractor count in the UI.
     */
    function updateContractorCount(totalCount, mappedCount) {
        const countEl = document.getElementById('contractor-count');
        if (countEl) {
            countEl.textContent = `${totalCount} contractor${totalCount !== 1 ? 's' : ''} found (${mappedCount} on map)`;
        }
    }

    /**
     * Setup search interaction to filter map markers.
     */
    function setupSearchInteraction() {
        // When the views exposed form is submitted, the page reloads with new data,
        // so markers will automatically update. But we can add visual feedback.
        const searchForm = document.querySelector('.views-exposed-form');
        if (searchForm) {
            searchForm.addEventListener('submit', function () {
                // Show loading state on map
                const mapContainer = document.getElementById('contractorMap') || document.getElementById('contractor-map');
                if (mapContainer) {
                    mapContainer.style.opacity = '0.6';
                    mapContainer.style.transition = 'opacity 0.3s';
                }
            });
        }
    }

    // Initialize when DOM is ready
    $(document).ready(function () {
        if (typeof google !== 'undefined' && google.maps && typeof window.initContractorMap === 'function') {
            window.initContractorMap();
        } else {
            let attempts = 0;
            const interval = setInterval(function () {
                attempts++;
                if (typeof google !== 'undefined' && google.maps && typeof window.initContractorMap === 'function') {
                    window.initContractorMap();
                    clearInterval(interval);
                } else if (attempts > 50) {
                    console.warn('IBEW Map: Google Maps API failed to load within 10 seconds.');
                    // Show fallback message
                    const mapContainer = document.getElementById('contractorMap') || document.getElementById('contractor-map');
                    if (mapContainer) {
                        mapContainer.innerHTML = '<div style="display: flex; align-items: center; justify-content: center; height: 100%; background: #f5f5f5; border-radius: 8px;"><p style="color: #666; text-align: center;">Map could not be loaded.<br>Please check your internet connection.</p></div>';
                    }
                    clearInterval(interval);
                }
            }, 200);
        }
    });

})(jQuery, Drupal, drupalSettings);
