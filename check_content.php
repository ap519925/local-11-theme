<?php
// Check content of block 32
$block_content = \Drupal::entityTypeManager()->getStorage('block_content')->load(32);
if ($block_content) {
    echo "Block Content ID: " . $block_content->id() . PHP_EOL;
    echo "Bundle: " . $block_content->bundle() . PHP_EOL;
    echo "Label: " . $block_content->label() . PHP_EOL;

    if ($block_content->hasField('field_display_title')) {
        echo "Title: " . $block_content->get('field_display_title')->getString() . PHP_EOL;
    }
    if ($block_content->hasField('field_hero_description')) {
        echo "Desc: " . $block_content->get('field_hero_description')->getString() . PHP_EOL;
    }
    if ($block_content->hasField('field_hero_actions')) {
        echo "Actions Count: " . $block_content->get('field_hero_actions')->count() . PHP_EOL;
    }
} else {
    echo "Block content 32 not found." . PHP_EOL;
}
