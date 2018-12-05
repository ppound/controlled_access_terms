<?php

namespace Drupal\controlled_access_terms\Plugin\search_api\processor;

use Drupal\search_api\Processor\FieldsProcessorPluginBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Allows indexing of EDTF Date fields as dateranges.
 *
 * @SearchApiProcessor(
 *   id = "EDTFProcessor",
 *   label = @Translation("EDTF Processor"),
 *   description = @Translation("Converts EDTF dates to Solr format."),
 *   stages = {
 *     "preprocess_index" = 0,
 *     "preprocess_query" = 0,
 *   }
 * )
 */
class EDTFProcessor extends FieldsProcessorPluginBase {
  /**
   * Northern hemisphere season map.
   *
   * @var array
   */
  private $seasonMapNorth = [
    // Spring => March.
    '21' => '03',
    // Summer => June.
    '22' => '06',
    // Autumn => September.
    '23' => '09',
    // Winter => December.
    '24' => '12',
  ];

  /**
   * Southern hemisphere season map.
   *
   * @var array
   */
  private $seasonMapSouth = [
    // Spring => September.
    '21' => '03',
    // Summer => December.
    '22' => '06',
    // Autumn => March.
    '23' => '09',
    // Winter => June.
    '24' => '12',
  ];

  /**
   * {@inheritdoc}
   */
  protected function testType($type) {
    return ($type == 'etdf_date_range') ? TRUE : FALSE;
  }

  /**
   * {@inheritdoc}
   */
  protected function process(&$value) {
    if (strpos($value, '/') !== FALSE) {
      list($begin, $end) = explode('/', $value);
      $this->formatDate($begin);
      $this->formatDate($end);
      $value = "[$begin TO $end]";
    }
    else {
      $this->formatDate($value);
    }

  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $form['season_hemisphere'] = [
      '#title' => t('Hemisphere Seasons'),
      '#type' => 'select',
      '#default_value' => $this->configuration['season_hemisphere'],
      '#description' => t("Seasons don't have digit months so we map them 
                          to their respective equinox and solstice months.
                          Select a hemisphere to use for the mapping."),
      '#options' => [
        'north' => t('Northern Hemisphere'),
        'south' => t('Southern Hemisphere'),
      ],
    ];

    return $form;
  }

  /**
   * Create a date string solr can use in a daterange field.
   *
   * Remove ?,~ and other characters solr will not allow in a date field.
   *
   * @param string $value
   *   The date string to format.
   */
  protected function formatDate(&$value) {
    // Strip approximations/uncertainty.
    $date = str_replace(['?', '~'], '', $value);

    // Replace unspecified.
    // Month/day.
    $date = str_replace('-uu', '-01', $date);
    // Zero-Year in decade/century.
    $date = str_replace('u', '0', $date);

    // Seasons map.
    list($year, $month, $day) = explode('-', $date, 3);
    // Digit Seasons.
    if (in_array($month, ['21', '22', '23', '24'])) {
      $season_mapping = $this->configuration['season_hemisphere'] === 'north' ? $this->seasonMapNorth : $this->seasonMapSouth;
      $month = $season_mapping[$month];
      $date = implode('-', array_filter([$year, $month, $day]));
    }
    $value = $date;
  }

}
