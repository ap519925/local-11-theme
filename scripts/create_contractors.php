<?php

use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Term;

// Sample Data
$contractors = [
    [
        'title' => 'A M Electric',
        'phone' => '203-555-0101',
        'email' => 'info@amelectric.com',
        'website' => 'https://www.amelectric.com',
        'areas' => 'Wallingford, Meriden, Cheshire'
    ],
    [
        'title' => 'ADT Commercial',
        'phone' => '203-555-0102',
        'email' => 'contact@adt.com',
        'website' => 'https://www.adt.com',
        'areas' => 'New Haven, East Haven, West Haven'
    ],
    [
        'title' => 'All Electric Construction',
        'phone' => '203-555-0103',
        'email' => 'sales@allelectric.com',
        'website' => 'https://www.allelectric.com',
        'areas' => 'Hamden, North Haven'
    ],
    [
        'title' => 'Ducci Electrical',
        'phone' => '860-496-4800',
        'email' => 'info@ducci.com',
        'website' => 'https://www.ducci.com',
        'areas' => 'Torrington, Waterbury, Hartford'
    ],
    [
        'title' => 'E.J. Electric',
        'phone' => '203-555-0109',
        'email' => 'service@ejelectric.com',
        'website' => 'https://www.ejelectric.com',
        'areas' => 'Cheshire, Southington'
    ],
    [
        'title' => 'C. White Electric',
        'phone' => '203-555-0110',
        'email' => 'info@cwhite.com',
        'website' => 'https://www.cwhite.com',
        'areas' => 'West Haven, Milford, Orange'
    ],
    [
        'title' => 'A.D. Rizzo Electrical',
        'phone' => '203-555-0104',
        'email' => 'rizzo@example.com',
        'website' => 'https://www.adrizzo.com',
        'areas' => 'Danbury, Bethel, Ridgefield'
    ],
    [
        'title' => 'ES Boulos',
        'phone' => '203-265-3820',
        'email' => 'info@esboulos.com',
        'website' => 'https://www.esboulos.com',
        'areas' => 'Wallingford, Entire State'
    ]
];

// Content Type
$type = 'contractor_profile';

// Check if type exists
$node_type = \Drupal\node\Entity\NodeType::load($type);
if (!$node_type) {
    echo "Content type '$type' does not exist.\n";
    exit;
}

foreach ($contractors as $data) {
    // Check if node exists to avoid duplicates
    $query = \Drupal::entityQuery('node')
        ->condition('type', $type)
        ->condition('title', $data['title'])
        ->accessCheck(FALSE);
    $nids = $query->execute();

    if (!empty($nids)) {
        echo "Contractor '{$data['title']}' already exists.\n";
        continue;
    }

    $node = Node::create([
        'type' => $type,
        'title' => $data['title'],
        'status' => 1,
        'field_phone' => $data['phone'],
        'field_email' => $data['email'],
        'field_website' => [
            'uri' => $data['website'],
            'title' => 'Visit Website'
        ],
        'field_service_areas' => $data['areas'],
        // Add lorem ipsum body
        'body' => [
            'value' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Professional electrical services provided by ' . $data['title'] . '.',
            'format' => 'basic_html',
        ],
    ]);

    $node->save();
    echo "Created contractor: {$data['title']}\n";
}
