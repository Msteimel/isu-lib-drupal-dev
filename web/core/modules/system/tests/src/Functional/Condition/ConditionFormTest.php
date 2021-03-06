<?php

namespace Drupal\Tests\system\Functional\Condition;

use Drupal\node\Entity\Node;
use Drupal\Tests\BrowserTestBase;

/**
 * Tests that condition plugins basic form handling is working.
 *
 * Checks condition forms and submission and gives a very cursory check to make
 * sure the configuration that was submitted actually causes the condition to
 * validate correctly.
 *
 * @group Condition
 */
class ConditionFormTest extends BrowserTestBase {

  protected static $modules = ['node', 'condition_test'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Submit the condition_node_type_test_form to test condition forms.
   */
  public function testConfigForm() {
    $this->drupalCreateContentType(['type' => 'page', 'name' => 'Page']);
    $this->drupalCreateContentType(['type' => 'article', 'name' => 'Article']);

    $article = Node::create([
      'type' => 'article',
      'title' => $this->randomMachineName(),
    ]);
    $article->save();

    $this->drupalGet('condition_test');
    $this->assertField('bundles[article]');
    $this->assertField('bundles[page]');
    $this->drupalPostForm(NULL, ['bundles[page]' => 'page', 'bundles[article]' => 'article'], t('Submit'));
    // @see \Drupal\condition_test\FormController::submitForm()
    $this->assertText('Bundle: page');
    $this->assertText('Bundle: article');
    $this->assertText('Executed successfully.', 'The form configured condition executed properly.');
  }

}
