<?php

namespace Kolyunya\Codeception\Tests\Lib\MarkupValidator;

use Codeception\Lib\ModuleContainer;
use PHPUnit\Framework\TestCase;
use Kolyunya\Codeception\Lib\MarkupValidator\DefaultMarkupProvider;

class DefaultMarkupProviderTest extends TestCase
{
    /**
     * @var ModuleContainer
     */
    private $moduleContainer;

    /**
     * @var DefaultMarkupProvider
     */
    private $markupProvider;

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        $this->moduleContainer = $this
            ->getMockBuilder('Codeception\Lib\ModuleContainer')
            ->disableOriginalConstructor()
            ->setMethods(array(
                'hasModule',
                'getModule',
            ))
            ->getMock()
        ;

        $this->markupProvider = $this
            ->getMockBuilder('Kolyunya\Codeception\Lib\MarkupValidator\DefaultMarkupProvider')
            ->setConstructorArgs(array(
                $this->moduleContainer,
                array(
                    'provider' => array(
                        'class' => 'Kolyunya\Codeception\Lib\MarkupValidator\DefaultMarkupProvider',
                        'config' => array(),
                    ),
                    'validator' => array(
                        'class' => 'Kolyunya\Codeception\Lib\MarkupValidator\W3CMarkupValidator',
                        'config' => array(),
                    ),
                    'reporter' => array(
                        'class' => 'Kolyunya\Codeception\Lib\MarkupValidator\DefaultMarkupReporter',
                        'config' => array(
                            'ignoredErrors' => array(),
                            'ignoreWarnings' => false,
                        ),
                    ),
                ),
            ))
            ->enableProxyingToOriginalMethods()
            ->getMock()
        ;
    }

    /**
     * {@inheritDoc}
     */
    public function tearDown()
    {
    }

    public function testWithNoPhpBrowserNoWebDriver()
    {
        $this->setExpectedException('Exception', 'Unable to obtain current page markup.');
        $this->markupProvider->getMarkup();
    }

    public function testWithPhpBrowser()
    {
        $expectedMarkup =
            <<<HTML
                <!DOCTYPE HTML>
                <html>
                    <head>
                        <title>
                            A valid page.
                        </title>
                    </head>
                </html>
HTML
        ;

        $phpBrowser = $this
            ->getMockBuilder('Codeception\Module\PhpBrowser')
            ->disableOriginalConstructor()
            ->setMethods(array(
                '_getResponseContent',
            ))
            ->getMock()
        ;
        $phpBrowser
            ->method('_getResponseContent')
            ->will($this->returnValue($expectedMarkup))
        ;

        $this->moduleContainer
            ->method('hasModule')
            ->will($this->returnValueMap(array(
                array('PhpBrowser', true)
            )))
        ;
        $this->moduleContainer
            ->method('getModule')
            ->will($this->returnValueMap(array(
                array('PhpBrowser', $phpBrowser)
            )))
        ;

        $actualMarkup = $this->markupProvider->getMarkup();
        $this->assertEquals($expectedMarkup, $actualMarkup);
    }

    public function testWithWebDrive()
    {
        $expectedMarkup =
            <<<HTML
                <!DOCTYPE HTML>
                <html>
                    <head>
                        <title>
                            A valid page.
                        </title>
                    </head>
                </html>
HTML
        ;

        $remoteWebDriver = $this
            ->getMockBuilder('Facebook\WebDriver\Remote\RemoteWebDriver')
            ->disableOriginalConstructor()
            ->setMethods(array(
                'getPageSource'
            ))
            ->getMock()
        ;
        $remoteWebDriver
            ->method('getPageSource')
            ->will($this->returnValue($expectedMarkup))
        ;

        $webDriver = $this
            ->getMockBuilder('Codeception\Module\WebDriver')
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $webDriver->webDriver = $remoteWebDriver;

        $this->moduleContainer
            ->method('hasModule')
            ->will($this->returnValueMap(array(
                array('WebDriver', true)
            )))
        ;
        $this->moduleContainer
            ->method('getModule')
            ->will($this->returnValueMap(array(
                array('WebDriver', $webDriver)
            )))
        ;

        $actualMarkup = $this->markupProvider->getMarkup();
        $this->assertEquals($expectedMarkup, $actualMarkup);
    }
}