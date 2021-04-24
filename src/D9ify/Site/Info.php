<?php

namespace D9ify\Site;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Info {

  protected string $id;
  protected string $name;
  protected string $label;
  protected string $created;
  protected string $framework;
  protected string $region;
  protected string $organization;
  protected string $plan_name;
  protected int $max_num_cdes;
  protected string $upstream;
  protected string $holder_type;
  protected string $holder_id;
  protected string $owner;
  protected bool $frozen;
  protected ?string $last_frozen_at;

  public function __construct($site_id) {
    $siteinfo = $this->getPantheonSiteInfo($site_id);
    foreach($siteinfo as $key => $value) {
      call_user_func([$this, "set" . str_replace(" ", "", ucwords(str_replace("_", " ", $key))) ], $value);
    }
  }


  /**
   * @param $site_id
   *
   * @return array|null
   * @throws \JsonException
   *
   * @example
   * Array (
   *   [id] => XXXXXXXX-e717-4144-ac38-XXXXXXXXXXXX
   *   [name] => my-drupal-8.x-site
   *   [label] => Drupal 8.x site
   *   [created] => 1569515403
   *   [framework] => drupal8
   *   [region] => United States
   *   [organization] => XXXXXXXX-e717-4144-ac38-XXXXXXXXXXXX
   *   [plan_name] => Performance Xlarge
   *   [max_num_cdes] => 10
   *   [upstream] => XXXXXXXX-e717-4144-ac38-XXXXXXXXXXXX:
   *   git://github.com/pantheon-systems/drops-8.git
   *   [holder_type] => organization
   *   [holder_id] => XXXXXXXX-e717-4144-ac38-XXXXXXXXXXXX
   *   [owner] => XXXXXXXX-e717-4144-ac38-XXXXXXXXXXXX
   *   [frozen] =>
   *   [last_frozen_at] =>
   * )
   */

  function getPantheonSiteInfo($site_id): ?array {
    $command = sprintf('terminus site:info %s --format=json', $site_id);
    exec($command, $output, $status);
    if ($status !== 0) {
      // Only let us know if something went wrong.
      echo $output;
    }
    return ($status === 0) ?
      json_decode(join("", $output), TRUE, 5, JSON_THROW_ON_ERROR) :
      NULL;
  }

  /**
   * @return string
   */
  public function getId(): string {
    return $this->id;
  }

  /**
   * @param string $id
   */
  public function setId(string $id): void {
    $this->id = $id;
  }

  /**
   * @return string
   */
  public function getName(): string {
    return $this->name;
  }

  /**
   * @param string $name
   */
  public function setName(string $name): void {
    $this->name = $name;
  }

  /**
   * @return string
   */
  public function getLabel(): string {
    return $this->label;
  }

  /**
   * @param string $label
   */
  public function setLabel(string $label): void {
    $this->label = $label;
  }

  /**
   * @return string
   */
  public function getCreated(): string {
    return $this->created;
  }

  /**
   * @param string $created
   */
  public function setCreated(string $created): void {
    $this->created = $created;
  }

  /**
   * @return string
   */
  public function getFramework(): string {
    return $this->framework;
  }

  /**
   * @param string $framework
   */
  public function setFramework(string $framework): void {
    $this->framework = $framework;
  }

  /**
   * @return string
   */
  public function getRegion(): string {
    return $this->region;
  }

  /**
   * @param string $region
   */
  public function setRegion(string $region): void {
    $this->region = $region;
  }

  /**
   * @return string
   */
  public function getOrganization(): string {
    return $this->organization;
  }

  /**
   * @param string $organization
   */
  public function setOrganization(string $organization): void {
    $this->organization = $organization;
  }

  /**
   * @return string
   */
  public function getPlanName(): string {
    return $this->plan_name;
  }

  /**
   * @param string $plan_name
   */
  public function setPlanName(string $plan_name): void {
    $this->plan_name = $plan_name;
  }

  /**
   * @return int
   */
  public function getMaxNumCdes(): int {
    return $this->max_num_cdes;
  }

  /**
   * @param int $max_num_cdes
   */
  public function setMaxNumCdes(int $max_num_cdes): void {
    $this->max_num_cdes = $max_num_cdes;
  }

  /**
   * @return string
   */
  public function getUpstream(): string {
    return $this->upstream;
  }

  /**
   * @param string $upstream
   */
  public function setUpstream(string $upstream): void {
    $this->upstream = $upstream;
  }

  /**
   * @return string
   */
  public function getHolderType(): string {
    return $this->holder_type;
  }

  /**
   * @param string $holder_type
   */
  public function setHolderType(string $holder_type): void {
    $this->holder_type = $holder_type;
  }

  /**
   * @return string
   */
  public function getHolderId(): string {
    return $this->holder_id;
  }

  /**
   * @param string $hold_id
   */
  public function setHolderId(string $holder_id): void {
    $this->holder_id = $holder_id;
  }

  /**
   * @return string
   */
  public function getOwner(): string {
    return $this->owner;
  }

  /**
   * @param string $owner
   */
  public function setOwner(string $owner): void {
    $this->owner = $owner;
  }

  /**
   * @return bool
   */
  public function isFrozen(): bool {
    return $this->frozen;
  }

  /**
   * @param bool $frozen
   */
  public function setFrozen(bool $frozen): void {
    $this->frozen = $frozen;
  }

  /**
   * @return string
   */
  public function getLastFrozenAt(): string {
    return $this->last_frozen_at;
  }

  /**
   * @param string $last_frozen_at
   */
  public function setLastFrozenAt(?string $last_frozen_at = null): void {
    $this->last_frozen_at = $last_frozen_at;
  }




}
