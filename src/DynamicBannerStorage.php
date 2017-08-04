<?php

namespace Drupal\dynamic_banner;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\dynamic_banner\Entity\DynamicBannerInterface;
use Drupal\dynamic_banner\Entity\DynamicBanner;

/**
 * Defines the storage handler class for Dynamic banner entities.
 *
 * This extends the base storage class, adding required special handling for
 * Dynamic banner entities.
 *
 * @ingroup dynamic_banner
 */
class DynamicBannerStorage extends SqlContentEntityStorage implements DynamicBannerStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(DynamicBannerInterface $entity) {
    return $this->database->query(
      'SELECT vid FROM {dynamic_banner_revision} WHERE id=:id ORDER BY vid',
      [':id' => $entity->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function userRevisionIds(AccountInterface $account) {
    return $this->database->query(
      'SELECT vid FROM {dynamic_banner_field_revision} WHERE uid = :uid ORDER BY vid',
      [':uid' => $account->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function countDefaultLanguageRevisions(DynamicBannerInterface $entity) {
    return $this->database->query('SELECT COUNT(*) FROM {dynamic_banner_field_revision} WHERE id = :id AND default_langcode = 1', [':id' => $entity->id()])
      ->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function clearRevisionsLanguage(LanguageInterface $language) {
    return $this->database->update('dynamic_banner_revision')
      ->fields(['langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED])
      ->condition('langcode', $language->getId())
      ->execute();
  }

}
