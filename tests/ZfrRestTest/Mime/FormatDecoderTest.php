<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license.
 */

namespace ZfrRestTest\Mime;

use PHPUnit_Framework_TestCase as TestCase;
use ReflectionClass;
use ZfrRest\Mime\FormatDecoder;

class FormatDecoderTest extends TestCase
{
    /**
     * @var FormatDecoder
     */
    protected $formatDecoder;

    public function setUp()
    {
        $this->formatDecoder = new FormatDecoder();
    }

    public function defaultMatches()
    {
        return array(
            array('text/html', 'html'),
            array('application/xhtml+xml', 'html'),
            array('application/json', 'json'),
            array('application/javascript', 'json'),
            array('application/xml', 'xml')
        );
    }

    /**
     * @dataProvider defaultMatches
     */
    public function testHaveSaneDefaults($mimeType, $expectedFormat)
    {
        $format = $this->formatDecoder->decode($mimeType);
        $this->assertEquals($expectedFormat, $format);
    }

    public function testAddAnExistingMimeTypeDoesNotAddItTwice()
    {
        $this->formatDecoder->add('html', 'funny/mime-type');
        $this->formatDecoder->add('html', 'funny/mime-type');

        $refl         = new ReflectionClass('ZfrRest\Mime\FormatDecoder');
        $reflProperty = $refl->getProperty('matches');
        $reflProperty->setAccessible(true);

        $matches = $reflProperty->getValue($this->formatDecoder);
        $matches = $matches['html'];
        $count   = array_count_values($matches);

        $this->assertEquals(1, $count['funny/mime-type']);
    }

    public function testCanRemoveMimeType()
    {
        $this->formatDecoder->remove('text/html');
        $this->assertFalse($this->formatDecoder->has('text/html'));
    }

    public function testAddAnExistingMimeTypeToAnotherFormatRemoveItFromOlderFormat()
    {
        // Move text/html to json format
        $this->formatDecoder->add('json', 'text/html');

        $refl         = new ReflectionClass('ZfrRest\Mime\FormatDecoder');
        $reflProperty = $refl->getProperty('matches');
        $reflProperty->setAccessible(true);

        $matches       = $reflProperty->getValue($this->formatDecoder);
        $htmlMimeTypes = $matches['html'];
        $jsonMimeTypes = $matches['json'];

        $this->assertFalse(in_array('text/html', $htmlMimeTypes));
        $this->assertTrue(in_array('text/html', $jsonMimeTypes));
    }

    public function testAssertUnknownMimeTypeReturnNullFormat()
    {
        $format = $this->formatDecoder->decode('lolcatz');
        $this->assertNull($format);
    }
}
