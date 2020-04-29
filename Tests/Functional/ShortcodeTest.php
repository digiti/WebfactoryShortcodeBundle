<?php

namespace Webfactory\ShortcodeBundle\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Abstract template for common shortcode tests.
 */
abstract class ShortcodeTest extends WebTestCase
{
    /**
     * @return string name of the shortcode to test.
     */
    abstract protected function getShortcodeToTest(): string;

    protected function setUp(): void
    {
        parent::setUp();

        if ('' === $this->getShortcodeToTest() || null === $this->getShortcodeToTest()) {
            throw new \PHPUnit_Framework_IncompleteTestError(
                'Albeit being a '.__CLASS__.', '.\get_called_class().' does not define a shortcode to test.'
            );
        }
    }

    protected function crawlRenderedExample(string $customParameters = null): Crawler
    {
        $urlWithRenderedExample = $this->getUrlWithRenderedExample($customParameters);

        $client = static::createClient();
        $crawlerOnRenderedExamplePage = $client->request('GET', $urlWithRenderedExample);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $crawlerOnRenderedExample = $crawlerOnRenderedExamplePage->filter('#rendered-example');
        if (0 === $crawlerOnRenderedExample->count()) {
            throw new \PHPUnit_Framework_ExpectationFailedException(
                'No rendered example found for shortcode "'.$this->shortcode.'"'
            );
        }

        return $crawlerOnRenderedExample;
    }

    protected function assertHttpStatusCodeWhenCrawlingRenderedExample(int $expectedStatusCode, string $customParameters = null): Crawler
    {
        $urlWithRenderedExample = $this->getUrlWithRenderedExample($customParameters);

        $client = static::createClient();
        $crawlerOnRenderedExamplePage = $client->request('GET', $urlWithRenderedExample);
        $this->assertEquals($expectedStatusCode, $client->getResponse()->getStatusCode());
    }

    protected function getUrlWithRenderedExample(string $customParameters = null): string
    {
        static::bootKernel();

        $urlParameters = ['shortcode' => $this->getShortcodeToTest()];
        if ($customParameters) {
            $urlParameters['customParameters'] = $customParameters;
        }

        return static::$kernel
            ->getContainer()
            ->get('router')
            ->generate('webfactory.shortcode.guide-detail', $urlParameters);
    }
}
