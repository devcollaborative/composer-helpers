<?php

namespace devcollaborative\ComposerHelpers;

/**
 * Object representing a Drupal module or theme package.
 */
class DrupalPackage {

  public string $name;
  public string $currentVersion;
  public array $supportedVersions;
  private object $releases;

  function __construct($package) {
    $this->name = explode('/', $package->getName())[1];
    $this->currentVersion = $package->getExtra()['drupal']['version'];

    $module_data = simplexml_load_string(
      file_get_contents("https://updates.drupal.org/release-history/$this->name/current")
    );

    $supported_versions = explode(',', $module_data->supported_branches);
    foreach($supported_versions as $version) {
      $this->supportedVersions[]= rtrim($version, '.');
    }

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
      if (str_starts_with($this->currentVersion, $supported_version)) {
        $is_supported = TRUE;
      }
    }
    return $is_supported;
  }

  /**
   * Returns supported versions that are greater than the current release.
   *
   * @return array
   */
  public function getNewerSupportedVersions() {
    $newer_supported_versions = [];
    foreach ($this->supportedVersions as $currentVersion) {
      if ($this->standardizeBranchSyntax($currentVersion) > $this->currentVersion) {
        $newer_supported_versions[] = $currentVersion;
      }
    }
    return $newer_supported_versions;
  }

  /**
   * Helper function that converts old Drupal versioning to semantic versioning.
   *
   * Example: 8.x-1.2 becomes 1.2.
   *
   * @param string $branch
   * @return string
   */
  private function standardizeBranchSyntax($branch) {
    $array =  explode('-', $branch);
    return end($array);
  }
}
