<?php

namespace Drupal\dynamic_banner\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\language\Entity\ContentLanguageSettings;

/**
 * Class DynamicBannerTypeForm.
 */
class DynamicBannerTypeForm extends EntityForm {

    /**
     * {@inheritdoc}
     */
    public function form(array $form, FormStateInterface $form_state) {
        $form = parent::form($form, $form_state);

        $entity = $this->entity;
        $form['label'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Label'),
            '#maxlength' => 255,
            '#default_value' => $entity->label(),
            '#description' => $this->t("Label for the Dynamic banner type."),
            '#required' => TRUE,
        ];

        $form['id'] = [
            '#type' => 'machine_name',
            '#default_value' => $entity->id(),
            '#machine_name' => [
                'exists' => '\Drupal\dynamic_banner\Entity\DynamicBannerType::load',
            ],
            '#disabled' => !$entity->isNew(),
        ];

        $form['description'] = [
            '#title' => t('Description'),
            '#type' => 'textarea',
            '#default_value' => $entity->getDescription(),
            '#description' => t('This text will be displayed on the <em>Add banner</em> page.'),
        ];

        $form['additional_settings'] = [
            '#type' => 'vertical_tabs'
        ];

        if ($this->moduleHandler->moduleExists('language')) {
            $form['language'] = [
                '#type' => 'details',
                '#title' => t('Language settings'),
                '#group' => 'additional_settings',
            ];

            $language_configuration = ContentLanguageSettings::loadByEntityTypeBundle('dynamic_banner', $entity->id());
            $form['language']['language_configuration'] = [
                '#type' => 'language_configuration',
                '#entity_information' => [
                    'entity_type' => 'dynamic_banner',
                    'bundle' => $entity->id(),
                ],
                '#default_value' => $language_configuration,
            ];
        }

        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function save(array $form, FormStateInterface $form_state) {
        $dynamic_banner_type = $this->entity;
        $status = $dynamic_banner_type->save();

        switch ($status) {
            case SAVED_NEW:
                drupal_set_message($this->t('Created the %label Dynamic banner type.', [
                    '%label' => $dynamic_banner_type->label(),
                ]));
                break;

            default:
                drupal_set_message($this->t('Saved the %label Dynamic banner type.', [
                    '%label' => $dynamic_banner_type->label(),
                ]));
        }
        $form_state->setRedirectUrl($dynamic_banner_type->toUrl('collection'));
    }

}
