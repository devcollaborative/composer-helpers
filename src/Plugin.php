<?php

namespace devcollaborative\ComposerHelpers;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\ScriptEvents;

class Plugin implements PluginInterface, EventSubscriberInterface
{
  protected $composer;
  protected $io;
  protected $unsupported_modules = [];

  public function activate(Composer $composer, IOInterface $io) {
      $this->composer = $composer;
      $this->io = $io;
  }

  public function deactivate(Composer $composer, IOInterface $io) {
  }

  public function uninstall(Composer $composer, IOInterface $io) {
  }

  public static function getSubscribedEvents() {
      return array(
          ScriptEvents::POST_UPDATE_CMD => 'checkDrupalStatus',
      );
  }

  public function checkDrupalStatus() {
    $repositoryManager = $this->composer->getRepositoryManager();
    $localRepository = $repositoryManager->getLocalRepository();
    $installationManager = $this->composer->getInstallationManager();
    $packages = $localRepository->getPackages();

    foreach($packages as $package) {
      if (
        $package->getType() == 'drupal-module' &&
        isset($package->getExtra()['drupal']['version'])
      ) {
        $name= explode('/', $package->getName())[1];
        $version = $package->getExtra()['drupal']['version'];

        $module_data = simplexml_load_string(
          file_get_contents("https://updates.drupal.org/release-history/$name/current")
        );

        $supported_versions= explode(',', $module_data->supported_branches);

        $release_data = $module_data->releases[0];
        $is_supported = false;
        foreach($supported_versions as $supported_version) {
          if (str_starts_with($version, $supported_version)) {
            $is_supported = true;
          }
        }

        if (!$is_supported) {
          $this->unsupported_modules[] = $name;
        }
      }
    }

    var_dump($this->unsupported_modules);
    // lando drush core:requirements --severity=2 --ignore=public:///.htaccess,entity_update,search_api_server_unavailable"
  }
}
