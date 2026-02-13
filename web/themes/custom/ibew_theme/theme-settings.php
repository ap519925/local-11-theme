<?php

/**
 * @file
 * Theme settings form for IBEW Theme.
 */

use Drupal\Core\Form\FormStateInterface;

/**
 * Implements hook_form_system_theme_settings_alter().
 */
function ibew_theme_form_system_theme_settings_alter(&$form, FormStateInterface $form_state)
{
    // Workaround for a core bug affecting admin themes.
    if (isset($form_id)) {
        return;
    }

    // Top Bar Settings
    $form['ibew_top_bar'] = [
        '#type' => 'details',
        '#title' => t('Top Utility Bar Settings'),
        '#open' => TRUE,
    ];

    $form['ibew_top_bar']['top_bar_show'] = [
        '#type' => 'checkbox',
        '#title' => t('Show Top Bar'),
        '#default_value' => theme_get_setting('top_bar_show') ?? TRUE,
    ];

    $form['ibew_top_bar']['top_bar_address'] = [
        '#type' => 'textfield',
        '#title' => t('Address Text'),
        '#default_value' => theme_get_setting('top_bar_address') ?? '2 N Plains Industrial Rd, Wallingford, CT 06492',
    ];

    $form['ibew_top_bar']['top_bar_phone'] = [
        '#type' => 'textfield',
        '#title' => t('Phone Number'),
        '#default_value' => theme_get_setting('top_bar_phone') ?? '1-800-562-2590',
    ];

    $form['ibew_top_bar']['top_bar_email'] = [
        '#type' => 'textfield',
        '#title' => t('Email Address'),
        '#default_value' => theme_get_setting('top_bar_email') ?? 'info@ibewlocal90.org',
    ];

    $form['ibew_top_bar']['top_bar_join_link'] = [
        '#type' => 'textfield',
        '#title' => t('Join URL'),
        '#description' => t('URL for the "JoinIBEWCT.org" link'),
        '#default_value' => theme_get_setting('top_bar_join_link') ?? 'https://JoinIBEWCT.org',
    ];

    $form['ibew_top_bar']['top_bar_join_text'] = [
        '#type' => 'textfield',
        '#title' => t('Join Link Text'),
        '#default_value' => theme_get_setting('top_bar_join_text') ?? 'JoinIBEWCT.org',
    ];

    // Social Media Links (Header specific)
    $form['ibew_social'] = [
        '#type' => 'details',
        '#title' => t('Social Media Links'),
        '#open' => TRUE,
    ];

    $form['ibew_social']['social_facebook'] = [
        '#type' => 'url',
        '#title' => t('Facebook URL'),
        '#default_value' => theme_get_setting('social_facebook'),
    ];

    $form['ibew_social']['social_twitter'] = [
        '#type' => 'url',
        '#title' => t('Twitter / X URL'),
        '#default_value' => theme_get_setting('social_twitter'),
    ];

    $form['ibew_social']['social_linkedin'] = [
        '#type' => 'url',
        '#title' => t('LinkedIn URL'),
        '#default_value' => theme_get_setting('social_linkedin'),
    ];

    $form['ibew_social']['social_instagram'] = [
        '#type' => 'url',
        '#title' => t('Instagram URL'),
        '#default_value' => theme_get_setting('social_instagram'),
    ];

    // Hero Background Settings
    $form['ibew_hero'] = [
        '#type' => 'details',
        '#title' => t('Hero Background Settings'),
        '#description' => t('Configure the homepage hero section background. You can use either the image slider or a video background.'),
        '#open' => TRUE,
    ];

    $form['ibew_hero']['hero_video_enabled'] = [
        '#type' => 'checkbox',
        '#title' => t('Enable Video Background'),
        '#description' => t('When enabled, a video will be used as the hero background instead of the image slider.'),
        '#default_value' => theme_get_setting('hero_video_enabled') ?? FALSE,
    ];

    $form['ibew_hero']['hero_video_source'] = [
        '#type' => 'radios',
        '#title' => t('Video Source'),
        '#options' => [
            'url' => t('External URL (YouTube, Vimeo, or direct MP4 link)'),
            'upload' => t('Upload MP4 file'),
        ],
        '#default_value' => theme_get_setting('hero_video_source') ?? 'url',
        '#states' => [
            'visible' => [
                ':input[name="hero_video_enabled"]' => ['checked' => TRUE],
            ],
        ],
    ];

    $form['ibew_hero']['hero_video_url'] = [
        '#type' => 'textfield',
        '#title' => t('Video URL'),
        '#description' => t('Enter a YouTube URL (e.g. https://www.youtube.com/watch?v=XXXXX), Vimeo URL, or a direct link to an MP4 file. YouTube and Vimeo videos will be embedded as backgrounds.'),
        '#default_value' => theme_get_setting('hero_video_url') ?? '',
        '#maxlength' => 512,
        '#states' => [
            'visible' => [
                ':input[name="hero_video_enabled"]' => ['checked' => TRUE],
                ':input[name="hero_video_source"]' => ['value' => 'url'],
            ],
        ],
    ];

    // Managed file upload for MP4
    $form['ibew_hero']['hero_video_upload'] = [
        '#type' => 'managed_file',
        '#title' => t('Upload Video File'),
        '#description' => t('Upload an MP4 video file (recommended: 1080p, under 30MB for performance). The video will loop automatically and play muted.'),
        '#upload_location' => 'public://hero-videos/',
        '#upload_validators' => [
            'file_validate_extensions' => ['mp4 webm'],
            'file_validate_size' => [30 * 1024 * 1024],
        ],
        '#default_value' => theme_get_setting('hero_video_upload') ?? '',
        '#states' => [
            'visible' => [
                ':input[name="hero_video_enabled"]' => ['checked' => TRUE],
                ':input[name="hero_video_source"]' => ['value' => 'upload'],
            ],
        ],
    ];

    $form['ibew_hero']['hero_video_overlay_opacity'] = [
        '#type' => 'number',
        '#title' => t('Video Overlay Darkness (%)'),
        '#description' => t('How dark the overlay on top of the video should be. 0 = no overlay, 100 = fully black. Default: 50'),
        '#min' => 0,
        '#max' => 100,
        '#step' => 5,
        '#default_value' => theme_get_setting('hero_video_overlay_opacity') ?? 50,
        '#states' => [
            'visible' => [
                ':input[name="hero_video_enabled"]' => ['checked' => TRUE],
            ],
        ],
    ];

    $form['ibew_hero']['hero_video_poster'] = [
        '#type' => 'textfield',
        '#title' => t('Fallback Poster Image URL'),
        '#description' => t('URL of an image to show while the video loads, or on mobile devices where autoplay may be blocked. Leave blank to use the default hero slider images.'),
        '#default_value' => theme_get_setting('hero_video_poster') ?? '',
        '#maxlength' => 512,
        '#states' => [
            'visible' => [
                ':input[name="hero_video_enabled"]' => ['checked' => TRUE],
            ],
        ],
    ];

    // Add a submit handler to make uploaded files permanent
    $form['#submit'][] = 'ibew_theme_settings_submit';

    // Color Scheme Settings
    $form['ibew_colors'] = [
        '#type' => 'details',
        '#title' => t('Color Scheme Settings'),
        '#description' => t('Customize the theme colors for Light and Dark modes.'),
        '#open' => TRUE,
    ];

    // --- Light Mode Colors ---
    $form['ibew_colors']['light_mode'] = [
        '#type' => 'details',
        '#title' => t('Light Mode Colors'),
        '#open' => FALSE,
    ];

    $form['ibew_colors']['light_mode']['ibew_light_primary'] = [
        '#type' => 'color',
        '#title' => t('Primary Color'),
        '#default_value' => theme_get_setting('ibew_light_primary') ?? '#1e293b',
        '#description' => t('Default: #1e293b'),
    ];

    $form['ibew_colors']['light_mode']['ibew_light_secondary'] = [
        '#type' => 'color',
        '#title' => t('Secondary Color'),
        '#default_value' => theme_get_setting('ibew_light_secondary') ?? '#3b82f6',
        '#description' => t('Default: #3b82f6'),
    ];

    $form['ibew_colors']['light_mode']['ibew_light_accent'] = [
        '#type' => 'color',
        '#title' => t('Accent Color'),
        '#default_value' => theme_get_setting('ibew_light_accent') ?? '#f59e0b',
        '#description' => t('Default: #f59e0b'),
    ];

    $form['ibew_colors']['light_mode']['ibew_light_bg'] = [
        '#type' => 'color',
        '#title' => t('Background Color'),
        '#default_value' => theme_get_setting('ibew_light_bg') ?? '#ffffff',
        '#description' => t('Default: #ffffff'),
    ];

    $form['ibew_colors']['light_mode']['ibew_light_card_bg'] = [
        '#type' => 'color',
        '#title' => t('Card Background Color'),
        '#default_value' => theme_get_setting('ibew_light_card_bg') ?? '#f3f4f6',
        '#description' => t('Default: #f3f4f6'),
    ];

    $form['ibew_colors']['light_mode']['ibew_light_text'] = [
        '#type' => 'color',
        '#title' => t('Text Color'),
        '#default_value' => theme_get_setting('ibew_light_text') ?? '#1f2937',
        '#description' => t('Default: #1f2937'),
    ];

    // --- Dark Mode Colors ---
    $form['ibew_colors']['dark_mode'] = [
        '#type' => 'details',
        '#title' => t('Dark Mode Colors'),
        '#open' => TRUE,
    ];

    $form['ibew_colors']['dark_mode']['ibew_dark_primary'] = [
        '#type' => 'color',
        '#title' => t('Primary Color'),
        '#default_value' => theme_get_setting('ibew_dark_primary') ?? '#1e293b',
        '#description' => t('Default: #1e293b'),
    ];

    $form['ibew_colors']['dark_mode']['ibew_dark_secondary'] = [
        '#type' => 'color',
        '#title' => t('Secondary Color'),
        '#default_value' => theme_get_setting('ibew_dark_secondary') ?? '#3b82f6',
        '#description' => t('Default: #3b82f6'),
    ];

    $form['ibew_colors']['dark_mode']['ibew_dark_accent'] = [
        '#type' => 'color',
        '#title' => t('Accent Color'),
        '#default_value' => theme_get_setting('ibew_dark_accent') ?? '#f59e0b',
        '#description' => t('Default: #f59e0b'),
    ];

    $form['ibew_colors']['dark_mode']['ibew_dark_bg'] = [
        '#type' => 'color',
        '#title' => t('Background Color'),
        '#default_value' => theme_get_setting('ibew_dark_bg') ?? '#1f2937',
        '#description' => t('Default: #1f2937'),
    ];

    $form['ibew_colors']['dark_mode']['ibew_dark_card_bg'] = [
        '#type' => 'color',
        '#title' => t('Card Background Color'),
        '#default_value' => theme_get_setting('ibew_dark_card_bg') ?? '#3e4c63',
        '#description' => t('Default: #3e4c63. Used for news cards, event cards, etc.'),
    ];

    $form['ibew_colors']['dark_mode']['ibew_dark_text'] = [
        '#type' => 'color',
        '#title' => t('Text Color'),
        '#default_value' => theme_get_setting('ibew_dark_text') ?? '#f3f4f6',
        '#description' => t('Default: #f3f4f6'),
    ];

    // --- Image Preview for Logo & Favicon ---

    // Logo Preview
    $logo_url = theme_get_setting('logo.url');
    // If 'logo.use_default' is unchecked and a path is provided, logo.url typically reflects that.
    // However, in the settings form context, sometimes we need to check specific form values if not saved yet, 
    // but reading the theme setting is the reliable way to show "Current Legacy".

    if ($logo_url) {
        $form['logo']['logo_preview'] = [
            '#type' => 'item',
            '#title' => t('Logo Preview'),
            '#markup' => '<div class="logo-preview" style="background: #ccc; padding: 5px; display: inline-block; border-radius: 4px; margin-top: 10px;"><img src="' . $logo_url . '" alt="Logo Preview" style="max-height: 40px; height: auto;" /></div>',
            '#weight' => -10, // Show above upload/path settings or near top of section
        ];
    }

    // Favicon Preview
    $favicon_url = theme_get_setting('favicon.url');
    if ($favicon_url) {
        $form['favicon']['favicon_preview'] = [
            '#type' => 'item',
            '#title' => t('Favicon Preview'),
            '#markup' => '<div class="favicon-preview" style="margin-top: 10px;"><img src="' . $favicon_url . '" alt="Favicon Preview" style="max-height: 16px; width: auto; border: 1px solid #ddd;" /></div>',
            '#weight' => -10,
        ];
    }
}

/**
 * Custom submit handler for theme settings.
 *
 * Makes uploaded hero video files permanent so they aren't deleted.
 */
function ibew_theme_settings_submit($form, FormStateInterface $form_state)
{
    $fid = $form_state->getValue('hero_video_upload');
    if (!empty($fid)) {
        // $fid may be an array with [0 => fid]
        if (is_array($fid)) {
            $fid = reset($fid);
        }
        if ($fid) {
            $file = \Drupal\file\Entity\File::load($fid);
            if ($file && $file->isTemporary()) {
                $file->setPermanent();
                $file->save();
                // Register file usage so it won't be cleaned up.
                \Drupal::service('file.usage')->add($file, 'ibew_theme', 'theme', 1);
            }
        }
    }
}
