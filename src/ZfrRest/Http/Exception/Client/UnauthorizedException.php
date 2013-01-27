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

namespace ZfrRest\Http\Exception\Client;

use ZfrRest\Http\Exception\ClientException;
use Zend\Http\Header;
use Zend\Http\Response as HttpResponse;

/**
 * UnauthorizedException
 *
 * @license MIT
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 */
class UnauthorizedException extends ClientException
{
    /**
     * @var string
     */
    protected $message = 'You are not authorized to access to the requested resource';

    /**
     * @var string
     */
    protected $challenge = 'Basic';


    /**
     * @param string $message
     * @param string $challenge
     */
    public function __construct($message = '', $challenge = '')
    {
        if (!empty($challenge)) {
            $this->setChallenge($challenge);
        }

        parent::__construct(401, $message);
    }

    /**
     * Set the challenge method used for authentication that will appear in the "WWW-Authenticate" header
     *
     * @param  string $challenge
     * @return UnauthorizedException
     */
    public function setChallenge($challenge)
    {
        $this->challenge = $challenge;
        return $this;
    }

    /**
     * Get the challenge method used for authentication that will appear in the "WWW-Authenticate" header
     *
     * @return string
     */
    public function getChallenge()
    {
        return $this->challenge;
    }

    /**
     * According to RFC 2617 (http://www.ietf.org/rfc/rfc2617.txt), the 401 response message MUST
     * contain a WWW-Authenticate header
     *
     * {@inheritDoc}
     */
    public function prepareResponse(HttpResponse $response)
    {
        parent::prepareResponse($response);

        $headers = $response->getHeaders();
        if ($headers->has('WWWAuthenticate')) {
            return;
        }

        $challenge          = $this->getChallenge();
        $authenticateHeader = Header\WWWAuthenticate::fromString("WWW-Authenticate: $challenge");

        $headers->addHeader($authenticateHeader);
    }
}
