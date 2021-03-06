<?php

use Diversity\Component;
use Diversity\Factory;
use Diversity\Collection;

class CollectionTest extends PHPUnit_Framework_TestCase {
  static private $factory;

  static public function setUpBeforeClass() {
    self::$factory = new Factory(
      array(
        'archive'       => FIXTURES . 'component_archive_3' . DS,
        'archive_url'   => 'http://foo.bar/',
      )
    );
  }

  public function testRenderScriptTags() {
    $test1 = self::$factory->get('test1');
    $collection = new Collection;

    $collection->add($test1);

    $scripts_html = $collection->renderScriptTags();
    $this->assertRegExp('$http://foo.bar/test1/1.0.0/test.js$', $scripts_html);
  }

  public function testRenderExternalScriptTags() {
    $test2 = self::$factory->get('test2');
    $collection = new Collection;

    $collection->add($test2);

    $scripts_html = $collection->renderScriptTags();
    $this->assertRegExp('$http://external.site/my.js$', $scripts_html);
  }

  public function testRenderStyleTags() {
    $test1 = self::$factory->get('test1');
    $collection = new Collection;

    $collection->add($test1);

    $styles_html = '<head>' . $collection->renderStyleTags() . '</head>';
    $this->assertContains('http://foo.bar/test1/1.0.0/test.css', $styles_html);
  }

  public function testRenderMultipleStyleTags() {
    $test3 = self::$factory->get('test3');
    $collection = new Collection;

    $collection->add($test3);

    $styles_html = '<head>' . $collection->renderStyleTags() . '</head>';
    $this->assertContains('http://foo.bar/test3/2.3.4/foo.css', $styles_html);
    $this->assertContains('http://foo.bar/test3/2.3.4/bar.css', $styles_html);
    $this->assertContains('//cdn.js/baz.css', $styles_html);
  }

  public function testNeedsAngular() {
    $factory = new Factory(
      array('archive' => FIXTURES . 'component_archive_1' . DS, 'archive_url' => 'dummy')
    );

    $collection = new Collection;

    // An empty collection won't need angular.
    $this->assertFalse($collection->needsAngular());

    // Component test_1 has it's own andular module.
    $test_1 = $factory->get('test_1');
    $collection->add($test_1);
    $this->assertTrue($collection->needsAngular());

    $collection2 = new Collection;

    // Component test_3 depends on test_1 that needs angular.
    $test_3 = $factory->get('test_3');
    $collection->add($test_3);
    $this->assertTrue($collection->needsAngular());
  }

  public function testAngularBootstrap() {
    $factory = new Factory(
      array('archive' => FIXTURES . 'component_archive_1' . DS, 'archive_url' => 'dummy')
    );
    $collection = new Collection;

    $test_5 = $factory->get('test_5');
    $collection->add($test_5);

    // test_5 depends on test_1, so test_1 should be bootstrapped first.
    $this->assertEquals(
      'angular.bootstrap(document, ["testmodule.test1","testmodule.test5"]);' . "\n",
      $collection->renderAngularBootstrap()
    );
  }
}
