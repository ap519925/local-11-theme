<?php

namespace Drupal\ibew_homepage_blocks\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Controller for news JSON endpoint.
 */
class NewsJsonController extends ControllerBase
{

    /**
     * Returns news articles as JSON.
     */
    public function json()
    {
        $news_items = [];

        // Load all news articles
        $query = \Drupal::entityQuery('node')
            ->condition('type', 'news_article')
            ->condition('status', 1)
            ->sort('created', 'DESC')
            ->accessCheck(TRUE);

        $nids = $query->execute();

        if (!empty($nids)) {
            $nodes = \Drupal::entityTypeManager()->getStorage('node')->loadMultiple($nids);

            foreach ($nodes as $node) {
                /** @var \Drupal\node\NodeInterface $node */
                $item = [
                    'title' => $node->getTitle(),
                    'url' => $node->toUrl()->toString(),
                    'date' => date('c', $node->getCreatedTime()),
                    'summary' => '',
                    'image' => '',
                ];

                // Get summary from body field
                if ($node->hasField('body') && !$node->get('body')->isEmpty()) {
                    $body = $node->get('body')->first();
                    $summary = $body->summary ?: strip_tags($body->value);
                    $item['summary'] = \Drupal\Component\Utility\Unicode::truncate($summary, 150, TRUE, TRUE);
                }

                // Get published by
                if ($node->hasField('field_published_by') && !$node->get('field_published_by')->isEmpty()) {
                    $item['published_by'] = $node->get('field_published_by')->value;
                }

                // Get image
                if ($node->hasField('field_image') && !$node->get('field_image')->isEmpty()) {
                    $image = $node->get('field_image')->first();
                    if ($image && $image->entity) {
                        $item['image'] = \Drupal::service('file_url_generator')->generateAbsoluteString($image->entity->getFileUri());
                    }
                }

                $news_items[] = $item;
            }
        }

        return new JsonResponse($news_items);
    }

}
