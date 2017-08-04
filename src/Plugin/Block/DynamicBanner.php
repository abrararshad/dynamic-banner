<?php

namespace Drupal\dynamic_banner\Plugin\Block;

use Drupal\Core\Annotation\Translation;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\dynamic_banner\Entity\DynamicBanner as BannerEntity;


/**
 * Class HelloBlock
 * @package Drupal\custom_test\Plugin\Block
 *
 * @Block(
 *     id = "dynamic_banner_block",
 *     admin_label = @Translation("Dynamic Banner"),
 *     category = @Translation("Dynamic Banner")
 * )
 */
class DynamicBanner extends BlockBase implements BlockPluginInterface {

    private $libraries;

    private $bannersConfigKey;

    private $bannerTypesConfigKey;

    private $libraryConfigKey;

    private $overridePath;

    private $override;

    public function __construct(array $configuration, $plugin_id, $plugin_definition) {
        parent::__construct($configuration, $plugin_id, $plugin_definition);

        $this->override = $this->getConfigOverride();
        $this->overridePath = BannerEntity::getDestination();

        $this->bannersConfigKey = 'banner';
        $this->bannerTypesConfigKey = 'banner_types';
        $this->libraryConfigKey = 'library';

        $this->libraries = $this->invokeAllAddLibrariesInfo();
    }

    /**
     * {@inheritdoc}
     */
    public function build() {
        $this->override = $this->getConfigOverride();
        $this->overridePath = $path = \Drupal::service('path.current')->getPath();

        $banners = BannerEntity::setBannerWeight($this->getBanners(), $this->getConfigBanners());

        if (sizeof($banners) > 0) {
            $render_array = entity_view_multiple($banners, 'full');

            foreach ($render_array as $key => $array) {
                if (is_numeric($key))
                    $render_array[$key]['#theme'] = 'dynamic_banner_banner';
            }

            // Default library
            $render_array['#attached']['library'] = ['dynamic_banner/dynamic_banner.bxslider'];

            $library = $this->getLibrary();
            $library_classes = '';
            if(isset($this->libraries[$library]) && isset($this->libraries[$library]['library'])) {
                $render_array['#attached']['library'] = [$this->libraries[$library]['library']];
                $library_classes = $this->libraries[$library]['classes'];
            }

            $render_array['#theme'] = 'dynamic_banner_slideshow';
            $render_array['#configuration'] = $this->configuration;
            $render_array['#library_name'] = $library;
            $render_array['#plugin_id'] = $this->pluginId;
            $render_array['#id'] = $this->configuration['instance_id'];
            $render_array['#classes'] = $library_classes;

            return $render_array;
        }

        return [];
    }

    public function getBanners() {
        $banner_types = $this->getConfigBannerTypes();

        $path = \Drupal::service('path.current')->getPath();
        return BannerEntity::getBannersForPath($banner_types, $path);
    }

    public function blockForm($form, FormStateInterface $form_state) {
        $form = parent::blockForm($form, $form_state);
        $bundles = \Drupal::entityManager()->getBundleInfo('dynamic_banner');
        $banner_types = $this->getConfigBannerTypes();

        $types = [];
        $selected_types = [];
        foreach ($bundles as $key => $bundle) {
            $types[$key] = $bundle['label'];

            if (in_array($key, $banner_types)) {
                array_push($selected_types, $key);
            }
        }

        if(!$this->override && !is_null($this->overridePath)) {
            $form['override'] = array(
                '#type' => 'checkbox',
                '#title' => $this->t('Override'),
                '#description' => $this->t('Override below config for <strong>%path</strong>
                    (destination in the URL)', ['%path' => $this->overridePath]),
                '#default_value' => $this->override,
                '#weight' => 3
            );
        }

        if($this->override && !is_null($this->overridePath)) {
            $form['revert'] = array(
                '#type' => 'checkbox',
                '#title' => $this->t('Revert'),
                '#description' => $this->t('Revert changes back to the block\'s default.'),
                '#weight' => 3
            );
        }else{
            $help = 'Below settings will be applied to all pages showing this block. 
            Editing this block from the page itself would allow override the configs 
            (it checks for destination in the URL).';

            $form['override_help'] = array(
                '#markup' => $this->t($help)
            );
        }

        $form['banner_types'] = array(
            '#type' => 'checkboxes',
            '#title' => $this->t('Banner Types'),
            '#description' => $this->t('Choose the type of banners you want to display images from.'),
            '#default_value' => $selected_types,
            '#options' => $types,
            '#weight' => 4
        );

        $library_names = [];
        foreach($this->libraries as $key => $library) {
            $library_names[$key] = $library['name'];
        };

        $form['library'] = array(
            '#type' => 'select',
            '#title' => $this->t('Library'),
            '#description' => $this->t('Attach library to slideshow the banners of this block/page.'),
            '#default_value' => $this->getLibrary(),
            '#options' => $library_names,
            '#weight' => 5
        );

        $form = array_merge($form, $this->getBlockBannersSortForm());

        return ['container' => [
                '#type' => 'fieldset',
                '#title' => $this->t('Banner Configuration' . ($this->override ? ' [Overriding]' : '')),
                '#attributes' => [
                    'class' => ['banner-block-container']
                ]
            ] + $form];
    }

    public function blockSubmit($form, FormStateInterface $form_state) {
        parent::blockSubmit($form, $form_state);
        $this->configuration['instance_id'] = $form['id']['#default_value'];

        $values = $form_state->getValue('container');

        if(isset($values['revert']) && $values['revert']) {
            $this->revertPathConfigs();
        }else {
            if (isset($values['override'])) {
                $this->override = $values['override'];
                $this->setConfigOverride($this->override);
            }

            if (isset($values['banner_types']))
                $this->setConfigBannerTypes($values['banner_types']);

            if (isset($values['banners']))
                $this->setConfigBanners($values['banners']);

            if(isset($values['library']))
                $this->setLibrary($values['library']);

        }
    }

    public function getOverriddenConfigKey($key) {
        if ($this->override && !is_null($this->overridePath)) {
            $path = str_replace('/', '_', $this->overridePath);

            return $key . '_' . $path;
        }

        return $key;
    }

    public function revertPathConfigs(){
        $this->removePathConfig($this->bannersConfigKey);
        $this->removePathConfig($this->bannerTypesConfigKey);

        $this->setConfigOverride(false);
    }

    public function removePathConfig($key){
        $pathKey = $this->getOverriddenConfigKey($key);
        if($key !== $pathKey) {
            unset($this->configuration[$pathKey]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheContexts() {
        return Cache::mergeContexts(parent::getCacheContexts(), ['route']);
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheTags() {
        // TODO: Fix it
        return Cache::mergeTags(parent::getCacheTags(), ['banner_list']);
    }

    public function getCacheMaxAge() {
        return 0;
    }

    public function getBlockBannersSortForm() {
        $banner_types = $this->getConfigBannerTypes();
        if (sizeof($banner_types) < 1) {
         return [
           'no_banner_text' => [
               '#markup' => $this->t('Once you have any <em>Banner Type</em> selected above. 
                Banners will be displayed here for sorting.'),
               '#weight' => 5
           ]
         ];
        }

        $form = [];
        $form['banners'] = [
            '#type' => 'table',
            '#header' => [
                $this->t('Banners'),
                $this->t('Weight'),
                $this->t('Operations')
            ],
            '#tabledrag' => [
                [
                    'action' => 'order',
                    'relationship' => 'sibling',
                    'group' => 'dynamic-banner-order-weight',
                ],
            ],
            '#attributes' => [
                'id' => 'dynamic-banners',
            ],
            '#empty' => t('There are currently no banners'),
            '#weight' => 5,
        ];

        $banners = BannerEntity::getBannersForSort($banner_types);
        $sorted_banners = BannerEntity::setBannerWeight($banners, $this->getConfigBanners());

        foreach ($sorted_banners as $key => $banner) {
            $key = $banner->id();

            $form['banners'][$key]['#attributes']['class'][] = 'draggable';
            $form['banners'][$key]['#weight'] = $banner->weight;
            $form['banners'][$key]['banner'] = [
                '#tree' => FALSE,
                'data' => [
                    'label' => [
                        '#plain_text' => $banner->label()
                    ],
                ],
            ];

            $form['banners'][$key]['weight'] = [
                '#type' => 'weight',
                '#title' => $this->t('Weight for @title', ['@title' => $banner->label]),
                '#title_display' => 'invisible',
                '#default_value' => $banner->weight,
                '#attributes' => [
                    'class' => ['dynamic-banner-order-weight'],
                ],
            ];

            $links = [];
            $links['edit'] = [
                'title' => $this->t('Edit'),
                'url' => Url::fromRoute('entity.dynamic_banner.edit_form', [
                    'dynamic_banner' => $banner->id()
                ]),
            ];

            $links['delete'] = [
                'title' => $this->t('Delete'),
                'url' => Url::fromRoute('entity.dynamic_banner.delete_form', [
                    'dynamic_banner' => $banner->id()
                ]),
            ];

            $form['banners'][$key]['operations'] = [
                '#type' => 'operations',
                '#links' => $links,
            ];
        }

        return $form;
    }

    public function getConfigBannerTypes() {
        $config = $this->configuration;

        $key = $this->getOverriddenConfigKey($this->bannerTypesConfigKey);
        $bannerTypesConfig = isset($config[$key]) ? $config[$key] : [];

        if (empty($bannerTypesConfig))
            $bannerTypesConfig = isset($config[$this->bannerTypesConfigKey]) ?
                $config[$this->bannerTypesConfigKey] : [];

        if (empty($bannerTypesConfig))
            return [];

        $types = [];
        foreach ($bannerTypesConfig as $key => $val) {
            if ($val !== 0)
                array_push($types, $key);
        }

        return $types;
    }

    public function setConfigBannerTypes($config) {
        $key = $this->getOverriddenConfigKey($this->bannerTypesConfigKey);
        $this->configuration[$key] = $config;
    }

    public function getConfigBanners() {
        $config = $this->configuration;

        $key = $this->getOverriddenConfigKey($this->bannersConfigKey);
        $bannerConfig = isset($config[$key]) ? $config[$key] : [];

        if (empty($bannerConfig)) {
            $bannerConfig = isset($config[$this->bannersConfigKey]) ?
                $config[$this->bannersConfigKey] : [];
        }

        return $bannerConfig;
    }

    public function setConfigBanners($config) {
        $key = $this->getOverriddenConfigKey($this->bannersConfigKey);
        $this->configuration[$key] = $config;
    }

    public function setConfigOverride($config){
        $this->configuration['override'] = $config;
    }

    public function getConfigOverride(){
        return isset($this->configuration['override']) ? $this->configuration['override'] : FALSE;
    }

    public function setLibrary($config){
        $key = $this->getOverriddenConfigKey($this->libraryConfigKey);
        $this->configuration[$key] = $config;
    }

    public function getLibrary() {
        $config = $this->configuration;

        $key = $this->getOverriddenConfigKey($this->libraryConfigKey);
        $libraryConfig = isset($config[$key]) ? $config[$key] : Null;

        if (empty($libraryConfigConfig)) {
            $libraryConfigConfig = isset($config[$this->libraryConfigKey]) ?
                $config[$this->libraryConfigKey] : Null;
        }

        return $libraryConfig;
    }

    public function invokeAllAddLibrariesInfo(){
        $module_handler = \Drupal::moduleHandler();
        $libraries = $module_handler->invokeAll('dynamic_banner_add_libraries');
        return $libraries;
    }

}








