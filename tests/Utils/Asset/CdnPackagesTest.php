<?php
/**
 * Copyright © 2017, Ambroise Maupate and Julien Blanchet
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is furnished
 * to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 *
 * Except as contained in this notice, the name of the ROADIZ shall not
 * be used in advertising or otherwise to promote the sale, use or other dealings
 * in this Software without prior written authorization from Ambroise Maupate and Julien Blanchet.
 *
 * @file CdnPackagesTest.php
 * @author Ambroise Maupate
 */

use RZ\Roadiz\Core\Entities\Document;
use RZ\Roadiz\Core\Entities\Setting;
use RZ\Roadiz\Core\Kernel;
use RZ\Roadiz\Tests\DefaultThemeDependentCase;
use RZ\Roadiz\Utils\Asset\Packages;
use Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class CdnPackagesTest extends DefaultThemeDependentCase
{
    /**
     * @return Request
     */
    public static function getMockRequest()
    {
        return new Request([], [], [], [], [], [
            'REQUEST_URI' => '/',
            'SCRIPT_NAME' => '/index.php',
            'SCRIPT_FILENAME' => '/var/www/test/index.php',
            'PHP_SELF' => '/test/index.php',
            'REQUEST_METHOD' => 'GET',
            'SERVER_NAME' => 'localhost',
            'SERVER_PORT' => 80,
            'HTTP_HOST' => 'localhost',
            'REDIRECT_URL' => '/',
            'PATH_INFO' => '/test/',
            'PATH_TRANSLATED' => '/test/',
            'DOCUMENT_ROOT' => '/var/www/test/',
            'SERVER_PROTOCOL' => 'HTTP/1.1',
            'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'HTTP_ACCEPT_LANGUAGE' => 'en-us,en;q=0.5',
            'HTTP_ACCEPT_CHARSET' => 'ISO-8859-1,utf-8;q=0.7,*;q=0.7',
        ]);
    }

    public function setUp()
    {
        /** @var Setting $setting */
        $setting = static::getManager()
            ->getRepository('RZ\Roadiz\Core\Entities\Setting')
            ->findOneByName('static_domain_name');
        $setting->setValue('static.localhost');
        static::getManager()->flush();
    }

    public function testUseStaticDomain()
    {
        $requestStack = new RequestStack();
        $requestStack->push(static::getMockRequest());
        $packages = new Packages(new EmptyVersionStrategy(), $requestStack, Kernel::getInstance(), 'static.localhost');

        $this->assertEquals(true, $packages->useStaticDomain());
    }

    public function testGetUrl()
    {
        $requestStack = new RequestStack();
        $requestStack->push(static::getMockRequest());
        $packages = new Packages(new EmptyVersionStrategy(), $requestStack, Kernel::getInstance(), 'static.localhost');

        $this->assertEquals(
            'https://static.localhost/files/some-custom-file',
            $packages->getUrl('/some-custom-file', Packages::ABSOLUTE_DOCUMENTS)
        );

        $this->assertEquals(
            'https://static.localhost/files/some-custom-file',
            $packages->getUrl('some-custom-file', Packages::ABSOLUTE_DOCUMENTS)
        );

        $this->assertEquals(
            'https://static.localhost/files/folder/some-custom-file',
            $packages->getUrl('/folder/some-custom-file', Packages::ABSOLUTE_DOCUMENTS)
        );

        $this->assertEquals(
            'https://static.localhost/files/folder/some-custom-file',
            $packages->getUrl('folder/some-custom-file', Packages::ABSOLUTE_DOCUMENTS)
        );
    }

    /**
     * @dataProvider documentUrlWithBasePathProvider
     * @param Document $document
     * @param array $options
     * @param $absolute
     * @param $expectedUrl
     */
    public function testDocumentUrlWithBasePath(Document $document, array $options, $absolute, $expectedUrl)
    {
        $requestStack = new RequestStack();
        $requestStack->push(static::getMockRequest());
        $packages = new Packages(new EmptyVersionStrategy(), $requestStack, Kernel::getInstance(), 'static.localhost');
        $viewer = $document->getViewer();
        $viewer->setPackages($packages);
        $this->assertEquals($expectedUrl, $viewer->getDocumentUrlByArray($options, $absolute));
    }

    /**
     * @return array
     */
    public function documentUrlWithBasePathProvider()
    {
        $document1 = new Document();
        $document1->setFolder('folder');
        $document1->setFilename('file.jpg');
        $document1->setMimeType('image/jpeg');

        return [
            [
                $document1,
                [
                    'quality' => 80
                ],
                false,
                'https://static.localhost/assets/q80/folder/file.jpg',
            ],
            [
                $document1,
                [
                    'quality' => 90,
                    'width' => 600,
                ],
                true,
                'https://static.localhost/assets/w600-q90/folder/file.jpg',
            ],
            [
                $document1,
                [
                    'noProcess' => true,
                ],
                true,
                'https://static.localhost/files/folder/file.jpg',
            ],
            [
                $document1,
                [
                    'noProcess' => true,
                ],
                false,
                'https://static.localhost/files/folder/file.jpg',
            ]
        ];
    }
}