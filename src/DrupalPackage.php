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

  public function HigherSupportedBranchAvailable() {
    $higher_supported_versions = [];
    foreach ($this->supportedVersions as $version) {
      if ($this->standardizeBranchSyntax($version) > $this->version) {
        $higher_supported_versions[] = $version;
      }
    }
    return $higher_supported_versions;
  }

  private function standardizeBranchSyntax($branch) {
    $array =  explode('-', $branch);
    return end($array);
  }

}
