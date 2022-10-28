<?php

namespace devcollaborative\ComposereHelpers;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;

class ComposerHelpersPLugin implements PluginInterface, EventSubscriberInterface
{
    public static function getSubscribedEvents()
  {
      return array(
          ScriptEvents::POST_UPDATE_CMD => 'checkDrupalStatus',
      );
  }

  public function checkDrupalStatus() {
    var_dump($this->composer);
    // lando drush core:requirements --severity=2 --ignore=public:///.htaccess,entity_update,search_api_server_unavailable"
  }
}
