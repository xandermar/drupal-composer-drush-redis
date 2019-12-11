<?php

namespace Drupal\font_awesome\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines the 'font_awesome_icon_picker' field widget.
 *
 * @FieldWidget(
 *   id = "font_awesome_icon_picker",
 *   label = @Translation("Font Awesome icon picker"),
 *   field_types = {"string"},
 * )
 */
class FontAwesomeIconPickerWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'default_value' => 'fas fa-eye',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {

    $element['default_value'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Default icon to show when the field is empty'),
      '#default_value' => $this->getSetting('default_value'),
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary[] = $this->t('Default value: @default_value', ['@default_value' => $this->getSetting('default_value')]);
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {

    $element['value'] = $element + [
      '#type' => 'textfield',
      '#default_value' => isset($items[$delta]->value) ? $items[$delta]->value : NULL,
      '#attributes' => [
        'class' => ['font-awesome-icon-picker'],
        'data-default-value' => $this->getSetting('default_value'),
        'data-input-search' => 'true',
        'data-collision' => 'true',
      ],
    ];

    $element['#attached']['library'][] = 'font_awesome/iconpicker';

    return $element;
  }

}
