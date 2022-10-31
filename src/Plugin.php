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
    $ignored_status_messages = [
      'public:///.htaccess',
      'entity_update',
      'search_api_server_unavailable',
    ];

    //TODO Allow additional ignored status messages to be set in composer.json config.

    $ignore_parameter = implode(',', $ignored_status_messages);

    //TODO Add means of configuring whether lando prefix is used.
    $command = 'lando drush core:requirements --severity=2 --ignore=' . $ignore_parameter;
    var_dump($command);
    echo exec($command);
  }
}
