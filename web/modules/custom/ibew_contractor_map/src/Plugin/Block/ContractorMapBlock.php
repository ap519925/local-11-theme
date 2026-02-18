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
        $contractors = $this->contractorDataService->getContractorData();
        $map_settings = $this->contractorDataService->getMapSettings();

        return [
            '#theme' => 'contractor_map_container',
            '#contractors' => $contractors,
            '#map_id' => 'contractor-map-' . $this->getPluginId(),
            '#map_height' => $map_settings['map_height'],
            '#attached' => [
                'library' => [
                    'ibew_contractor_map/contractor-map',
                ],
                'drupalSettings' => [
                    'ibewContractorMap' => [
                        'contractors' => $contractors,
                        'mapSettings' => $map_settings,
                    ],
                ],
            ],
            '#cache' => [
                'tags' => ['node_list:contractor_profile'],
            ],
        ];
    }

}
