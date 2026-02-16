<?php

namespace Drupal\ibew_homepage_blocks\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides an 'Hero Content' Block.
 *
 * @Block(
 *   id = "ibew_hero_content",
 *   admin_label = @Translation("IBEW Hero Content"),
 *   category = @Translation("IBEW Homepage"),
 * )
 */
class HeroContentBlock extends BlockBase
{

    /**
     * {@inheritdoc}
     */
    public function defaultConfiguration()
    {
        return [
            'eyebrow' => 'The New Haven Electricians',
            'title' => 'IBEW Local 90',
            'description' => 'Serving electricians across Greater New Haven from our Wallingford office. Business Manager: Sean W. Daly.',
            'link_1_title' => 'Member Re-Sign',
            'link_1_url' => '/content/book-1-re-sign-procedure',
            'link_2_title' => 'JoinIBEWCT.org',
            'link_2_url' => 'https://JoinIBEWCT.org',
            'link_3_title' => 'Email Local 90',
            'link_3_url' => 'mailto:info@ibewlocal90.org',
        ] + parent::defaultConfiguration();
    }

    /**
     * {@inheritdoc}
     */
    public function blockForm($form, FormStateInterface $form_state)
    {
        $config = $this->getConfiguration();

        $form['eyebrow'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Eyebrow Text'),
            '#default_value' => $config['eyebrow'],
        ];

        $form['title'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Main Title'),
            '#default_value' => $config['title'],
        ];

        $form['description'] = [
            '#type' => 'textarea',
            '#title' => $this->t('Description'),
            '#default_value' => $config['description'],
        ];

        $form['link_1_group'] = [
            '#type' => 'details',
            '#title' => $this->t('Primary Button'),
            '#open' => TRUE,
        ];
        $form['link_1_group']['link_1_title'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Title'),
            '#default_value' => $config['link_1_title'],
        ];
        $form['link_1_group']['link_1_url'] = [
            '#type' => 'textfield',
            '#title' => $this->t('URL'),
            '#default_value' => $config['link_1_url'],
        ];

        $form['link_2_group'] = [
            '#type' => 'details',
            '#title' => $this->t('Secondary Button 1'),
            '#open' => FALSE,
        ];
        $form['link_2_group']['link_2_title'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Title'),
            '#default_value' => $config['link_2_title'],
        ];
        $form['link_2_group']['link_2_url'] = [
            '#type' => 'textfield',
            '#title' => $this->t('URL'),
            '#default_value' => $config['link_2_url'],
        ];

        $form['link_3_group'] = [
            '#type' => 'details',
            '#title' => $this->t('Secondary Button 2'),
            '#open' => FALSE,
        ];
        $form['link_3_group']['link_3_title'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Title'),
            '#default_value' => $config['link_3_title'],
        ];
        $form['link_3_group']['link_3_url'] = [
            '#type' => 'textfield',
            '#title' => $this->t('URL'),
            '#default_value' => $config['link_3_url'],
        ];

        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function blockSubmit($form, FormStateInterface $form_state)
    {
        $this->configuration['eyebrow'] = $form_state->getValue('eyebrow');
        $this->configuration['title'] = $form_state->getValue('title');
        $this->configuration['description'] = $form_state->getValue('description');

        $link_1 = $form_state->getValue('link_1_group');
        $this->configuration['link_1_title'] = $link_1['link_1_title'];
        $this->configuration['link_1_url'] = $link_1['link_1_url'];

        $link_2 = $form_state->getValue('link_2_group');
        $this->configuration['link_2_title'] = $link_2['link_2_title'];
        $this->configuration['link_2_url'] = $link_2['link_2_url'];

        $link_3 = $form_state->getValue('link_3_group');
        $this->configuration['link_3_title'] = $link_3['link_3_title'];
        $this->configuration['link_3_url'] = $link_3['link_3_url'];
    }

    /**
     * {@inheritdoc}
     */
    public function build()
    {
        $config = $this->getConfiguration();

        return [
            '#theme' => 'ibew_hero_content',
            '#eyebrow' => $config['eyebrow'],
            '#title' => $config['title'],
            '#description' => $config['description'],
            '#links' => [
                [
                    'title' => $config['link_1_title'],
                    'url' => $config['link_1_url'],
                    'style' => 'primary',
                ],
                [
                    'title' => $config['link_2_title'],
                    'url' => $config['link_2_url'],
                    'style' => 'ghost',
                ],
                [
                    'title' => $config['link_3_title'],
                    'url' => $config['link_3_url'],
                    'style' => 'ghost',
                ],
            ],
            '#cache' => [
                'tags' => ['config:ibew_homepage_blocks.hero_content'],
                'contexts' => ['user'],
            ],
        ];
    }

}
