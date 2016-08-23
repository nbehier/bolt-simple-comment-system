<?php

namespace Bolt\Extension\Leskis\BoltSimpleCommentSystem\Tests;

use Bolt\Tests\BoltUnitTest;
use Bolt\Extension\Leskis\BoltSimpleCommentSystem\BoltSimpleCommentSystemExtension;

/**
 * BoltSimpleCommentSystem testing class.
 *
 * @author Nicolas Béhier-Dévigne
 */
class ExtensionTest extends BoltUnitTest
{
    /**
     * Ensure that the BoltSimpleCommentSystem extension loads correctly.
     */
    public function testExtensionBasics()
    {
        $app = $this->getApp(false);
        $extension = new BoltSimpleCommentSystemExtension($app);

        $name = $extension->getName();
        $this->assertSame($name, 'Simple comment system');
        $this->assertInstanceOf('\Bolt\Extension\ExtensionInterface', $extension);
    }

    public function testExtensionComposerJson()
    {
        $composerJson = json_decode(file_get_contents(dirname(__DIR__) . '/composer.json'), true);

        // Check that the 'bolt-class' key correctly matches an existing class
        $this->assertArrayHasKey('bolt-class', $composerJson['extra']);
        $this->assertTrue(class_exists($composerJson['extra']['bolt-class']));

        // Check that the 'bolt-assets' key points to the correct directory
        $this->assertArrayHasKey('bolt-assets', $composerJson['extra']);
        $this->assertSame('web', $composerJson['extra']['bolt-assets']);
    }
}
