<?php

namespace Drupal\ibew_contractor_map\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure IBEW Contractor Map settings.
 */
class ContractorMapSettingsForm extends ConfigFormBase
{

    /**
     * {@inheritdoc}
     */
    public function getFormId()
    {
        return 'ibew_contractor_map_settings';
    }

    /**
     * {@inheritdoc}
     */
    protected function getEditableConfigNames()
    {
        return ['ibew_contractor_map.settings'];
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $config = $this->config('ibew_contractor_map.settings');

        $form['api_settings'] = [
            '#type' => 'details',
            '#title' => $this->t('Google Maps API'),
            '#open' => TRUE,
        ];

        $form['api_settings']['google_maps_api_key'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Google Maps API Key'),
            '#default_value' => $config->get('google_maps_api_key'),
            '#description' => $this->t('Enter your Google Maps API key. Required for the contractor map and auto-geocoding. <strong>Note:</strong> If this is set via settings.local.php or settings.production.php, the override will take precedence.'),
            '#maxlength' => 128,
        ];

        $form['geocoding_settings'] = [
            '#type' => 'details',
            '#title' => $this->t('Auto-Geocoding'),
            '#open' => TRUE,
        ];

        $form['geocoding_settings']['auto_geocode'] = [
            '#type' => 'checkbox',
            '#title' => $this->t('Enable auto-geocoding'),
            '#default_value' => $config->get('auto_geocode') ?? TRUE,
            '#description' => $this->t('Automatically geocode contractor addresses to latitude/longitude when a contractor profile is saved. Requires a valid Google Maps API key with the Geocoding API enabled.'),
        ];

        $form['map_settings'] = [
            '#type' => 'details',
            '#title' => $this->t('Map Display Settings'),
            '#open' => TRUE,
        ];

        $form['map_settings']['default_zoom'] = [
            '#type' => 'number',
            '#title' => $this->t('Default zoom level'),
            '#default_value' => $config->get('default_zoom') ?? 9,
            '#min' => 1,
            '#max' => 20,
            '#description' => $this->t('Default map zoom level (1 = world, 20 = building level). Recommended: 9-12.'),
        ];

        $form['map_settings']['default_lat'] = [
            '#type' => 'number',
            '#title' => $this->t('Default center latitude'),
            '#default_value' => $config->get('default_lat') ?? 41.50,
            '#step' => 0.01,
            '#description' => $this->t('Default map center latitude (e.g., 41.50 for Connecticut).'),
        ];

        $form['map_settings']['default_lng'] = [
            '#type' => 'number',
            '#title' => $this->t('Default center longitude'),
            '#default_value' => $config->get('default_lng') ?? -72.80,
            '#step' => 0.01,
            '#description' => $this->t('Default map center longitude (e.g., -72.80 for Connecticut).'),
        ];

        $form['map_settings']['map_height'] = [
            '#type' => 'number',
            '#title' => $this->t('Map height (px)'),
            '#default_value' => $config->get('map_height') ?? 600,
            '#min' => 300,
            '#max' => 1200,
            '#description' => $this->t('Height of the map container in pixels.'),
        ];

        return parent::buildForm($form, $form_state);
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        $this->config('ibew_contractor_map.settings')
            ->set('google_maps_api_key', $form_state->getValue('google_maps_api_key'))
            ->set('auto_geocode', $form_state->getValue('auto_geocode'))
            ->set('default_zoom', $form_state->getValue('default_zoom'))
            ->set('default_lat', $form_state->getValue('default_lat'))
            ->set('default_lng', $form_state->getValue('default_lng'))
            ->set('map_height', $form_state->getValue('map_height'))
            ->save();

        parent::submitForm($form, $form_state);
    }

}
