<?php

namespace Drupal\Tests\node\Functional;

use Drupal\Core\Language\LanguageInterface;

/**
 * Tests node type initial language settings.
 *
 * @group node
 */
class NodeTypeInitialLanguageTest extends NodeTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = ['language', 'field_ui'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  protected function setUp(): void {
    parent::setUp();

    $web_user = $this->drupalCreateUser([
      'bypass node access',
      'administer content types',
      'administer node fields',
      'administer node form display',
      'administer node display',
      'administer languages',
    ]);
    $this->drupalLogin($web_user);
  }

  /**
   * Tests the node type initial language defaults, and modifies them.
   *
   * The default initial language must be the site's default, and the language
   * locked option must be on.
   */
  public function testNodeTypeInitialLanguageDefaults() {
    $this->drupalGet('admin/structure/types/manage/article');
    $this->assertTrue($this->assertSession()->optionExists('edit-language-configuration-langcode', LanguageInterface::LANGCODE_SITE_DEFAULT)->isSelected());
    $this->assertSession()->checkboxNotChecked('edit-language-configuration-language-alterable');

    // Tests if the language field cannot be rearranged on the manage fields tab.
    $this->drupalGet('admin/structure/types/manage/article/fields');
    $language_field = $this->xpath('//*[@id="field-overview"]/*[@id="language"]');
    $this->assert(empty($language_field), 'Language field is not visible on manage fields tab.');

    // Verify that language is not selectable on node add page by default.
    $this->drupalGet('node/add/article');
    $this->assertNoField('langcode');

    // Adds a new language and set it as default.
    $edit = [
      'predefined_langcode' => 'hu',
    ];
    $this->drupalPostForm('admin/config/regional/language/add', $edit, t('Add language'));
    $edit = [
      'site_default_language' => 'hu',
    ];
    $this->drupalPostForm('admin/config/regional/language', $edit, t('Save configuration'));

    // Tests the initial language after changing the site default language.
    // First unhide the language selector.
    $edit = [
      'language_configuration[language_alterable]' => TRUE,
    ];
    $this->drupalPostForm('admin/structure/types/manage/article', $edit, t('Save content type'));
    $this->drupalGet('node/add/article');
    // Ensure that the language is selectable on node add page when language
    // not hidden.
    $this->assertField('langcode[0][value]');
    $this->assertTrue($this->assertSession()->optionExists('edit-langcode-0-value', 'hu')->isSelected());

    // Tests if the language field can be rearranged on the manage form display
    // tab.
    $this->drupalGet('admin/structure/types/manage/article/form-display');
    $language_field = $this->xpath('//*[@id="langcode"]');
    $this->assert(!empty($language_field), 'Language field is visible on manage form display tab.');

    // Tests if the language field can be rearranged on the manage display tab.
    $this->drupalGet('admin/structure/types/manage/article/display');
    $language_display = $this->xpath('//*[@id="langcode"]');
    $this->assert(!empty($language_display), 'Language field is visible on manage display tab.');
    // Tests if the language field is hidden by default.
    $this->assertTrue($this->assertSession()->optionExists('edit-fields-langcode-region', 'hidden')->isSelected());

    // Changes the initial language settings.
    $edit = [
      'language_configuration[langcode]' => 'en',
    ];
    $this->drupalPostForm('admin/structure/types/manage/article', $edit, t('Save content type'));
    $this->drupalGet('node/add/article');
    $this->assertTrue($this->assertSession()->optionExists('edit-langcode-0-value', 'en')->isSelected());
  }

  /**
   * Tests language field visibility features.
   */
  public function testLanguageFieldVisibility() {
    // Creates a node to test Language field visibility feature.
    $edit = [
      'title[0][value]' => $this->randomMachineName(8),
      'body[0][value]' => $this->randomMachineName(16),
    ];
    $this->drupalPostForm('node/add/article', $edit, t('Save'));
    $node = $this->drupalGetNodeByTitle($edit['title[0][value]']);
    $this->assertNotEmpty($node, 'Node found in database.');

    // Loads node page and check if Language field is hidden by default.
    $this->drupalGet('node/' . $node->id());
    $language_field = $this->xpath('//div[@id=:id]/div', [
      ':id' => 'field-language-display',
    ]);
    $this->assertTrue(empty($language_field), 'Language field value is not shown by default on node page.');

    // Configures Language field formatter and check if it is saved.
    $edit = [
      'fields[langcode][type]' => 'language',
      'fields[langcode][region]' => 'content',
    ];
    $this->drupalPostForm('admin/structure/types/manage/article/display', $edit, t('Save'));
    $this->drupalGet('admin/structure/types/manage/article/display');
    $this->assertTrue($this->assertSession()->optionExists('edit-fields-langcode-type', 'language')->isSelected());

    // Loads node page and check if Language field is shown.
    $this->drupalGet('node/' . $node->id());
    $language_field = $this->xpath('//div[@id=:id]/div', [
      ':id' => 'field-language-display',
    ]);
    $this->assertFalse(empty($language_field), 'Language field value is shown on node page.');
  }

}
