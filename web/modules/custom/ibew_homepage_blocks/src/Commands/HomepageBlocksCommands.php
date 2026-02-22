<?php

namespace Drupal\ibew_homepage_blocks\Commands;

use Drush\Commands\DrushCommands;
use Drupal\block_content\Entity\BlockContent;
use Drupal\block\Entity\Block;

/**
 * A Drush commandfile to setup homepage blocks.
 */
class HomepageBlocksCommands extends DrushCommands
{

    /**
     * Generates default CTA blocks for the homepage.
     *
     * @command ibew:setup-cta-blocks
     * @aliases iscb
     * @usage ibew:setup-cta-blocks
     *   Creates block_content entities for CTA items and places them.
     */
    public function setupCtaBlocks()
    {
        $blocks_data = [
            [
                'id' => 'cta_prospective_members',
                'label' => 'CTA: Prospective Members',
                'html' => '<div class="ibew-cta-card bg-white/10 backdrop-blur-sm border border-white/20 p-8 rounded-2xl hover:bg-white/20 transition-all duration-300">
            <h3 class="font-oswald text-2xl font-bold mb-3 text-white uppercase text-shadow-sm">Prospective Members</h3>
            <p class="text-white/90 mb-6 min-h-[60px] font-light">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
            <a href="/join" class="btn w-full py-3 fw-bold text-uppercase font-oswald tracking-wide shadow-lg hover:shadow-xl transition-all" style="background-color: #cc9c42; color: #1e3a5f; border: none;">Join</a>
        </div>',
                'weight' => 0,
            ],
            [
                'id' => 'cta_contractors',
                'label' => 'CTA: Contractors',
                'html' => '<div class="ibew-cta-card bg-white/10 backdrop-blur-sm border border-white/20 p-8 rounded-2xl hover:bg-white/20 transition-all duration-300">
            <h3 class="font-oswald text-2xl font-bold mb-3 text-white uppercase text-shadow-sm">Contractors</h3>
            <p class="text-white/90 mb-6 min-h-[60px] font-light">Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
            <a href="/contractors" class="btn w-full py-3 fw-bold text-uppercase font-oswald tracking-wide shadow-lg hover:shadow-xl transition-all" style="background-color: #465a8c; color: white; border: none;">Contractor Information</a>
        </div>',
                'weight' => 1,
            ],
            [
                'id' => 'cta_training',
                'label' => 'CTA: Training Opportunities',
                'html' => '<div class="ibew-cta-card bg-white/10 backdrop-blur-sm border border-white/20 p-8 rounded-2xl hover:bg-white/20 transition-all duration-300">
            <h3 class="font-oswald text-2xl font-bold mb-3 text-white uppercase text-shadow-sm">Training Opportunities</h3>
            <p class="text-white/90 mb-6 min-h-[60px] font-light">Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.</p>
            <a href="/training" class="btn w-full py-3 fw-bold text-uppercase font-oswald tracking-wide shadow-lg hover:shadow-xl transition-all" style="background-color: #9e373f; color: white; border: none;">IBEW Training</a>
        </div>',
                'weight' => 2,
            ],
        ];

        foreach ($blocks_data as $data) {
            if (!Block::load($data['id'])) {
                $block_content = BlockContent::create([
                    'info' => $data['label'],
                    'type' => 'basic',
                    'body' => [
                        'value' => $data['html'],
                        'format' => 'full_html',
                    ],
                ]);
                $block_content->save();

                $block = Block::create([
                    'id' => $data['id'],
                    'theme' => 'ibew_theme',
                    'region' => 'homepage_cta_cards',
                    'weight' => $data['weight'],
                    'plugin' => 'block_content:' . $block_content->uuid(),
                    'settings' => [
                        'id' => 'block_content:' . $block_content->uuid(),
                        'label' => $data['label'],
                        'label_display' => 0,
                        'status' => 1,
                    ],
                ]);
                $block->save();
                $this->logger()->success("Created and placed block: " . $data['label']);
            } else {
                $this->logger()->notice("Block already exists: " . $data['label']);
            }
        }

        // Clear basic caches
        \Drupal::service('cache.render')->invalidateAll();
        $this->logger()->success('Render cache cleared.');
    }

}
