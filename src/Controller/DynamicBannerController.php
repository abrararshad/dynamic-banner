<?php

namespace Drupal\dynamic_banner\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Url;
use Drupal\dynamic_banner\Entity\DynamicBannerInterface;

/**
 * Class DynamicBannerController.
 *
 *  Returns responses for Dynamic banner routes.
 */
class DynamicBannerController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * Displays a Dynamic banner  revision.
   *
   * @param int $dynamic_banner_revision
   *   The Dynamic banner  revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($dynamic_banner_revision) {
    $dynamic_banner = $this->entityManager()->getStorage('dynamic_banner')->loadRevision($dynamic_banner_revision);
    $view_builder = $this->entityManager()->getViewBuilder('dynamic_banner');

    return $view_builder->view($dynamic_banner);
  }

  /**
   * Page title callback for a Dynamic banner  revision.
   *
   * @param int $dynamic_banner_revision
   *   The Dynamic banner  revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($dynamic_banner_revision) {
    $dynamic_banner = $this->entityManager()->getStorage('dynamic_banner')->loadRevision($dynamic_banner_revision);
    return $this->t('Revision of %title from %date', ['%title' => $dynamic_banner->label(), '%date' => format_date($dynamic_banner->getRevisionCreationTime())]);
  }

  /**
   * Generates an overview table of older revisions of a Dynamic banner .
   *
   * @param \Drupal\dynamic_banner\Entity\DynamicBannerInterface $dynamic_banner
   *   A Dynamic banner  object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(DynamicBannerInterface $dynamic_banner) {
    $account = $this->currentUser();
    $langcode = $dynamic_banner->language()->getId();
    $langname = $dynamic_banner->language()->getName();
    $languages = $dynamic_banner->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $dynamic_banner_storage = $this->entityManager()->getStorage('dynamic_banner');

    $build['#title'] = $has_translations ? $this->t('@langname revisions for %title', ['@langname' => $langname, '%title' => $dynamic_banner->label()]) : $this->t('Revisions for %title', ['%title' => $dynamic_banner->label()]);
    $header = [$this->t('Revision'), $this->t('Operations')];

    $revert_permission = (($account->hasPermission("revert all dynamic banner revisions") || $account->hasPermission('administer dynamic banner entities')));
    $delete_permission = (($account->hasPermission("delete all dynamic banner revisions") || $account->hasPermission('administer dynamic banner entities')));

    $rows = [];

    $vids = $dynamic_banner_storage->revisionIds($dynamic_banner);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\dynamic_banner\DynamicBannerInterface $revision */
      $revision = $dynamic_banner_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link to link to revisions that are not active.
        $date = \Drupal::service('date.formatter')->format($revision->getRevisionCreationTime(), 'short');
        if ($vid != $dynamic_banner->getRevisionId()) {
          $link = $this->l($date, new Url('entity.dynamic_banner.revision', ['dynamic_banner' => $dynamic_banner->id(), 'dynamic_banner_revision' => $vid]));
        }
        else {
          $link = $dynamic_banner->link($date);
        }

        $row = [];
        $column = [
          'data' => [
            '#type' => 'inline_template',
            '#template' => '{% trans %}{{ date }} by {{ username }}{% endtrans %}{% if message %}<p class="revision-log">{{ message }}</p>{% endif %}',
            '#context' => [
              'date' => $link,
              'username' => \Drupal::service('renderer')->renderPlain($username),
              'message' => ['#markup' => $revision->getRevisionLogMessage(), '#allowed_tags' => Xss::getHtmlTagList()],
            ],
          ],
        ];
        $row[] = $column;

        if ($latest_revision) {
          $row[] = [
            'data' => [
              '#prefix' => '<em>',
              '#markup' => $this->t('Current revision'),
              '#suffix' => '</em>',
            ],
          ];
          foreach ($row as &$current) {
            $current['class'] = ['revision-current'];
          }
          $latest_revision = FALSE;
        }
        else {
          $links = [];
          if ($revert_permission) {
            $links['revert'] = [
              'title' => $this->t('Revert'),
              'url' => $has_translations ?
              Url::fromRoute('entity.dynamic_banner.translation_revert', ['dynamic_banner' => $dynamic_banner->id(), 'dynamic_banner_revision' => $vid, 'langcode' => $langcode]) :
              Url::fromRoute('entity.dynamic_banner.revision_revert', ['dynamic_banner' => $dynamic_banner->id(), 'dynamic_banner_revision' => $vid]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.dynamic_banner.revision_delete', ['dynamic_banner' => $dynamic_banner->id(), 'dynamic_banner_revision' => $vid]),
            ];
          }

          $row[] = [
            'data' => [
              '#type' => 'operations',
              '#links' => $links,
            ],
          ];
        }

        $rows[] = $row;
      }
    }

    $build['dynamic_banner_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];

    return $build;
  }

}
