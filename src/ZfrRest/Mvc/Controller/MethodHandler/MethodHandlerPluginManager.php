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

namespace ZfrRest\Mvc\Controller\MethodHandler;

use Zend\ServiceManager\AbstractPluginManager;
use ZfrRest\Mvc\Exception\RuntimeException;

/**
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 * @licence MIT
 * @method  MethodHandlerInterface get($name)
 */
class MethodHandlerPluginManager extends AbstractPluginManager
{
    /**
     * @var array
     */
    protected $invokableClasses = array(
        'delete'  => 'ZfrRest\Mvc\Controller\MethodHandler\DeleteHandler',
        'get'     => 'ZfrRest\Mvc\Controller\MethodHandler\GetHandler',
        'options' => 'ZfrRest\Mvc\Controller\MethodHandler\OptionsHandler'
    );

    /**
     * @var array
     */
    protected $factories = array(
        'post' => 'ZfrRest\Factory\PostHandlerFactory',
        'put'  => 'ZfrRest\Factory\PutHandlerFactory'
    );

    /**
     * Whether or not to auto-add a class as an invokable class if it exists
     *
     * @var bool
     */
    protected $autoAddInvokableClass = false;

    /**
     * {@inheritDoc}
     */
    public function validatePlugin($plugin)
    {
        if ($plugin instanceof MethodHandlerInterface) {
            return; // we're okey
        }

        throw new RuntimeException(sprintf(
            'Method handlers must implement "ZfrRest\Mvc\Controller\MethodHandler\MethodHandlerInterface", "%s" given',
            is_object($plugin) ? get_class($plugin) : gettype($plugin)
        ));
    }
}
