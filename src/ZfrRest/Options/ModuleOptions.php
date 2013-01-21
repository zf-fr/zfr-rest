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
 * ModuleOptions
 *
 * @license MIT
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 */
class ModuleOptions extends AbstractOptions
{
    /**
     * Turn off strict options mode
     */
    protected $__strictMode__ = false;

    /**
     * If this listener is registered (true by default), it will listen on MvcEvent::EVENT_DISPATCH_ERROR,
     * and allow to automatically set Response code and message if a ZfrRest\Http exception is thrown
     *
     * @var bool
     */
    protected $registerHttpExceptionListener;

    /**
     * If this listener is registered (true by default), it will listen on both MvcEvent::EVENT_DISPATCH and
     * MvcEvent::EVENT_DISPATCH_ERROR to select the appropriate ModelInterface instance according to the
     * Accept-Header value
     *
     * @var bool
     */
    protected $registerSelectModelListener;

    /**
     * If this listener is registered (false by default), it will listen on MvcEvent::EVENT_DISPATCH and
     * check the existence of a specific header that allow to override Http method
     *
     * @var bool
     */
    protected $registerHttpMethodOverrideListener;

    /**
     * @param  boolean $registerHttpExceptionListener
     * @return void
     */
    public function setRegisterHttpExceptionListener($registerHttpExceptionListener)
    {
        $this->registerHttpExceptionListener = (bool) $registerHttpExceptionListener;
    }

    /**
     * @return boolean
     */
    public function getRegisterHttpExceptionListener()
    {
        return $this->registerHttpExceptionListener;
    }

    /**
     * @param  boolean $registerHttpMethodOverrideListener
     * @return void
     */
    public function setRegisterHttpMethodOverrideListener($registerHttpMethodOverrideListener)
    {
        $this->registerHttpMethodOverrideListener = (bool) $registerHttpMethodOverrideListener;
    }

    /**
     * @return boolean
     */
    public function getRegisterHttpMethodOverrideListener()
    {
        return $this->registerHttpMethodOverrideListener;
    }

    /**
     * @param  boolean $registerSelectModelListener
     * @return void
     */
    public function setRegisterSelectModelListener($registerSelectModelListener)
    {
        $this->registerSelectModelListener = (bool) $registerSelectModelListener;
    }

    /**
     * @return boolean
     */
    public function getRegisterSelectModelListener()
    {
        return $this->registerSelectModelListener;
    }
}
