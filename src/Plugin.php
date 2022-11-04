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
  protected $unsupportedModules = [];


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
          ScriptEvents::POST_UPDATE_CMD => 'checkDrupalPackagesStatus',
      );
  }

  public function checkDrupalPackagesStatus() {
    $repositoryManager = $this->composer->getRepositoryManager();
    $localRepository = $repositoryManager->getLocalRepository();
    // $installationManager = $this->composer->getInstallationManager();
    $packages = $localRepository->getPackages();

    foreach ($packages as $package) {
      if (
        $package->getType() == 'drupal-module' &&
        isset($package->getExtra()['drupal']['version'])
      ) {
        $module = new DrupalPackage($package);
        if (!$module->isCurrentBranchSupported()) {
          $this->unsupportedModules[] = $module;
        }
      }
    }

    if (!empty($this->unsupportedModules)) {
      $this->io->write(
          '<comment>You are using versions of Drupal modules that are no longer supported:</comment>'
      );
      foreach ($this->unsupportedModules as $module) {
        $supported_versions = implode(', ', $module->supportedVersions);
        $this->io->write(
            "<error>- $module->name $module->version is unsupported; use instead: $supported_versions</error>"
        );
      }
      $this->io->write(
            "<comment>Please upgrade these modules to a supported branch as soon as possible.</comment>\n"
        );
    }
  }
}
