<?php

namespace Drupal\Tests\migrate_drupal_ui\Functional\d7;

use Drupal\Tests\migrate_drupal_ui\Functional\MigrateUpgradeExecuteTestBase;

/**
 * Tests Drupal 7 Id Conflict page.
 *
 * @group migrate_drupal_ui
 */
class IdConflictTest extends MigrateUpgradeExecuteTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'file',
    'language',
    'config_translation',
    'content_translation',
    'migrate_drupal_ui',
    'telephone',
    'aggregator',
    'book',
    'forum',
    'rdf',
    'statistics',
    // Required for translation migrations.
    'migrate_drupal_multilingual',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->loadFixture(drupal_get_path('module', 'migrate_drupal') . '/tests/fixtures/drupal7.php');
  }

  /**
   * {@inheritdoc}
   */
  protected function getSourceBasePath() {
    return __DIR__ . '/files';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntityCounts() {
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntityCountsIncremental() {
  }

  /**
   * {@inheritdoc}
   */
  protected function getAvailablePaths() {
  }

  /**
   * {@inheritdoc}
   */
  protected function getMissingPaths() {
  }

  /**
   * Tests ID Conflict form.
   */
  public function testMigrateUpgradeExecute() {
    $this->drupalGet('/upgrade');
    $session = $this->assertSession();
    $session->responseContains("Upgrade a site by importing its files and the data from its database into a clean and empty new install of Drupal $this->destinationSiteVersion.");

    $this->drupalPostForm(NULL, [], t('Continue'));
    $session->pageTextContains('Provide credentials for the database of the Drupal site you want to upgrade.');
    $session->fieldExists('mysql[host]');

    // Get valid credentials.
    $edits = $this->translatePostValues($this->getCredentials());

    // Start the upgrade process.
    $this->drupalGet('/upgrade');
    $session->responseContains("Upgrade a site by importing its files and the data from its database into a clean and empty new install of Drupal $this->destinationSiteVersion.");

    $this->drupalPostForm(NULL, [], t('Continue'));
    $session->pageTextContains('Provide credentials for the database of the Drupal site you want to upgrade.');
    $session->fieldExists('mysql[host]');

    $this->drupalPostForm(NULL, $edits, t('Review upgrade'));
    $entity_types = [
      'block_content',
      'menu_link_content',
      'file',
      'taxonomy_term',
      'user',
      'comment',
      'node',
    ];
    $this->assertIdConflict($session, $entity_types);
  }

}
