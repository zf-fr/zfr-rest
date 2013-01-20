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

namespace ZfrRest\Mime;

/**
 * This is a simple class that map a format (eg. json, xml, html...) to a list of MIME-Types. It
 * contains some default matches, but of course all of them can be overridden
 *
 * @license MIT
 * @since   0.0.1
 */
class FormatDecoder
{
    /**
     * @var array
     */
    protected $matches = array(
        'html' => array('text/html', 'application/xhtml+xml'),
        'json' => array('application/json', 'application/javascript'),
        'xml'  => array('application/xml')
    );


    /**
     * Add a new MIME-Type for a given format
     *
     * @param  string $format
     * @param  string $mimeType
     * @return void
     */
    public function add($format, $mimeType)
    {
        if (!isset($this->matches[$format])) {
            $this->matches[$format][] = $mimeType;
        } else {
            // Remove the MIME-Type from any other format to allow override
            $this->remove($mimeType);
            $this->matches[$format][] = $mimeType;
        }
    }

    /**
     * Remove the MIME-Type so that it is not matched to any format anymore
     *
     * @param  string $mimeType
     * @return void
     */
    public function remove($mimeType)
    {
        foreach ($this->matches as $format => &$mimeTypes) {
            $key = array_search($mimeType, $mimeTypes);

            if ($key !== false) {
                unset($mimeTypes[$key]);
                return;
            }
        }
    }

    /**
     * Determine if a given MIME-Type is matched to a format
     *
     * @param  string $mimeType
     * @return bool
     */
    public function has($mimeType)
    {
        foreach ($this->matches as $format => $mimeTypes) {
            if (in_array($mimeType, $mimeTypes)) {
                return true;
            }
        }

        return false;
    }

    /**
     * For a given MIME-Type, get the format that is matched
     *
     * @param  string      $mimeType
     * @return string|null
     */
    public function decode($mimeType)
    {
        foreach ($this->matches as $format => $mimeTypes) {
            if (in_array($mimeType, $mimeTypes)) {
                return $format;
            }
        }

        return null;
    }
}
