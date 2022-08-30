<?php

namespace Drupal\hb_migration\Controller;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Extension\ModuleHandler;
use Drupal\Core\File\FileSystem;
use Drupal\file\FileRepository;
use Drupal\Core\File\FileSystemInterface;
use Drupal\media\Entity\Media;
use Drupal\taxonomy\Entity\Term;

/**
 * Class HBMigrationController to process XML file.
 */
class HBMigrationController extends ControllerBase {

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandler
   */
  protected $moduleHandler;

  /**
   * The file system.
   *
   * @var \Drupal\Core\File\FileSystem
   */
  protected $fileSystem;

  /**
   * The file repository.
   *
   * @var \Drupal\file\FileRepository
   */
  protected $fileRepository;

  /**
   * ModalFormContactController constructor.
   *
   * @param \Drupal\Core\Extension\ModuleHandler $moduleHandler
   *   The form builder.
   * @param \Drupal\Core\File\FileSystem $fileSystem
   *   The file system.
   * @param \Drupal\file\FileRepository $fileRepository
   *   The file repository.
   */
  public function __construct(ModuleHandler $moduleHandler, FileSystem $fileSystem, FileRepository $fileRepository) {
    $this->moduleHandler = $moduleHandler;
    $this->fileSystem = $fileSystem;
    $this->fileRepository = $fileRepository;
  }

  /**
   * {@inheritdoc}
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The Drupal service container.
   *
   * @return static
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('module_handler'),
      $container->get('file_system'),
      $container->get('file.repository')
    );
  }

  /**
   * Fetch file entities.
   *
   * @inheritdoc
   */
  public function fileFetch($files) {
    // Files are here on sitecore: https://www.hansonbridgett.com/-/media/
    libxml_use_internal_errors(TRUE);
    $module_path = $this->moduleHandler->getModule('hb_migration')->getPath() . '/files';
    $xml = simplexml_load_file($module_path . '/' . $files . '.xml') or die('Error: Cannot create object');

    $data = [];

    // Get the path of the first item.
    $path = (string) $xml->phrase[0]->attributes()->{'path'};
    $data[(string) $xml->phrase[0]->attributes()->{'key'} . '.pdf']['path'] = $path;
    $data[(string) $xml->phrase[0]->attributes()->{'key'} . '.pdf']['legacy'] = str_replace('}', '', str_replace('{', '', $xml->phrase[0]->attributes()->{'itemid'}));
    $data[(string) $xml->phrase[0]->attributes()->{'key'} . '.pdf']['updated'] = $xml->phrase[0]->attributes()->{'updated'};
    foreach ($xml->phrase as $item) {
      $item_path = (string) $item->attributes()->{'path'};
      $id = (string) $item->attributes()->{'key'} . '.pdf';
      if ($item_path !== $path) {
        $path = $item_path;
        $data[$id]['path'] = $path;
        $data[$id]['legacy'] = str_replace('}', '', str_replace('{', '', $item->attributes()->{'itemid'}));
        $data[$id]['updated'] = $item->attributes()->{'updated'};
      }
    }

    foreach ($data as $key => $path) {

      $media_entity = \Drupal::entityTypeManager()->getStorage('media')->loadByProperties([
        'field_legacy_id' => $path['legacy'],
      ]);
      if (!$media_entity) {
        $file_uri = str_replace('/sitecore/media library/', '', $path['path']);
        $file_uri = $file_uri . '.pdf';

        // Get the file.
        $file_data = file_get_contents(str_replace(' ', '%20', 'https://www.hansonbridgett.com/-/media/' . $file_uri));

        // Construct the directory name.
        $uri_parts = explode('/', $file_uri);
        $file_dir = '';
        for ($i = 0; $i < count($uri_parts) - 1; $i++) {
          $file_dir .= $uri_parts[$i] . '/';
        }

        $directory = 'public://sitecore/' . $file_dir;
        $this->fileSystem->prepareDirectory($directory, FileSystemInterface::CREATE_DIRECTORY);
        $file = $this->fileRepository->writeData($file_data, 'public://sitecore/' . $file_uri, FileSystemInterface::EXISTS_REPLACE);

        $media = Media::create([
          'bundle' => 'document',
          'uid' => 1,
          'field_media_document' => [
            'target_id' => $file->id(),
          ],
          'field_legacy_id' => $path['legacy'],
          'created' => strtotime($path['updated']),
          'changed' => strtotime($path['updated']),
        ]);

        $media->setName($key)
          ->setPublished(TRUE)
          ->save();
      }
    }

    return ['#markup' => ''];
  }

  /**
   * Fetch image entities.
   *
   * @inheritdoc
   */
  public function imageFetch($images) {
    // Files are here on sitecore: https://www.hansonbridgett.com/-/media/
    libxml_use_internal_errors(TRUE);
    $module_path = $this->moduleHandler->getModule('hb_migration')->getPath() . '/files';
    $xml = simplexml_load_file($module_path . '/' . $images . '.xml') or die('Error: Cannot create object');

    $data = [];

    // Get the path of the first item.
    $path = (string) $xml->phrase[0]->attributes()->{'path'};
    $data[(string) $xml->phrase[0]->attributes()->{'key'}]['path'] = $path;
    $data[(string) $xml->phrase[0]->attributes()->{'key'}]['legacy'] = str_replace('}', '', str_replace('{', '', $xml->phrase[0]->attributes()->{'itemid'}));
    $data[(string) $xml->phrase[0]->attributes()->{'key'}]['updated'] = $xml->phrase[0]->attributes()->{'updated'};
    $data[(string) $xml->phrase[0]->attributes()->{'key'}]['alt'] = $xml->phrase[0]->attributes()->{'alt'};
    foreach ($xml->phrase as $item) {
      $item_path = (string) $item->attributes()->{'path'};
      $id = (string) $item->attributes()->{'key'};
      if ($item_path !== $path) {
        $path = $item_path;
        $data[$id]['path'] = $path;
        $data[$id]['legacy'] = str_replace('}', '', str_replace('{', '', $item->attributes()->{'itemid'}));
        $data[$id]['updated'] = $item->attributes()->{'updated'};
        if ($item->attributes()->{'fieldid'} == 'Alt') {
          $data[$id]['alt'] = $item->en;
        } else {
          $data[$id]['alt'] = $item->attributes()->{'key'};
        }
      }
    }

    foreach ($data as $key => $path) {

      $file_uri = str_replace('/sitecore/media library/', '', $path['path']);
      $mime = hb_migration_get_image_type(str_replace(' ', '%20', 'https://www.hansonbridgett.com/-/media/' . $file_uri));
      if (!$mime) {
        $mime = 'jpeg';
      }
      $file_uri = $file_uri . '.' . $mime;

      // Get the file.
      $file_data = file_get_contents(str_replace(' ', '%20', 'https://www.hansonbridgett.com/-/media/' . $file_uri));

      // Construct the directory name.
      $uri_parts = explode('/', $file_uri);
      $file_dir = '';
      for ($i = 0; $i < count($uri_parts) - 1; $i++) {
        $file_dir .= $uri_parts[$i] . '/';
      }

      $directory = 'public://sitecore/' . $file_dir;
      $this->fileSystem->prepareDirectory($directory, FileSystemInterface::CREATE_DIRECTORY);

      $file = $this->fileRepository->writeData($file_data, 'public://sitecore/' . $file_uri, FileSystemInterface::EXISTS_REPLACE);
      $media_entity = \Drupal::entityTypeManager()->getStorage('media')->loadByProperties([
        'field_legacy_id' => $path['legacy'],
      ]);
      if (!$media_entity) {
        $media = Media::create([
          'bundle' => 'image',
          'uid' => 1,
          'field_media_image' => [
            'target_id' => $file->id(),
            'alt' => $path['alt'],
          ],
          'field_legacy_id' => $path['legacy'],
          'created' => strtotime($path['updated']),
          'changed' => strtotime($path['updated']),
        ]);

        $media->setName($key . '.' . $mime)
          ->setPublished(TRUE)
          ->save();
      }
    }

    return ['#markup' => ''];
  }

  /**
   * Fetch image entities.
   *
   * @inheritdoc
   */
  public function basicTaxonomy($vocabulary, $vid) {
    libxml_use_internal_errors(TRUE);
    $module_path = $this->moduleHandler->getModule('hb_migration')->getPath() . '/files';
    $xml = simplexml_load_file($module_path . '/' . $vocabulary . '.xml') or die('Error: Cannot create object');

    $data = [];

    foreach ($xml->phrase as $item) {
      $id = (string) $item->attributes()->{'key'};
      $data[$id]['legacy'] = str_replace('}', '', str_replace('{', '', $item->attributes()->{'itemid'}));
      $data[$id]['name'] = trim(strip_tags($item->en));
    }

    foreach ($data as $key => $value) {
      $term_entity = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadByProperties([
        'field_legacy_id' => $value['legacy'],
      ]);
      if (!$term_entity) {
        $term = Term::create(array(
          'parent' => array(),
          'name' => $value['name'],
          'vid' => $vid,
          'field_legacy_id' => $value['legacy'],
        ))->save();
      }
    }

    return ['#markup' => ''];
  }

}
