<?php

namespace devcollaborative\DrupalBranchStatus;

/**
 * Object representing the Drupal core package.
 */
class DrupalCore extends DrupalPackage {

  public function __construct($package) {
    $this->name = 'drupal';

    $this->currentVersion = $package->getVersion();
    $this->currentVersion = rtrim($this->currentVersion, '.0');
    $this->processDrupalData();
  }

}
