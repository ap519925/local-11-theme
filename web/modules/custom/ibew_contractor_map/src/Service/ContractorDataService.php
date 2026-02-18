<?php

namespace Drupal\ibew_contractor_map\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\NodeInterface;
use Drupal\Component\Utility\Html;

/**
 * Service to fetch and sanitize contractor data for the map.
 */
class ContractorDataService
{

    /**
     * The entity type manager.
     *
     * @var \Drupal\Core\Entity\EntityTypeManagerInterface
     */
    protected $entityTypeManager;

    /**
     * The config factory.
     *
     * @var \Drupal\Core\Config\ConfigFactoryInterface
     */
    protected $configFactory;

    /**
     * ContractorDataService constructor.
     *
     * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
     *   The entity type manager.
     * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
     *   The config factory.
     */
    public function __construct(EntityTypeManagerInterface $entity_type_manager, ConfigFactoryInterface $config_factory)
    {
        $this->entityTypeManager = $entity_type_manager;
        $this->configFactory = $config_factory;
    }

    /**
     * Get contractor data for the map.
     *
     * @return array
     *   Array of sanitized contractor data including coordinates.
     */
    public function getContractorData()
    {
        $contractors = [];

        // Query for published contractor profiles.
        $query = $this->entityTypeManager
            ->getStorage('node')
            ->getQuery()
            ->accessCheck(FALSE)
            ->condition('type', 'contractor_profile')
            ->condition('status', NodeInterface::PUBLISHED)
            ->sort('title', 'ASC');

        $nids = $query->execute();

        if (!empty($nids)) {
            $nodes = $this->entityTypeManager
                ->getStorage('node')
                ->loadMultiple($nids);

            foreach ($nodes as $node) {
                /** @var \Drupal\node\NodeInterface $node */
                $lat = $node->get('field_latitude')->value;
                $lng = $node->get('field_longitude')->value;

                // Build address string.
                $street = $node->get('field_street_address')->value;
                $city = $node->get('field_city')->value;
                $state = $node->get('field_state')->value;
                $zip = $node->get('field_zip')->value;

                $address = '';
                if ($street || $city || $state || $zip) {
                    $address_parts = array_filter([$street, $city, $state, $zip]);
                    $address = implode(', ', $address_parts);
                }

                $phone = $node->get('field_phone')->value;
                $website = $node->get('field_website')->uri;

                // Get email if available.
                $email = '';
                if ($node->hasField('field_email') && !$node->get('field_email')->isEmpty()) {
                    $email = $node->get('field_email')->value;
                }

                // Get contact person if available.
                $contact_person = '';
                if ($node->hasField('field_contact_person') && !$node->get('field_contact_person')->isEmpty()) {
                    $contact_person = $node->get('field_contact_person')->value;
                }

                // Get specialties if available.
                $specialties = [];
                if ($node->hasField('field_specialties') && !$node->get('field_specialties')->isEmpty()) {
                    foreach ($node->get('field_specialties') as $item) {
                        if ($item->entity) {
                            $specialties[] = Html::escape($item->entity->label());
                        }
                    }
                }

                // Get service areas if available.
                $service_areas = [];
                if ($node->hasField('field_service_areas') && !$node->get('field_service_areas')->isEmpty()) {
                    foreach ($node->get('field_service_areas') as $item) {
                        $service_areas[] = Html::escape((string) $item->value);
                    }
                }

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

                // Build contractor data array.
                $contractor_data = [
                    'id' => $node->id(),
                    'title' => Html::escape((string) $node->getTitle()),
                    'lat' => $lat ? (float) $lat : NULL,
                    'lng' => $lng ? (float) $lng : NULL,
                    'address' => Html::escape((string) $address),
                    'phone' => Html::escape((string) $phone),
                    'email' => Html::escape((string) $email),
                    'contact_person' => Html::escape((string) $contact_person),
                    'website' => Html::escape((string) $website),
                    'image' => $image_url,
                    'specialties' => $specialties,
                    'service_areas' => $service_areas,
                    'url' => $node->toUrl()->toString(),
                ];

                // Include contractors even without coordinates (for the list view).
                // Map will only show ones with coordinates.
                $contractors[] = $contractor_data;
            }
        }

        return $contractors;
    }

    /**
     * Get map configuration settings.
     *
     * @return array
     *   Map configuration array.
     */
    public function getMapSettings()
    {
        $config = $this->configFactory->get('ibew_contractor_map.settings');
        return [
            'default_zoom' => $config->get('default_zoom') ?? 9,
            'default_lat' => $config->get('default_lat') ?? 41.50,
            'default_lng' => $config->get('default_lng') ?? -72.80,
            'map_height' => $config->get('map_height') ?? 600,
        ];
    }

}
