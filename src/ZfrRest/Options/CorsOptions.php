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

namespace ZfrRest\Options;

use Zend\Stdlib\AbstractOptions;

/**
 * CorsOptions
 *
 * @license MIT
 * @author  Florent Blaison <florent.blaison@gmail.com>
 */
class CorsOptions extends AbstractOptions
{
    /**
     * Set the list of allowed origins domain.
     *
     * @var array
     */
    protected $origins;

    /**
     * Set the list of rest method verbs separate by a comma.
     *
     * @var string
     */
    protected $allowedMethods;

    /**
     * Set the list of headers separate by a comma.
     *
     * @var string
     */
    protected $allowedHeaders;

    /**
     * Set the max age of the authorize request in seconds.
     *
     * @var int
     */
    protected $maxAge;

    /**
     * Set the list of exposed headers separate by a comma.
     *
     * @var string
     */
    protected $exposedHeaders;

    /**
     * Allow cors request with credential.
     *
     * @var bool
     */
    protected $allowedCredentials;

    /**
     * @param  array $origins
     * @return void
     */
    public function setOrigins(array $origins)
    {
        $this->origins = $origins;
    }

    /**
     * @return array
     */
    public function getOrigins()
    {
        return $this->origins;
    }

    /**
     * @param string $allowedMethods
     * @return void
     */
    public function setAllowedMethods($allowedMethods)
    {
        $this->allowedMethods = (string) $allowedMethods;
    }

    /**
     * @return string
     */
    public function getAllowedMethods()
    {
        return $this->allowedMethods;
    }

    /**
     * @param string $allowedHeaders
     * @return void
     */
    public function setAllowedHeaders($allowedHeaders)
    {
        $this->allowedHeaders = (string) $allowedHeaders;
    }

    /**
     * @return string
     */
    public function getAllowedHeaders()
    {
        return $this->allowedHeaders;
    }

    /**
     * @param int $maxAge
     * @return void
     */
    public function setMaxAge($maxAge)
    {
        $this->maxAge = (int) $maxAge;
    }

    /**
     * @return int
     */
    public function getMaxAge()
    {
        return $this->maxAge;
    }

    /**
     * @param string $exposedHeaders
     * @return void
     */
    public function setExposedHeaders($exposedHeaders)
    {
        $this->exposedHeaders = (string) $exposedHeaders;
    }

    /**
     * @return string
     */
    public function getExposedHeaders()
    {
        return $this->exposedHeaders;
    }

    /**
     * @param bool $allowedCredentials
     * @return void
     */
    public function setAllowedCredentials($allowedCredentials)
    {
        $this->allowedCredentials = (bool) $allowedCredentials;
    }

    /**
     * @return boolean
     */
    public function getAllowedCredentials()
    {
        return $this->allowedCredentials;
    }
}
