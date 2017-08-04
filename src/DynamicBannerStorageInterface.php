<?php

namespace Drupal\dynamic_banner;

use Drupal\Core\Entity\ContentEntityStorageInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\dynamic_banner\Entity\DynamicBannerInterface;

/**
 * Defines the storage handler class for Dynamic banner entities.
 *
 * This extends the base storage class, adding required special handling for
 * Dynamic banner entities.
 *
 * @ingroup dynamic_banner
 */
interface DynamicBannerStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of Dynamic banner revision IDs for a specific Dynamic banner.
   *
   * @param \Drupal\dynamic_banner\Entity\DynamicBannerInterface $entity
   *   The Dynamic banner entity.
   *
   * @return int[]
   *   Dynamic banner revision IDs (in ascending order).
   */
  public function revisionIds(DynamicBannerInterface $entity);

  /**
   * Gets a list of revision IDs having a given user as Dynamic banner author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   Dynamic banner revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account);

  /**
   * Counts the number of revisions in the default language.
   *
   * @param \Drupal\dynamic_banner\Entity\DynamicBannerInterface $entity
   *   The Dynamic banner entity.
   *
   * @return int
   *   The number of revisions in the default language.
   */
  public function countDefaultLanguageRevisions(DynamicBannerInterface $entity);

  /**
   * Unsets the language for all Dynamic banner with the given language.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language object.
   */
  public function clearRevisionsLanguage(LanguageInterface $language);

}
