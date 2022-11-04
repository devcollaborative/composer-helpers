<?php

namespace devcollaborative\ComposerHelpers;

/**
 * Object representing a Drupal module or theme package.
 */
class DrupalPackage {

  public string $name;
  public string $version;
  public array $supportedVersions;
  private object $releases;

  function __construct($package) {
    $this->name = explode('/', $package->getName())[1];
    $this->version = $package->getExtra()['drupal']['version'];

    $module_data = simplexml_load_string(
      file_get_contents("https://updates.drupal.org/release-history/$this->name/current")
    );

    $this->supportedVersions = explode(',', $module_data->supported_branches);

    $this->releases = $module_data->releases[0];
  }

  /**
   * Checks for maintainer support for the current project branch.
   *
   * @return bool
   */
  public function isCurrentBranchSupported() {
    $is_supported = FALSE;
    foreach ($this->supportedVersions as $supported_version) {
      if (str_starts_with($this->version, $supported_version)) {
        $is_supported = TRUE;
      }
    }
    return $is_supported;
  }

  public function isHigherSupportedBranchAvailable() {
    var_dump($this->supportedVersions);
  }
}
