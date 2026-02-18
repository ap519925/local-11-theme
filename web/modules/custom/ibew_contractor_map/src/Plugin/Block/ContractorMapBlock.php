<?php

namespace Drupal\ibew_contractor_map\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\ibew_contractor_map\Service\ContractorDataService;

/**
 * Provides a contractor map block.
 *
 * @Block(
 *   id = "contractor_map_block",
 *   admin_label = @Translation("Contractor Map"),
 *   category = @Translation("IBEW")
 * )
 */
class ContractorMapBlock extends BlockBase implements ContainerFactoryPluginInterface
{

    /**
     * The contractor data service.
     *
     * @var \Drupal\ibew_contractor_map\Service\ContractorDataService
     */
    protected $contractorDataService;

    /**
     * Constructs a new ContractorMapBlock.
     *
     * @param array $configuration
     *   A configuration array containing information about the plugin instance.
     * @param string $plugin_id
     *   The plugin_id for the plugin instance.
     * @param mixed $plugin_definition
     *   The plugin implementation definition.
     * @param \Drupal\ibew_contractor_map\Service\ContractorDataService $contractor_data_service
     *   The contractor data service.
     */
    public function __construct(array $configuration, $plugin_id, $plugin_definition, ContractorDataService $contractor_data_service)
    {
        parent::__construct($configuration, $plugin_id, $plugin_definition);
        $this->contractorDataService = $contractor_data_service;
    }

    /**
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition)
    {
        return new static(
            $configuration,
            $plugin_id,
            $plugin_definition,
            $container->get('ibew_contractor_map.contractor_data_service')
        );
    }

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
        return $this->contractorDataService->getContractorData();
    }

}
