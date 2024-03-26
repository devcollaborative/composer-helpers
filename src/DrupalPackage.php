<?php

namespace devcollaborative\DrupalBranchStatus;

/**
 * Object representing a Drupal module or theme package.
 */
class DrupalPackage {

  public string $name;
  public string $currentVersion;
  public array $supportedVersions;
  protected object $releases;

  public function __construct($package) {
    $this->name = explode('/', $package->getName())[1];
    if ($this->name == 'core') {
      $this->name = 'drupal';
    }

    $this->currentVersion = $package->getExtra()['drupal']['version'];
    $this->processDrupalData();
  }

  /**
   * Get the xml module data from drupal.org and parse it.
   */
  protected function processDrupalData() {
    $module_data = simplexml_load_string(
      file_get_contents("https://updates.drupal.org/release-history/$this->name/current")
    );

    $supported_versions = explode(',', $module_data->supported_branches);
    foreach ($supported_versions as $version) {
      $this->supportedVersions[] = rtrim($version, '.');
    }

    $this->releases = $module_data->releases[0];
  }

  /**
   * Checks for maintainer support for the current project branch.
   *
   * @return bool
   *   Whether or not the current branch is supported.
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
   * Returns supported, covered versions newer than the current release.
   *
   * @return array
   *   List of newer branches of the module.
   */
  public function getNewerSupportedVersions() {
    $newer_supported_versions = [];
    foreach ($this->supportedVersions as $version) {
      // Check to see if branch version is higher than current branch.
      if ($this->standardizeBranchSyntax($version) > $this->standardizeBranchSyntax($this->currentVersion)) {

        // Check if branch has a release with security advisory coverage.
        $has_coverage = FALSE;
        foreach ($this->releases as $release) {
          if (
            str_starts_with($release->version, $version) &&
            $release->security == "Covered by Drupal's security advisory policy"
          ) {
            $has_coverage = TRUE;
            break;
          }
        }

        if ($has_coverage) {
          $newer_supported_versions[] = $version;
        }
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
   *   Branch name from raw drupal data.
   *
   * @return string
   *   Standardized branch name.
   */
  private function standardizeBranchSyntax($branch) {
    $array = explode('-', $branch);
    return end($array);
  }

}
