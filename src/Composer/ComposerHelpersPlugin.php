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


}
