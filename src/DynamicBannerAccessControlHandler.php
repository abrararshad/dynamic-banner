<?php

namespace Drupal\dynamic_banner;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Dynamic banner entity.
 *
 * @see \Drupal\dynamic_banner\Entity\DynamicBanner.
 */
class DynamicBannerAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\dynamic_banner\Entity\DynamicBannerInterface $entity */
    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished dynamic banner entities');
        }
        return AccessResult::allowedIfHasPermission($account, 'view published dynamic banner entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit dynamic banner entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete dynamic banner entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add dynamic banner entities');
  }

}
