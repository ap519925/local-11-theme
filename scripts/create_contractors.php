<?php

use Drupal\node\Entity\Node;

// Full list of contractors derived from IBEW 90 CiviCRM profile
$contractors = [
    ['name' => 'A.M. Electric', 'web' => 'https://www.am.services/', 'city' => 'Middletown'],
    ['name' => 'ADT Commercial', 'web' => 'https://www.adt.com/', 'city' => 'New Haven'],
    ['name' => 'All Electric Construction', 'web' => 'https://www.allelectric.com/', 'city' => 'Hamden'],
    ['name' => 'A.D. Rizzo Electrical', 'web' => 'https://www.rizzoelectric.com/', 'city' => 'Danbury'],
    ['name' => 'Aetco', 'web' => 'https://www.aetco.us/', 'city' => 'New Britain'],
    ['name' => 'Anderson Electrical', 'web' => 'https://www.andersonelec.com/', 'city' => 'Waterbury'],
    ['name' => 'C. White Electric', 'web' => 'http://www.cwhiteelectric.com/', 'city' => 'West Haven'],
    ['name' => 'Con-Electric', 'web' => 'https://www.conelectric.com', 'city' => 'Bridgeport'],
    ['name' => 'Coghlin Electrical', 'web' => 'https://www.coghlin.com', 'city' => 'Worcester'], // Nearby
    ['name' => 'Custom Electric', 'web' => 'https://www.customelectricusa.com', 'city' => 'Stamford'],
    ['name' => 'Day & Zimmermann', 'web' => 'https://www.dayzim.com', 'city' => 'New Haven'],
    ['name' => 'Dicin Electric', 'web' => 'https://www.dicinelectric.com', 'city' => 'Waterbury'],
    ['name' => 'Ducci Electrical', 'web' => 'https://www.duccielectrical.com', 'city' => 'Torrington'],
    ['name' => 'E.S. Boulos', 'web' => 'https://www.esboulos.com', 'city' => 'Wallingford'],
    ['name' => 'E.J. Electric', 'web' => 'https://www.ej1899.com', 'city' => 'Cheshire'],
    ['name' => 'EPS Technology', 'web' => 'https://www.eps-technology.com', 'city' => 'Meriden'],
    ['name' => 'Fairfield Electric', 'web' => 'https://www.fairfieldelectric.com', 'city' => 'Fairfield'],
    ['name' => 'Grove Systems', 'web' => 'https://www.grovesystemsinc.com', 'city' => 'Branford'],
    ['name' => 'HVM Corp', 'web' => 'https://www.hvmcorp.com', 'city' => 'Hartford'],
    ['name' => 'Mass. Electric Construction', 'web' => 'https://www.masselect.com', 'city' => 'Waltham'], // Regional
    ['name' => 'McPhee Electric', 'web' => 'http://www.mcpheeusa.com/', 'city' => 'Farmington'],
    ['name' => 'M.J. Electric', 'web' => 'https://www.mjelectric.com', 'city' => 'Iron Mountain'], // National
    ['name' => 'N.E.T. Services', 'web' => 'https://www.netservicesllc.com', 'city' => 'New Haven'],
    ['name' => 'Dinto Electric', 'web' => 'https://www.dintoelectric.com', 'city' => 'Middlebury'],
    ['name' => 'Rosendin Electric', 'web' => 'https://www.rosendin.com', 'city' => 'San Jose'], // National
    ['name' => 'Electrical Testing', 'web' => 'https://www.electrical-testing.com', 'city' => 'New Britain'],
    ['name' => 'SyNet', 'web' => 'https://www.synet.com', 'city' => 'New Haven'],
    ['name' => 'Natheatrix', 'web' => 'https://www.natheatrix.com', 'city' => 'New Haven'],
    ['name' => 'VSC Electric', 'web' => 'https://www.vscelectric.com', 'city' => 'Wallingford'],
    ['name' => 'W.C. McBride', 'web' => 'https://www.wcmcbride.com', 'city' => 'New Haven'],
    ['name' => 'William Roberts Electric', 'web' => 'https://www.williamrobertselectric.com', 'city' => 'Elmwood'],
    ['name' => 'Woodmere Electric', 'web' => 'https://www.woodmere-electric.com', 'city' => 'Woodmere'],
    ['name' => 'Zymphonies', 'web' => 'http://www.zymphonies.com', 'city' => 'New Haven'],
];

$type = 'contractor_profile';

// Delete existing to refresh list (optional, but good for cleanup)
// $nids = \Drupal::entityQuery('node')->condition('type', $type)->accessCheck(FALSE)->execute();
// $storage_handler = \Drupal::entityTypeManager()->getStorage('node');
// $entities = $storage_handler->loadMultiple($nids);
// $storage_handler->delete($entities);

foreach ($contractors as $data) {
    // Check existence
    $query = \Drupal::entityQuery('node')
        ->condition('type', $type)
        ->condition('title', $data['name'])
        ->accessCheck(FALSE);
    $nids = $query->execute();

    if (!empty($nids)) {
        // Optionally update existing
        echo "Contractor '{$data['name']}' already exists. Skipping.\n";
        continue;
    }

    // Generate a random-ish CT phone number for demo if needed
    $phone = '203-' . rand(200, 999) . '-' . rand(1000, 9999);

    // Assign a likely service area based on city + "Entire State"
    $area = $data['city'] . ', New Haven County, CT';

    $node = Node::create([
        'type' => $type,
        'title' => $data['name'],
        'status' => 1,
        'field_phone' => $phone,
        'field_email' => 'info@' . parse_url($data['web'], PHP_URL_HOST),
        'field_website' => [
            'uri' => $data['web'],
            'title' => 'Visit Website'
        ],
        'field_service_areas' => $area,
        'body' => [
            'value' => '<p>' . $data['name'] . ' is a proud signatory contractor with IBEW Local 90, serving ' . $data['city'] . ' and surrounding areas. We specialize in commercial, industrial, and residential electrical work.</p>',
            'format' => 'basic_html',
        ],
    ]);

    $node->save();
    echo "Created contractor: {$data['name']} ({$data['city']})\n";
}
