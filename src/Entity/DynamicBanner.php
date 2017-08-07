<?php

namespace Drupal\dynamic_banner\Entity;

use Drupal\Component\Utility\SortArray;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\RevisionableContentEntityBase;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\user\UserInterface;

/**
 * Defines the Dynamic banner entity.
 *
 * @ingroup dynamic_banner
 *
 * @ContentEntityType(
 *   id = "dynamic_banner",
 *   label = @Translation("Dynamic banner"),
 *   bundle_label = @Translation("Dynamic banner type"),
 *   handlers = {
 *     "storage" = "Drupal\dynamic_banner\DynamicBannerStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\dynamic_banner\DynamicBannerListBuilder",
 *     "views_data" = "Drupal\dynamic_banner\Entity\DynamicBannerViewsData",
 *     "translation" = "Drupal\dynamic_banner\DynamicBannerTranslationHandler",
 *
 *     "form" = {
 *       "default" = "Drupal\dynamic_banner\Form\DynamicBannerForm",
 *       "add" = "Drupal\dynamic_banner\Form\DynamicBannerForm",
 *       "edit" = "Drupal\dynamic_banner\Form\DynamicBannerForm",
 *       "delete" = "Drupal\dynamic_banner\Form\DynamicBannerDeleteForm",
 *     },
 *     "access" = "Drupal\dynamic_banner\DynamicBannerAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\dynamic_banner\DynamicBannerHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "dynamic_banner",
 *   data_table = "dynamic_banner_field_data",
 *   revision_table = "dynamic_banner_revision",
 *   revision_data_table = "dynamic_banner_field_revision",
 *   translatable = TRUE,
 *   admin_permission = "administer dynamic banner entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "revision" = "vid",
 *     "bundle" = "type",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *     "status" = "status",
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/dynamic_banner/{dynamic_banner}",
 *     "add-page" = "/admin/structure/dynamic_banner/add",
 *     "add-form" = "/admin/structure/dynamic_banner/add/{dynamic_banner_type}",
 *     "edit-form" = "/admin/structure/dynamic_banner/{dynamic_banner}/edit",
 *     "delete-form" = "/admin/structure/dynamic_banner/{dynamic_banner}/delete",
 *     "version-history" = "/admin/structure/dynamic_banner/{dynamic_banner}/revisions",
 *     "revision" = "/admin/structure/dynamic_banner/{dynamic_banner}/revisions/{dynamic_banner_revision}/view",
 *     "revision_revert" = "/admin/structure/dynamic_banner/{dynamic_banner}/revisions/{dynamic_banner_revision}/revert",
 *     "translation_revert" = "/admin/structure/dynamic_banner/{dynamic_banner}/revisions/{dynamic_banner_revision}/revert/{langcode}",
 *     "revision_delete" = "/admin/structure/dynamic_banner/{dynamic_banner}/revisions/{dynamic_banner_revision}/delete",
 *     "collection" = "/admin/structure/dynamic_banner",
 *   },
 *   bundle_entity_type = "dynamic_banner_type",
 *   field_ui_base_route = "entity.dynamic_banner_type.edit_form"
 * )
 */
class DynamicBanner extends RevisionableContentEntityBase implements DynamicBannerInterface {

    use EntityChangedTrait;

    /**
     * {@inheritdoc}
     */
    public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
        parent::preCreate($storage_controller, $values);
        $values += [
            'user_id' => \Drupal::currentUser()->id(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function preSave(EntityStorageInterface $storage) {
        parent::preSave($storage);

        foreach (array_keys($this->getTranslationLanguages()) as $langcode) {
            $translation = $this->getTranslation($langcode);

            // If no owner has been set explicitly, make the anonymous user the owner.
            if (!$translation->getOwner()) {
                $translation->setOwnerId(0);
            }
        }

        // If no revision author has been set explicitly, make the dynamic_banner owner the
        // revision author.
        if (!$this->getRevisionUser()) {
            $this->setRevisionUserId($this->getOwnerId());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getName() {
        return $this->get('name')->value;
    }

    /**
     * {@inheritdoc}
     */
    public function setName($name) {
        $this->set('name', $name);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCreatedTime() {
        return $this->get('created')->value;
    }

    /**
     * {@inheritdoc}
     */
    public function setCreatedTime($timestamp) {
        $this->set('created', $timestamp);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getOwner() {
        return $this->get('user_id')->entity;
    }

    /**
     * {@inheritdoc}
     */
    public function getOwnerId() {
        return $this->get('user_id')->target_id;
    }

    /**
     * {@inheritdoc}
     */
    public function setOwnerId($uid) {
        $this->set('user_id', $uid);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setOwner(UserInterface $account) {
        $this->set('user_id', $account->id());
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isPublished() {
        return (bool)$this->getEntityKey('status');
    }

    /**
     * {@inheritdoc}
     */
    public function setPublished($published) {
        $this->set('status', $published ? TRUE : FALSE);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
        $fields = parent::baseFieldDefinitions($entity_type);

        $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
            ->setLabel(t('Authored by'))
            ->setDescription(t('The user ID of author of the Dynamic banner entity.'))
            ->setRevisionable(TRUE)
            ->setSetting('target_type', 'user')
            ->setSetting('handler', 'default')
            ->setTranslatable(TRUE)
            ->setDisplayOptions('view', [
                'label' => 'hidden',
                'type' => 'author',
                'weight' => 0,
            ])
            ->setDisplayOptions('form', [
                'type' => 'entity_reference_autocomplete',
                'weight' => 5,
                'settings' => [
                    'match_operator' => 'CONTAINS',
                    'size' => '60',
                    'autocomplete_type' => 'tags',
                    'placeholder' => '',
                ],
            ])
            ->setDisplayConfigurable('form', TRUE)
            ->setDisplayConfigurable('view', TRUE);

        $fields['name'] = BaseFieldDefinition::create('string')
            ->setLabel(t('Name'))
            ->setDescription(t('The name of the Dynamic banner entity.'))
            ->setRevisionable(TRUE)
            ->setTranslatable(TRUE)
            ->setSettings([
                'max_length' => 50,
                'text_processing' => 0,
            ])
            ->setDefaultValue('')
            ->setDisplayOptions('view', [
                'label' => 'above',
                'type' => 'string',
                'weight' => -10,
            ])
            ->setDisplayOptions('form', [
                'type' => 'string_textfield',
                'weight' => -10,
            ])
            ->setDisplayConfigurable('form', TRUE)
            ->setDisplayConfigurable('view', TRUE);

        $fields['image'] = BaseFieldDefinition::create('image')
            ->setLabel(t('Image'))
            ->setDescription(t('Image to be displayed'))
            ->setRevisionable(TRUE)
            ->setTranslatable(TRUE)
            ->setDefaultValue('')
            ->setSettings([
                'text_processing' => 0
            ])
            ->setDisplayOptions('view', [
                'label' => 'above',
                'type' => 'image',
                'weight' => -9
            ])
            ->setDisplayOptions('form', [
                'type' => 'image_image',
                'weight' => -9
            ])
            ->setDisplayConfigurable('form', TRUE)
            ->setDisplayConfigurable('view', TRUE);

        $paths_help = 'Example: <strong>/first-article</strong> or <strong>/first-article/*</strong>
                        For front page <strong> &lt;front&gt; </strong>
                        For all pages use either <strong>/*</strong> or <strong>*</strong>';

        $fields['path_include'] = BaseFieldDefinition::create('string_long')
            ->setLabel(t('Include Path'))
            ->setDescription(t('Specify paths to be included.') . ' ' . t($paths_help))
            ->setRevisionable(TRUE)
            ->setDefaultValue('')
            ->setSettings([
                'text_processing' => 0
            ])
            ->setDisplayOptions('view', [
                'label' => 'above',
                'type' => 'string',
                'weight' => -8
            ])
            ->setDisplayOptions('form', [
                'type' => 'string_textfield',
                'weight' => -8
            ])
            ->setDisplayConfigurable('form', TRUE)
            ->setDisplayConfigurable('view', TRUE);

        $fields['path_exclude'] = BaseFieldDefinition::create('string_long')
            ->setLabel(t('Exclude Path'))
            ->setDescription(t('Specify paths to be excluded.') . ' ' . t($paths_help))
            ->setRevisionable(TRUE)
            ->setDefaultValue('')
            ->setSettings([
                'text_processing' => 0
            ])
            ->setDisplayOptions('view', [
                'label' => 'above',
                'type' => 'string',
                'weight' => -7
            ])
            ->setDisplayOptions('form', [
                'type' => 'string_textfield',
                'weight' => -7
            ])
            ->setDisplayConfigurable('form', TRUE)
            ->setDisplayConfigurable('view', TRUE);

        $fields['status'] = BaseFieldDefinition::create('boolean')
            ->setLabel(t('Publishing status'))
            ->setDescription(t('A boolean indicating whether the Dynamic banner is published.'))
            ->setRevisionable(TRUE)
            ->setTranslatable(TRUE)
            ->setDefaultValue(TRUE);

        $fields['created'] = BaseFieldDefinition::create('created')
            ->setLabel(t('Created'))
            ->setRevisionable(TRUE)
            ->setTranslatable(TRUE)
            ->setDescription(t('The time that the entity was created.'));

        $fields['changed'] = BaseFieldDefinition::create('changed')
            ->setLabel(t('Changed'))
            ->setRevisionable(TRUE)
            ->setTranslatable(TRUE)
            ->setDescription(t('The time that the entity was last edited.'));

        $fields['revision_translation_affected'] = BaseFieldDefinition::create('boolean')
            ->setLabel(t('Revision translation affected'))
            ->setDescription(t('Indicates if the last edit of a translation belongs to current revision.'))
            ->setReadOnly(TRUE)
            ->setRevisionable(TRUE)
            ->setTranslatable(TRUE);

        return $fields;
    }

    public static function getBannersOfTypes(array $types) {
        if (empty($types))
            return [];

        $language = \Drupal::languageManager()->getCurrentLanguage()->getId();

        $ids = \Drupal::entityQuery('dynamic_banner')
            ->condition('type', $types, 'IN', $language)
            ->condition('status', 1)
            ->execute();
        return DynamicBanner::loadMultiple($ids);
    }

    public static function getBannersForPath(array $types, $path) {
        $banners = static::getBannersOfTypes($types);

        if (is_null($path))
            return $banners;

        $path_alias = \Drupal::service('path.alias_manager')->getAliasByPath($path);

        $selected_banners = [];
        foreach ($banners as $key => $banner) {
            $include_paths = explode(PHP_EOL, $banner->get('path_include')->value);
            $exclude_paths = explode(PHP_EOL, $banner->get('path_exclude')->value);
            if (static::isBannerValidForPath($path_alias, $include_paths)
                && static::isBannerValidForPath($path_alias, $exclude_paths, 'exclude')
            ) {
                $selected_banners[$key] = $banner;
            }
        }

        return $selected_banners;
    }

    public static function getBannersForSort(array $types) {
        return static::getBannersForPath($types, static::getDestination());
    }

    public static function getDestination() {
        $param = \Drupal::request()->query->all();

        return isset($param['destination']) ? '/' . $param['destination'] : Null;
    }

    public static function isBannerValidForPath(string $path, array $paths, $mode = 'include') {
        $path = ltrim(rtrim(strtolower($path), '/\\'), '/\\');
        $isValid = FALSE;

        foreach ($paths as $search_path) {
            $search_path = ltrim(rtrim(strtolower($search_path), '/\\'), '/\\');

            // Exact match
            if ($path === $search_path)
                $isValid = TRUE;

            // Wildcards either via /* or *
            if (!$isValid && $search_path === '/*' || $search_path === '*')
                $isValid = TRUE;

            // Front page
            $isFront = \Drupal::service('path.matcher')->isFrontPage();
            if (!$isValid && $isFront && $search_path === '<front>')
                $isValid = TRUE;

            if (!$isValid) {
                // Check for '/*' and go one level up in the actual path
                if (($pos = strpos($search_path, '/*')) !== FALSE) {
                    do {
                        if ($path . '/*' == $search_path)
                            $isValid = TRUE;

                        if (!$isValid) {
                            if (($pos = strrpos($path, '/')) !== FALSE && $pos !== 0) {
                                $path = substr($path, 0, $pos);
                            } else {
                                $path = FALSE;
                            }
                        }

                    } while ($path !== FALSE && $isValid == FALSE);
                }
            }

        }

        return ($mode === 'include') ? $isValid : !$isValid;
    }

    public static function setBannerWeight(array $banners, array $weights) {
        $sorts = [];

        foreach ($banners as $key => $banner) {
            $weight = 0;
            if (isset($weights[$key]))
                $weight = $weights[$key]['weight'];

            $banners[$key]->weight = $weight;
            $sorts[$key]['weight'] = $weight;
        }

        uasort($sorts, [SortArray::class, 'sortByWeightElement']);

        $sorted_banners = [];
        foreach ($sorts as $key => $weight) {
            $sorted_banners[$key] = $banners[$key];
        }

        return sizeof($sorts) > 0 ? $sorted_banners : $banners;
    }
}
