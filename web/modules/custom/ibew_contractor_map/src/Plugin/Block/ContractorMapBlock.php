<?php

namespace Drupal\ibew_contractor_map\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\node\NodeInterface;

/**
 * Provides a contractor map block.
 *
 * @Block(
 *   id = "contractor_map_block",
 *   admin_label = @Translation("Contractor Map"),
 *   category = @Translation("IBEW")
 * )
 */
class ContractorMapBlock extends BlockBase
{

    /**
     * {@inheritdoc}
     */
    public function build()
    {
        $contractors = $this->getContractorData();

        return [
            '#theme' => 'contractor_map_container',
            '#contractors' => $contractors,
            '#map_id' => 'contractor-map-' . $this->getPluginId(),
            '#attached' => [
                'library' => [
                    'ibew_contractor_map/contractor-map',
                ],
                'drupalSettings' => [
                    'ibewContractorMap' => [
                        'contractors' => $contractors,
                    ],
                ],
            ],
        ];
    }

    /**
     * Get contractor data for the map.
     *
     * @return array
     *   Array of contractor data including coordinates.
     */
    protected function getContractorData()
    {
        $contractors = [];

        // Query for published contractor profiles with coordinates.
        $query = \Drupal::entityTypeManager()
            ->getStorage('node')
            ->getQuery()
            ->accessCheck(FALSE)
            ->condition('type', 'contractor_profile')
            ->condition('status', NodeInterface::PUBLISHED);

        $nids = $query->execute();

        if (!empty($nids)) {
            $nodes = \Drupal::entityTypeManager()
                ->getStorage('node')
                ->loadMultiple($nids);

            foreach ($nodes as $node) {
                // Get coordinate values.
                $lat = $node->get('field_latitude')->value;
                $lng = $node->get('field_longitude')->value;

                // Only add contractors with coordinates.
                if ($lat && $lng) {
                    $address = '';
                    $street = $node->get('field_street_address')->value;
                    $city = $node->get('field_city')->value;
                    $state = $node->get('field_state')->value;
                    $zip = $node->get('field_zip')->value;

                    if ($street || $city || $state || $zip) {
                        $address_parts = array_filter([$street, $city, $state, $zip]);
                        $address = implode(', ', $address_parts);
                    }

                    $phone = $node->get('field_phone')->value;
                    $website = $node->get('field_website')->uri;

                    // Get image URL if available.
                    $image_url = '';
                    $image_field = $node->get('field_image');
                    if (!$image_field->isEmpty()) {
                        $image_item = $image_field->first();
                        if ($image_item) {
                            $image_entity = $image_item->entity;
                            if ($image_entity) {
                                $image_url = $image_entity->createFileUrl();
                            }
                        }
                    }

                    // Build popup content.
                    $popup_content = '<h4>' . $node->getTitle() . '</h4>';
                    if ($address) {
                        $popup_content .= '<div class="popup-address">' . $address . '</div>';
                    }
                    if ($phone) {
                        $popup_content .= '<div class="popup-phone"><a href="tel:' . $phone . '">' . $phone . '</a></div>';
                    }
                    if ($website) {
                        $popup_content .= '<div class="popup-link"><a href="' . $website . '" target="_blank">Visit Website</a></div>';
                    }

                    $contractors[] = [
                        'id' => $node->id(),
                        'title' => $node->getTitle(),
                        'lat' => (float) $lat,
                        'lng' => (float) $lng,
                        'address' => $address,
                        'phone' => $phone,
                        'website' => $website,
                        'image' => $image_url,
                        'popupContent' => $popup_content,
                    ];
                }
            }
        }

        return $contractors;
    }

}
