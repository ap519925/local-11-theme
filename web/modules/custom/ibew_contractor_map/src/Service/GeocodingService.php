<?php

namespace Drupal\ibew_contractor_map\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use GuzzleHttp\ClientInterface;

/**
 * Service to geocode addresses using Google Maps Geocoding API.
 */
class GeocodingService
{

    /**
     * The HTTP client.
     *
     * @var \GuzzleHttp\ClientInterface
     */
    protected $httpClient;

    /**
     * The config factory.
     *
     * @var \Drupal\Core\Config\ConfigFactoryInterface
     */
    protected $configFactory;

    /**
     * The logger.
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * GeocodingService constructor.
     *
     * @param \GuzzleHttp\ClientInterface $http_client
     *   The HTTP client.
     * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
     *   The config factory.
     * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
     *   The logger channel factory.
     */
    public function __construct(
        ClientInterface $http_client,
        ConfigFactoryInterface $config_factory,
        LoggerChannelFactoryInterface $logger_factory
    ) {
        $this->httpClient = $http_client;
        $this->configFactory = $config_factory;
        $this->logger = $logger_factory->get('ibew_contractor_map');
    }

    /**
     * Geocode an address string to latitude and longitude.
     *
     * @param string $address
     *   The full address string to geocode.
     *
     * @return array|null
     *   An array with 'lat' and 'lng' keys, or NULL if geocoding failed.
     */
    public function geocode(string $address): ?array
    {
        $config = $this->configFactory->get('ibew_contractor_map.settings');
        $api_key = $config->get('google_maps_api_key');

        if (empty($api_key)) {
            $this->logger->warning('Cannot geocode: Google Maps API key is not configured.');
            return NULL;
        }

        if (empty(trim($address))) {
            $this->logger->warning('Cannot geocode: Empty address provided.');
            return NULL;
        }

        try {
            $response = $this->httpClient->request('GET', 'https://maps.googleapis.com/maps/api/geocode/json', [
                'query' => [
                    'address' => $address,
                    'key' => $api_key,
                ],
                'timeout' => 10,
            ]);

            $data = json_decode($response->getBody()->getContents(), TRUE);

            if (!empty($data['status']) && $data['status'] === 'OK' && !empty($data['results'][0])) {
                $location = $data['results'][0]['geometry']['location'];
                $this->logger->info('Successfully geocoded address: @address -> @lat, @lng', [
                    '@address' => $address,
                    '@lat' => $location['lat'],
                    '@lng' => $location['lng'],
                ]);

                return [
                    'lat' => (float) $location['lat'],
                    'lng' => (float) $location['lng'],
                ];
            }

            $this->logger->warning('Geocoding failed for address: @address. Status: @status', [
                '@address' => $address,
                '@status' => $data['status'] ?? 'unknown',
            ]);

            return NULL;
        } catch (\Exception $e) {
            $this->logger->error('Geocoding error for address @address: @message', [
                '@address' => $address,
                '@message' => $e->getMessage(),
            ]);
            return NULL;
        }
    }

    /**
     * Build a full address string from individual components.
     *
     * @param string|null $street
     *   Street address.
     * @param string|null $city
     *   City name.
     * @param string|null $state
     *   State abbreviation or name.
     * @param string|null $zip
     *   ZIP/postal code.
     *
     * @return string
     *   The assembled address string.
     */
    public function buildAddressString(?string $street, ?string $city, ?string $state, ?string $zip): string
    {
        $parts = array_filter([$street, $city, $state, $zip]);
        return implode(', ', $parts);
    }

}
