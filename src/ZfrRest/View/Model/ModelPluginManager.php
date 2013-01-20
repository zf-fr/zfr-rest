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

namespace ZfrRest\View\Model;

use Zend\ServiceManager\AbstractPluginManager;
use Zend\View\Model\ModelInterface;
use ZfrRest\View\Exception;

/**
 * ModelPluginManager. It allows to retrieve a view model from a format.
 *
 * TODO: Add a Zend\View\Model\XmlModel into Zend Framework 2, currently it's not supported (although
 *       we can serialize data to XML)
 *
 * @license MIT
 * @since   0.0.1
 */
class ModelPluginManager extends AbstractPluginManager
{
    /**
     * @var array
     */
    protected $invokableClasses = array(
        'text/html'              => 'Zend\View\Model\ViewModel',
        'application/xhtml+xml'  => 'Zend\View\Model\ViewModel',
        'application/json'       => 'Zend\View\Model\JsonModel',
        'application/javascript' => 'Zend\View\Model\JsonModel',
    );


    /**
     * {@inheritDoc}
     */
    public function validatePlugin($plugin)
    {
        if ($plugin instanceof ModelInterface) {
            return;
        }

        throw new Exception\RuntimeException(sprintf(
            'Plugin of type %s is invalid; must implement Zend\View\ViewModel',
            (is_object($plugin) ? get_class($plugin) : gettype($plugin))
        ));
    }
}
