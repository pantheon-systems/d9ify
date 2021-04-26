<?php

namespace D9ify\Composer;


/**
 * Class ComposerSectionBase
 *
 * @package D9ify\Composer
 */
abstract class ComposerSectionBase {

  protected array $sectionValues = [];

  /**
   * ComposerSectionBase constructor.
   *
   * @param $values
   */
  public function __construct($values) {
    foreach ($values as $requirement => $version) {
      $this->sectionValues[$requirement] = $version;
    }
  }

  /**
   * @return string
   */
  public function __toJson() : string {
    return \json_ecode($this, JSON_PRETTY_PRINT);
  }

}
