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
  protected $upgradableModules = [];


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
        } else {
          $higher_versions = ($module->getNewerSupportedVersions());
          if (!empty($higher_versions)) {
            $this->upgradableModules[$module->name] = [
              'version' => $module->currentVersion,
              'upgrade_list' => implode('/', $higher_versions),
            ];
          }
        }
      }
    }
    $this->io->write("");
    $this->writeUnsupportedMessages();
    $this->writeUpgradeMessages();
    $this->io->write("");
  }

  private function writeUnsupportedMessages() {
    if (!empty($this->unsupportedModules)) {
      foreach ($this->unsupportedModules as $module) {
        $supported_versions = implode('/', $module->supportedVersions);
        $this->io->write(
            "<error>- $module->name $module->currentVersion is unsupported; change to a supported branch: $supported_versions</error>"
        );
      }
      $this->io->write("");
    }
  }

  private function writeUpgradeMessages() {
    if (!empty($this->upgradableModules)) {
      foreach ($this->upgradableModules as $module => $data) {
          $version = $data['version'];
          $upgrade_list = $data['upgrade_list'];

          $this->io->write(
            "<comment>- Consider upgrading $module $version to a newer branch: $upgrade_list</comment>"
          );
      }
      $this->io->write("");
    }

  }
}
