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

namespace ZfrRestTest\Http\Parser\Request;

use PHPUnit_Framework_TestCase as TestCase;
use Zend\Http\Request as HttpRequest;
use Zend\ServiceManager\ServiceManager;
use ZfrRest\Http\Parser\Request\BodyParser;
use ZfrRest\Serializer\DecoderPluginManager;
use ZfrRestTest\Util\ServiceManagerFactory;

class BodyParserTest extends TestCase
{
    /**
     * @var BodyParser
     */
    protected $bodyParser;

    /**
     * @var ServiceManager
     */
    protected $serviceManager;


    public function setUp()
    {
        $this->serviceManager = ServiceManagerFactory::getServiceManager();
        $this->bodyParser     = new BodyParser(new DecoderPluginManager());
    }

    public function testAlwaysReturnNullIfNoContentTypeIsSet()
    {
        $request = new HttpRequest();
        $result  = $this->bodyParser->parse($request);

        $this->assertNull($result);
    }

    public function testCanParseJsonContent()
    {
        $request = new HttpRequest();
        $request->getHeaders()->addHeaderLine("Content-Type: application/json");
        $request->setContent('{"food": [{"name": "Escargot", "rating": 16}, {"name": "Tartiflette", "rating": 18}]}');

        $expected['food'] = array(
            array('name' => 'Escargot', 'rating' => 16),
            array('name' => 'Tartiflette', 'rating' => 18)
        );

        $this->assertEquals($expected, $this->bodyParser->parse($request));
    }

    public function testCanParseXmlContent()
    {
        $xml = <<<'EOD'
<?xml version="1.0" encoding="UTF-8" ?>
<menu>
    <food>
        <name>Escargot</name>
        <rating>16</rating>
    </food>

    <food>
        <name>Tartiflette</name>
        <rating>18</rating>
    </food>
</menu>
EOD;

        $request = new HttpRequest();
        $request->getHeaders()->addHeaderLine("Content-Type: application/xml");
        $request->setContent($xml);

        $expected['food'] = array(
            array('name' => 'Escargot', 'rating' => 16),
            array('name' => 'Tartiflette', 'rating' => 18)
        );

        $this->assertEquals($expected, $this->bodyParser->parse($request));
    }

    public function testCanRetrieveBodyParserWithServiceManager()
    {
        $bodyParser = $this->serviceManager->get('ZfrRest\Http\Parser\Request\BodyParser');
        $this->assertInstanceOf('ZfrRest\Http\Parser\Request\BodyParser', $bodyParser);
    }
}
