<?php

namespace Drupal\dynamic_banner\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for Dynamic banner edit forms.
 *
 * @ingroup dynamic_banner
 */
class DynamicBannerForm extends ContentEntityForm {

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state) {
        /* @var $entity \Drupal\dynamic_banner\Entity\DynamicBanner */
        $form = parent::buildForm($form, $form_state);

        if (!$this->entity->isNew()) {
            $form['new_revision'] = [
                '#type' => 'checkbox',
                '#title' => $this->t('Create new revision'),
                '#default_value' => FALSE,
                '#weight' => 10,
            ];
        }

        $entity = $this->entity;

        $form['status'] = [
            '#type' => 'checkbox',
            '#title' => $this->t('Status'),
            '#description' => $this->t('Check/uncheck to change the status of this banner.'),
            '#default_value' => $entity->isPublished(),
            '#weight' => 50,
        ];

        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function save(array $form, FormStateInterface $form_state) {
        $entity = &$this->entity;

        // Save as a new revision if requested to do so.
        if (!$form_state->isValueEmpty('new_revision') && $form_state->getValue('new_revision') != FALSE) {
            $entity->setNewRevision();

            // If a new revision is created, save the current user as revision author.
            $entity->setRevisionCreationTime(REQUEST_TIME);
            $entity->setRevisionUserId(\Drupal::currentUser()->id());
        } else {
            $entity->setNewRevision(FALSE);
        }

        $status = parent::save($form, $form_state);

        switch ($status) {
            case SAVED_NEW:
                drupal_set_message($this->t('Created the %label Dynamic banner.', [
                    '%label' => $entity->label(),
                ]));
                break;

            default:
                drupal_set_message($this->t('Saved the %label Dynamic banner.', [
                    '%label' => $entity->label(),
                ]));
        }
        $form_state->setRedirect('entity.dynamic_banner.canonical', ['dynamic_banner' => $entity->id()]);
    }

}
