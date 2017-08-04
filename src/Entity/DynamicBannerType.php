<?php

namespace Drupal\dynamic_banner\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the Dynamic banner type entity.
 *
 * @ConfigEntityType(
 *   id = "dynamic_banner_type",
 *   label = @Translation("Dynamic banner type"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\dynamic_banner\DynamicBannerTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\dynamic_banner\Form\DynamicBannerTypeForm",
 *       "edit" = "Drupal\dynamic_banner\Form\DynamicBannerTypeForm",
 *       "delete" = "Drupal\dynamic_banner\Form\DynamicBannerTypeDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\dynamic_banner\DynamicBannerTypeHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "dynamic_banner_type",
 *   admin_permission = "administer site configuration",
 *   bundle_of = "dynamic_banner",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/dynamic_banner_type/{dynamic_banner_type}",
 *     "add-form" = "/admin/structure/dynamic_banner_type/add",
 *     "edit-form" = "/admin/structure/dynamic_banner_type/{dynamic_banner_type}/edit",
 *     "delete-form" = "/admin/structure/dynamic_banner_type/{dynamic_banner_type}/delete",
 *     "collection" = "/admin/structure/dynamic_banner_type"
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "description"
 *   }
 * )
 */
class DynamicBannerType extends ConfigEntityBundleBase implements DynamicBannerTypeInterface {

    /**
     * The Dynamic banner type ID.
     *
     * @var string
     */
    protected $id;

    /**
     * The Dynamic banner type label.
     *
     * @var string
     */
    protected $label;

    /**
     * A brief description of this Dynamic Banner type.
     *
     * @var string
     */
    protected $description;

    /**
     * {@inheritdoc}
     */
    public function getDescription() {
        return $this->description;
    }

    /**
     * {@inheritdoc}
     */
    public function setDescription($description) {
        $this->description = $description;
        return $this;
    }

}
