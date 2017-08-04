<?php

namespace Drupal\dynamic_banner;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * Defines a class to build a listing of Dynamic banner entities.
 *
 * @ingroup dynamic_banner
 */
class DynamicBannerListBuilder extends EntityListBuilder {


  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['name'] = $this->t('Name');
    $header['status'] = $this->t('Status');
    $header['type'] = $this->t('Type');
    $header['language'] = $this->t('Language');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\dynamic_banner\Entity\DynamicBanner */

    $row['name'] = Link::createFromRoute(
      $entity->label(),
      'entity.dynamic_banner.edit_form',
      ['dynamic_banner' => $entity->id()]
    );
    $row['status'] = $entity->isPublished() ? $this->t('Published') : $this->t('Unpublished');
    $row['type'] = $entity->bundle();
    $row['language'] = $entity->language()->getName();
    return $row + parent::buildRow($entity);
  }

}
