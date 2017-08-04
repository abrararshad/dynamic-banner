<?php

namespace Drupal\dynamic_banner\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface for defining Dynamic banner type entities.
 */
interface DynamicBannerTypeInterface extends ConfigEntityInterface {

    /**
     * Returns the Dynamic banner type description.
     *
     * @return string
     *   The Dynamic banner description.
     */
    public function getDescription();

    /**
     * Sets the description of the Dynamic banner.
     *
     * @param string $description
     *   The new description.
     *
     * @return $this
     */
    public function setDescription($description);
}
