<?php

/**
 * @file
 * Script to create contractor profiles from the provided data.
 *
 * Run with: drush php-script scripts/create_contractors.php
 *
 * Note: You will need to geocode the addresses to get lat/lng coordinates.
 * This script uses approximate coordinates for Connecticut/MA area contractors.
 */

// Contractor data from the provided list.
// Format: [name, street, city, state, zip, phone, website]
// Note: Some entries have missing data - marked as empty strings.
$contractors_data = [
    ['A&M Electric', '121 Turnpike Dr', 'Woodbury', 'CT', '06762', '475-222-3900', 'https://www.am.services/'],
    ['ADT Security Services, Inc.', '70 Technology Center', 'Shelton', 'CT', '06484', '203-951-3330', 'https://www.adt.com/'],
    ['All Electric Construction Co.', '80 Farwell St', 'West Haven', 'CT', '06516', '203-535-1244', 'https://www.allelectric.com/'],
    ['AM Rizzo Electric', '64 Triangle St', 'Danbury', 'CT', '06810', '203-731-3131', 'https://www.rizzoelectric.com/'],
    ['American Electrical Testing Co.', '25 Forbes Blvd Unit 1', 'Foxborough', 'MA', '02035', '781-821-0121', 'https://www.aetco.us/'],
    ['Anderson Electric', '55 Airport Rd Ste 101', 'Hartford', 'CT', '06114', '860-560-0091', 'https://www.andersonelec.com/'],
    ['C-White Electric LLC', '115 Walnut Tree Ln', 'Guilford', 'CT', '06437', '203-457-0642', 'http://www.cwhiteelectric.com/'],
    ['Cap Comm', '39 Olenick Rd', 'Lebanon', 'CT', '06249', '', ''],
    ['Cell-Comm Electrical Services, LLC', '12 Alstrum St', 'New Haven', 'CT', '06514', '203-776-2883', ''],
    ['Consolidated Electric, Inc.', '100 Wheeler St # F', 'New Haven', 'CT', '06512', '203-468-2111', 'https://www.CONELECTRIC.com'],
    ['Coughlin Electric', '100 Prescott St', 'Worcester', 'MA', '01605', '508-793-0300', 'https://www.coghlin.com'],
    ['Custom Electric, Inc', '52 Main St', 'Manchester', 'CT', '06042', '860-643-7110', 'https://www.customelectricusa.com'],
    ['Day & Zimmerman NPS', '1827 Freedom Rd Ste 101', 'Lancaster', 'PA', '17601', '717-481-5600', 'https://www.dayzim.com'],
    ['Dicin Electric', '156 Cross Rd', 'Waterford', 'CT', '06385', '860-442-0826', 'https://www.dicinelectric.com'],
    ['Ducci Electrical Contractors, Inc.', '72 Scott Swamp Rd', 'Farmington', 'CT', '06032', '860-489-9267', 'https://www.duccielectrical.com'],
    ['E. Pierpont Electrical Co.', '500 Washington Ave', 'North Haven', 'CT', '06473', '203-234-8024', ''],
    ['E.S. Boulos Company', '45 Bradley Dr', 'Westbrook', 'ME', '04092', '207-464-3706', 'https://www.esboulos.com'],
    ['EJ Electric', '53 N Plains Industrial Rd', 'Wallingford', 'CT', '06492', '203-626-9625', 'https://www.ej1899.com'],
    ['EPS Technologies', '37 Ozick Dr', 'Cheshire', 'CT', '06422', '203-679-0154', 'https://www.eps-technology.com'],
    ['Fairfield Electric', '250 Greenwich Ave', 'Greenwich', 'CT', '06902', '203-324-1532', 'https://www.fairfieldelectric.com'],
    ['Fiora Electrical Constr.', '16 Briarwood Cir', 'North Haven', 'CT', '06473', '203-694-4869', ''],
    ['Fusion Communications LLC', '94 Saint John St', 'North Haven', 'CT', '06473', '203-815-6670', ''],
    ['Genovese & Massaro, Inc.', '2466 State Street', 'New Haven', 'CT', '06517', '203-230-9055', ''],
    ['Grove Systems, Inc.', '572 Route 148', 'Killingworth', 'CT', '06419', '860-663-2555', 'https://www.grovesystemsinc.com'],
    ['High Voltage Maintenance', '29 Diana Ct', 'Cheshire', 'CT', '06410', '203-949-2650', 'https://www.hvmcorp.com'],
    ['Malangone Electric', '46 Depot Rd', 'Milford', 'CT', '06460', '203-877-7753', ''],
    ['MASS Electric Construction Co., Inc.', '400 Totten Pond Rd', 'Waltham', 'MA', '02451', '681-290-1001', 'https://www.masselect.com'],
    ['McPhee Electric. LTD LLC', '503 Main St', 'Farmington', 'CT', '06032', '860-677-9797', 'http://www.mcpheeusa.com/'],
    ['MJ Electric, LLC', 'PO Box 310', 'Bally', 'PA', '19555', '610-562-7570', 'https://www.mjelectric.com'],
    ['Net Services, LLC', '77 Industrial Park Rd', 'Vernon', 'CT', '06066', '860-563-3134', 'https://www.netservicesllc.com'],
    ['Paul Dinto Electrical Contracting', '121 Turnpike Dr', 'Woodbury', 'CT', '06762', '203-575-9473', 'https://www.dintoelectric.com'],
    ['Rosendin Electric', '', '', '', '', '', 'https://www.rosendin.com'],
    ['Siemens Republic ITS', '8 Progress Road', 'Billerica', 'MA', '01821', '978-262-9010', ''],
    ['Southern NE Electrical Testing', '3 Buel Street Unit 2', 'Wallingford', 'CT', '06492', '203-269-8778', 'https://www.electrical-testing.com'],
    ['SyNet Inc.', '', '', '', '', '', 'https://www.synet.com'],
    ['T.F. Electric, Inc.', '33 Townsend Ave', 'New Haven', 'CT', '06512', '203-467-9168', ''],
    ['Tek Electrix, LLC', '60 Industrial Dr', 'Waterbury', 'CT', '06489', '888-988-8813', 'https://www.natheatrix.com'],
    ['VSC Electric', '', '', '', '', '', 'https://www.vscelectric.com'],
    ['WC McBride Electric', '', '', '', '', '', 'https://www.wcmcbride.com'],
    ['William Roberts Electric', '', '', '', '', '', 'https://www.williamrobertselectric.com'],
    ['Woo@electric', '123 Railstone Dr', 'Southbury', 'CT', '06488', '203-375-1572', 'https://www.woodmere-electric.com'],
];

// Approximate coordinates for the addresses (in a real implementation, use a geocoding service).
// These are rough coordinates for Connecticut/MA area.
$coordinates = [
    'CT' => ['lat' => 41.6032, 'lng' => -73.0877], // Connecticut center
    'MA' => ['lat' => 42.4072, 'lng' => -71.3824], // Massachusetts center
    'ME' => ['lat' => 43.6591, 'lng' => -70.2568], // Maine center
    'PA' => ['lat' => 40.0150, 'lng' => -76.3054], // Pennsylvania center
];

// Get the node storage.
$node_storage = \Drupal::entityTypeManager()->getStorage('node');

// Count created nodes.
$created = 0;

foreach ($contractors_data as $data) {
    list($name, $street, $city, $state, $zip, $phone, $website) = $data;

    // Skip if no name.
    if (empty($name)) {
        continue;
    }

    // Check if contractor already exists.
    $existing = \Drupal::entityTypeManager()
        ->getStorage('node')
        ->getQuery()
        ->accessCheck(FALSE)
        ->condition('type', 'contractor_profile')
        ->condition('title', $name)
        ->execute();

    if (!empty($existing)) {
        echo "Skipping existing: $name\n";
        continue;
    }

    // Create the node.
    $node = $node_storage->create([
        'type' => 'contractor_profile',
        'title' => $name,
        'status' => 1,
    ]);

    // Set address fields.
    if (!empty($street)) {
        $node->set('field_street_address', $street);
    }
    if (!empty($city)) {
        $node->set('field_city', $city);
    }
    if (!empty($state)) {
        $node->set('field_state', $state);
    }
    if (!empty($zip)) {
        $node->set('field_zip', $zip);
    }
    if (!empty($phone)) {
        $node->set('field_phone', $phone);
    }
    if (!empty($website)) {
        $node->set('field_website', [
            'uri' => $website,
            'title' => 'Website',
        ]);
    }

    // Set approximate coordinates based on state.
    // Note: For accurate coordinates, you would need to use a geocoding API.
    if (!empty($state) && isset($coordinates[$state])) {
        // Add some random offset to simulate different locations within the state.
        $lat = $coordinates[$state]['lat'] + (mt_rand(-100, 100) / 1000);
        $lng = $coordinates[$state]['lng'] + (mt_rand(-100, 100) / 1000);
        $node->set('field_latitude', $lat);
        $node->set('field_longitude', $lng);
    }

    $node->save();
    $created++;
    echo "Created: $name\n";
}

echo "\nTotal contractors created: $created\n";
echo "Note: Coordinates are approximate. For accurate map placement, geocode the addresses.\n";
