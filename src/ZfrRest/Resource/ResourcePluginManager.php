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

namespace ZfrRest\Resource;

use Doctrine\Common\Persistence\ObjectManager;
use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\Exception;
use ZfrRest\Exception\RuntimeException;

/**
 * Plugin manager that allows to create object repository, without having to define a factory
 * for each entity. This is used also to improve performance, so that the router can lazy-load
 * the creation of repositories
 *
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 * @licence MIT
 */
class ResourcePluginManager extends AbstractPluginManager
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @param ObjectManager $objectManager
     */
    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Get the repository
     *
     * @param  string $name
     * @return object
     */
    public function get($name)
    {
        // First check if an explicit resource was set
        if ($this->has($name)) {
            return $this->get($name);
        }

        // Otherwise, fallback to the object manager
        return $this->objectManager->getRepository($name);
    }

    /**
     * {@inheritDoc}
     */
    public function validatePlugin($plugin)
    {
        if (is_object($plugin)) {
            return; // we're okay
        }

        throw new RuntimeException(sprintf(
            'An object was expected; "%s" given',
            gettype($plugin)
        ));
    }

    /**
     * {@inheritDoc}
     */
    protected function canonicalizeName($name)
    {
        return $name;
    }
}
