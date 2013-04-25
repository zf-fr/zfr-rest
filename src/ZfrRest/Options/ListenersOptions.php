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
 * ListenersOptions
 *
 * @license MIT
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 */
class ListenersOptions extends AbstractOptions
{
    /**
     * If this listener is registered, then any exceptions that could be thrown in controller is serialized
     * to the proper format extracted from Content-Type header
     *
     * @var bool
     */
    protected $registerHttpException;

    /**
     * If this listener is registered, it will check if the request contains a header "X-HTTP-Method-Override".
     * This header allows to change the HTTP verb. This is useful in some contexts (for instance, some companies'
     * proxies only allow GET and POST methods)
     *
     * @var bool
     */
    protected $registerHttpMethodOverride;

    /**
     * If this listener is registered, it allows you to return an object (an entity, for instance) from a
     * controller's action, and automatically create payload from its content. Please note that if you deactivate
     * this listener, you MUST NOT return a resource (entity...) from your controller, but an array or Model
     *
     * @var bool
     */
    protected $registerCreateResourcePayload;

    /**
     * If this listener is registered (it is by default), please note that if you deactivate this listener,
     * then you need to return a concrete ModelInterface object (ViewModel, JsonModel...) from your actions,
     * or manually use the AcceptableViewModelSelector to return the right model according to Content-Type
     *
     * @var bool
     */
    protected $registerSelectModel;

    /**
     * @param  bool $registerHttpException
     * @return void
     */
    public function setRegisterHttpException($registerHttpException)
    {
        $this->registerHttpException = (bool) $registerHttpException;
    }

    /**
     * @return bool
     */
    public function getRegisterHttpException()
    {
        return $this->registerHttpException;
    }

    /**
     * @param  bool $registerHttpMethodOverride
     * @return void
     */
    public function setRegisterHttpMethodOverride($registerHttpMethodOverride)
    {
        $this->registerHttpMethodOverride = (bool) $registerHttpMethodOverride;
    }

    /**
     * @return bool
     */
    public function getRegisterHttpMethodOverride()
    {
        return $this->registerHttpMethodOverride;
    }

    /**
     * @param  bool $registerCreateResourcePayload
     * @return void
     */
    public function setRegisterCreateResourcePayload($registerCreateResourcePayload)
    {
        $this->registerCreateResourcePayload = $registerCreateResourcePayload;
    }

    /**
     * @return bool
     */
    public function getRegisterCreateResourcePayload()
    {
        return $this->registerCreateResourcePayload;
    }

    /**
     * @param  bool $registerSelectModel
     * @return void
     */
    public function setRegisterSelectModel($registerSelectModel)
    {
        $this->registerSelectModel = (bool) $registerSelectModel;
    }

    /**
     * @return bool
     */
    public function getRegisterSelectModel()
    {
        return $this->registerSelectModel;
    }
}
