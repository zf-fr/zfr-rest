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

use Zend\Stdlib\MessageInterface;
use Zend\Http\Request as HttpRequest;
use ZfrRest\Http\Parser\AbstractParser;
use ZfrRest\Mime\FormatDecoder;
use ZfrRest\Mime\FormatDecoderAwareInterface;

/**
 * Parse the body of a request according to the Content-Type header
 *
 * @license MIT
 * @since   0.0.1
 */
class BodyParser extends AbstractParser implements FormatDecoderAwareInterface
{
    /**
     * @var FormatDecoder
     */
    protected $formatDecoder;


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
        $format   = $this->getFormatDecoder()->decode($mimeType);

        $content = $request->getContent();

        return $this->getDecoder()->decode($content, $format);
    }

    /**
     * {@inheritDoc}
     */
    public function setFormatDecoder(FormatDecoder $formatDecoder)
    {
        $this->formatDecoder = $formatDecoder;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getFormatDecoder()
    {
        return $this->getFormatDecoder();
    }
}
