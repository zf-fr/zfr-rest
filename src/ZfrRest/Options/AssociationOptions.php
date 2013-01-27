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
 * Configuration for an association of a resource type
 *
 * @license MIT
 * @author  Marco Pivetta <ocramius@gmail.com>
 */
class AssociationOptions extends AbstractOptions
{
    /**
     * @var string|null name of the resource as recognized by the class metadata factory. This is usually not
     *                  necessary since an association's resource type can be detected from class metadata
     */
    protected $resourceName;

    /**
     * @var string|null name of the controller to use for the resource. This is required when an
     *                  association is a collection of items
     */
    protected $controller;

    /**
     * @var string|null name of the input filter service to use for the resource. This is required when an
     *                  association is a collection of items
     */
    protected $inputFilter;

    /**
     * @var string|null name of the hydrator service to use for the resource. This is required when an
     *                  association is a collection of items
     */
    protected $hydrator;

    /**
     * @var string[] map of content-type => decoder service name to use for the resource. This is required when an
     *               association is a collection of items
     */
    protected $decoders = array();

    /**
     * @var string[] map of content-type => encoder service name to use for the resource. This is required when an
     *               association is a collection of items
     */
    protected $encoders = array();

    /**
     * @param string $resourceName
     */
    public function setResourceName($resourceName)
    {
        $this->resourceName = (null === $resourceName ? null : (string) $resourceName);
    }

    /**
     * @return string
     */
    public function getResourceName()
    {
        return $this->resourceName;
    }

    /**
     * @param null|string $controller
     */
    public function setController($controller)
    {
        $this->controller = (null === $controller ? null : (string) $controller);
    }

    /**
     * @return null|string
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * @param null|string $inputFilter
     */
    public function setInputFilter($inputFilter)
    {
        $this->inputFilter = (null === $inputFilter ? null : (string) $inputFilter);
    }

    /**
     * @return null|string
     */
    public function getInputFilter()
    {
        return $this->inputFilter;
    }

    /**
     * @param null|string $hydrator
     */
    public function setHydrator($hydrator)
    {
        $this->hydrator = (null === $hydrator ? null : (string) $hydrator);
    }

    /**
     * @return null|string
     */
    public function getHydrator()
    {
        return $this->hydrator;
    }

    /**
     * @param array $decoders
     */
    public function setDecoders(array $decoders)
    {
        $this->decoders = $decoders;
    }

    /**
     * @return string[]
     */
    public function getDecoders()
    {
        return $this->decoders;
    }

    /**
     * @param array $encoders
     */
    public function setEncoders(array $encoders)
    {
        $this->encoders = $encoders;
    }

    /**
     * @return \string[]
     */
    public function getEncoders()
    {
        return $this->encoders;
    }
}
