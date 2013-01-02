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

namespace ZfrRest\Mvc;

use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\Http\Header;
use Zend\Http\Response as HttpResponse;
use Zend\Mvc\MvcEvent;
use ZfrRest\Http\Exception\AbstractHttpException;
use ZfrRest\Http\Exception\Client;

/**
 * HttpExceptionListener
 *
 * @license MIT
 * @since   0.0.1
 */
class HttpExceptionListener implements ListenerAggregateInterface
{
    /**
     * @var \Zend\Stdlib\CallbackHandler[]
     */
    protected $listeners = array();


    /**
     * {@inheritDoc}
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH_ERROR, array($this, 'onDispatchError'), 100);
    }

    /**
     * {@inheritDoc}
     */
    public function detach(EventManagerInterface $events)
    {
        foreach ($this->listeners as $index => $listener) {
            if ($events->detach($listener)) {
                unset($this->listeners[$index]);
            }
        }
    }

    /**
     * Get the exception and optionally set status code and reason message. Note that according to RFC 2617
     * (http://www.ietf.org/rfc/rfc2617.txt), the 401 response message MUST contain a WWW-Authenticate header
     *
     * @param  MvcEvent $e
     * @return array
     */
    public function onDispatchError(MvcEvent $e)
    {
        $response  = $e->getResponse();
        $exception = $e->getParam('exception');

        // We just deal with our Http error codes here !
        if (!$exception instanceof AbstractHttpException || !$response instanceof HttpResponse) {
            return array();
        }

        $e->stopPropagation();

        $response->setStatusCode($exception->getStatusCode());
        $response->setReasonPhrase($exception->getReasonPhrase());

        if ($exception instanceof Client\UnauthorizedException) {
            /** @var $headers \Zend\Http\Headers */
            $headers            = $response->getHeaders();
            $challenge          = $exception->getChallenge();
            $authenticateHeader = Header\WWWAuthenticate::fromString("WWW-Authenticate: $challenge");

            $headers->addHeader($authenticateHeader);
        }

        return $response;
    }
}
