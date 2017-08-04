<?php

/**
 * @file
 * Contains dynamic_banner.page.inc.
 *
 * Page callback for Dynamic banner entities.
 */

use Drupal\Core\Render\Element;
use Drupal\Component\Utility\Html;

/**
 * Prepares variables for Dynamic banner templates.
 *
 * Default template: dynamic_banner.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_dynamic_banner_slideshow(array &$variables) {
    $variables['configuration'] = $variables['elements']['#configuration'];
    $variables['plugin_id'] = $variables['elements']['#plugin_id'];
    $variables['label'] = !empty($variables['configuration']['label_display']) ? $variables['configuration']['label'] : '';

    foreach (Element::children($variables['elements']) as $key) {
        $banner = $variables['elements'][$key];
        // Remove #theme value to print the image without any wrappers
        $banner['image']['#theme'] = '';
        $variables['content'][$key] = $banner;
    }

    if (!empty($variables['elements']['#id'])) {
        $variables['attributes']['id'] = Html::getUniqueId('block-' . $variables['elements']['#id']);
    }

    if(!empty($variables['elements']['#classes'])) {
        $variables['attributes']['class'] = $variables['elements']['#classes'];
    }
}

/**
 * Prepares variables for Dynamic banner block templates.
 *
 * Default template: dynamic_banner_banner.html.twig.
 * @param array $variables .
 *
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_dynamic_banner_banner(array &$variables) {
    $dynamic_banner = $variables['elements']['#dynamic_banner'];

    foreach (Element::children($variables['elements']) as $key) {
        $variables['content'][$key] = $variables['elements'][$key];
    }

}
