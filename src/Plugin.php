<?php

namespace devcollaborative\DrupalBranchStatus;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\ScriptEvents;

/**
 * Custom plugin to check drupal modules for branch upgrade/support status.
 */
class Plugin implements PluginInterface, EventSubscriberInterface {
  protected $composer;
  protected $io;
  protected $unsupportedModules = [];
  protected $upgradableModules = [];

  /**
   * {@inheritdoc}
   */
  public function activate(Composer $composer, IOInterface $io) {
    $this->composer = $composer;
    $this->io = $io;
  }

  /**
   * {@inheritdoc}
   */
  public function deactivate(Composer $composer, IOInterface $io) {
  }

  /**
   * {@inheritdoc}
   */
  public function uninstall(Composer $composer, IOInterface $io) {
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
      return array(
          ScriptEvents::POST_UPDATE_CMD => 'checkDrupalPackagesStatus',
      );
  }

  /**
   * Identifies installed Drupal packages and checks their branch statuses
   *
   * @return void
   */
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
          if ($module->getNewerSupportedVersions()) {
            $this->upgradableModules[] = $module;
          }
        }
      }
      else if ($package->getType() == "drupal-core") {
        $core = new DrupalCore($package);
        if (!$core->isCurrentBranchSupported()) {
          $this->unsupportedModules[] = $core;
        } else {
          if ($module->getNewerSupportedVersions()) {
            $this->upgradableModules[] = $core;
          }
        }
      }
    }
    $this->io->write("");
    $this->writeUnsupportedMessages();
    $this->writeUpgradeMessages();
    $this->io->write("");
  }

  /**
   * Outputs messages identifying drupal projects on unsupported branches.
   *
   * @return void
   */
  private function writeUnsupportedMessages() {
    if (!empty($this->unsupportedModules)) {
      foreach ($this->unsupportedModules as $module) {
        $supported_versions = implode('/', $module->supportedVersions);
        $this->io->write(
            "<error>- $module->name: $module->currentVersion is unsupported; change to a supported branch ($supported_versions)</error>"
        );
      }
      $this->io->write("");
    }
  }

  /**
   * Outputs messages identifying drupal projects with newer branches available.
   *
   * @return void
   */
  private function writeUpgradeMessages() {
    if (!empty($this->upgradableModules)) {
      foreach ($this->upgradableModules as $module) {
          $upgrade_list = implode('/', $module->getNewerSupportedVersions());
          $this->io->write(
            "<comment>- $module->name: consider upgrading from $module->currentVersion to a newer branch ($upgrade_list)</comment>"
          );
      }
      $this->io->write("");
    }

  }
}
