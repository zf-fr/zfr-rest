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

namespace ZfrRest\Http\Request\Parser;

use Zend\Http\Request as HttpRequest;
use Zend\Stdlib\MessageInterface;
use ZfrRest\Http\Parser\AbstractParser;
use ZfrRest\Mime\FormatDecoder;
use ZfrRest\Serializer\EncoderPluginManager;

/**
 * Parse the body of a request according to the Content-Type header
 *
 * @license MIT
 * @since   0.0.1
 */
class BodyParser extends AbstractParser
{
    /**
     * @var FormatDecoder
     */
    protected $formatDecoder;


    /**
     * Constructor
     *
     * @param EncoderPluginManager $pluginManager
     * @param FormatDecoder        $formatDecoder
     */
    public function __construct(EncoderPluginManager $pluginManager, FormatDecoder $formatDecoder)
    {
        parent::__construct($pluginManager);
        $this->formatDecoder = $formatDecoder;
    }

    /**
     * Parse the body
     *
     * @param  MessageInterface $request
     * @return array|null
     */
    public function parse(MessageInterface $request)
    {
        if (!$request instanceof HttpRequest) {
            return null;
        }

        $header = $request->getHeader('Content-Type', null);
        if ($header === null) {
            return null;
        }

        $mimeType = $header->getFieldValue();
        $format   = $this->formatDecoder->decode($mimeType);
        $content  = $request->getContent();

        $encoder = $this->encoderPluginManager->get($format);

        return $encoder->decode($content, $format);
    }
}
