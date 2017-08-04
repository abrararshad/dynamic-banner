<?php

namespace Drupal\dynamic_banner\Entity;

use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Entity\RevisionableInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Dynamic banner entities.
 *
 * @ingroup dynamic_banner
 */
interface DynamicBannerInterface extends RevisionableInterface, RevisionLogInterface, EntityChangedInterface, EntityOwnerInterface {

  // Add get/set methods for your configuration properties here.

  /**
   * Gets the Dynamic banner name.
   *
   * @return string
   *   Name of the Dynamic banner.
   */
  public function getName();

  /**
   * Sets the Dynamic banner name.
   *
   * @param string $name
   *   The Dynamic banner name.
   *
   * @return \Drupal\dynamic_banner\Entity\DynamicBannerInterface
   *   The called Dynamic banner entity.
   */
  public function setName($name);

  /**
   * Gets the Dynamic banner creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Dynamic banner.
   */
  public function getCreatedTime();

  /**
   * Sets the Dynamic banner creation timestamp.
   *
   * @param int $timestamp
   *   The Dynamic banner creation timestamp.
   *
   * @return \Drupal\dynamic_banner\Entity\DynamicBannerInterface
   *   The called Dynamic banner entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the Dynamic banner published status indicator.
   *
   * Unpublished Dynamic banner are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Dynamic banner is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a Dynamic banner.
   *
   * @param bool $published
   *   TRUE to set this Dynamic banner to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\dynamic_banner\Entity\DynamicBannerInterface
   *   The called Dynamic banner entity.
   */
  public function setPublished($published);

  /**
   * Gets the Dynamic banner revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the Dynamic banner revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\dynamic_banner\Entity\DynamicBannerInterface
   *   The called Dynamic banner entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Gets the Dynamic banner revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   */
  public function getRevisionUser();

  /**
   * Sets the Dynamic banner revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return \Drupal\dynamic_banner\Entity\DynamicBannerInterface
   *   The called Dynamic banner entity.
   */
  public function setRevisionUserId($uid);

}
