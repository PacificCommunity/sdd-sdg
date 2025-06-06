<?php

namespace Drupal\sdd_sdg\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Form\FormStateInterface;
// use Drupal\Core\Link;
// use Drupal\Core\Url;

/**
 * Highlight and link to SDG Dashboard
 *
 * @Block(
 *   id = "topic_sdg_indicators",
 *   admin_label = @Translation("Topic SDG Indicators")
 * )
 */
class TopicSdgIndicators extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    
    $config = $this->getConfiguration();

    $sdgs = [];
    if (!empty($config['topic_sdg_indicators'])) {
      $sdgs = $config['topic_sdg_indicators'];
    }
    
    $terms =\Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree('sdg_indicators', 0, 1, true);
    
    $items = [];
    
    $target_on = '<a class="sdg-tile sdg-{{weight}}" href="{{uri}}" title="{{label}}" data-toggle="tooltip" target="_blank">{{label}}</a>';
    $target_off = '<span class="sdg-tile sdg-{{weight}}">{{label}}</span>';

    $lang = \Drupal::languageManager()->getCurrentLanguage()->getId();
      
    foreach ($terms as $term) {
      $w = $term->get('weight')->value;

      if ($term->hasTranslation($lang)) {
        $term = $term->getTranslation($lang);
      }

      $items[] = [
        '#type' => 'inline_template',
        '#template' => !empty($config['topic_sdg_indicators'][$w+1])?$target_on:$target_off,
        '#context' => [
          'weight' => $w+1,
          'label' => $term->get('name')->value,
          'uri' => $term->get('field_dashboard_link')->getValue()[0][ 'uri']
        ]
      ];
    }
    
    // Display Logo
    if (array_key_exists('topic_sdg_logo', $config)) {
      $items[] = [
        '#type' => 'inline_template',
        '#template' => '<a href="https://pacificdata.org/dashboard/17-goals-transform-pacific" target="_blank" title="Visit the SDG Dashboard" data-toggle="tooltip" class="sdg-logo">{{label}}</a>',
        '#context' => [
          'label' => $this->t('Sustainable Development Goals')
        ]
      ];
    }

    $classes = [ 'topic-sdg-indicators' ];
    if (array_key_exists('topic_sdg_double', $config) && $config["topic_sdg_double"]) {
      $classes[] = 'double-trouble';
    }

    $build = [
      '#type' => 'container',
      '#attributes' => ['class' => $classes],
      'items' => $items,
      '#attached' => [
        'library' => [
          'sdd_sdg/tiles',
        ],
      ],
    ];
    
    return $build;
  }
  
  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    $config = $this->getConfiguration();

    $form['topic_sdg_logo'] = array(
      '#type' => 'checkbox',
      '#title' => t('Show SDG logo (link to Dashboard home page)'),
      '#return_value' => 1,
      '#default_value' => empty($config['topic_sdg_logo']) ? 0 : 1
    );

    $form['topic_sdg_double'] = array(
      '#type' => 'checkbox',
      '#title' => t('Double size icons'),
      '#return_value' => 1,
      '#default_value' => empty($config['topic_sdg_double']) ? 0 : 1
    );
    
    $terms =\Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree('sdg_indicators');
    
    $options = [];
      
    foreach ($terms as $term) {
      $options[ $term->weight + 1 ] = $term->name;
    }

    $form['topic_sdg_indicators'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Related SDGs indicators'),
      '#options' => $options,
      // '#description' => $this->t('Yeah I dont need this'),
      '#default_value' => isset($config['topic_sdg_indicators']) ? $config['topic_sdg_indicators'] : '',
    ];

    return $form;
  }
  
  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $values = $form_state->getValues();
    // error_log('** FORM BLOCK SUBMIT **');
    // _dbg($values);
    $this->configuration['topic_sdg_indicators'] = $values['topic_sdg_indicators'];
    $this->configuration['topic_sdg_logo'] = empty($values['topic_sdg_logo']) ? 0 : 1;
    $this->configuration['topic_sdg_double'] = empty($values['topic_sdg_double']) ? 0 : 1;
  }

}
