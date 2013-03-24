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

use Doctrine\Common\Persistence\Mapping\ClassMetadata;

/**
 * {@inheritDoc}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 */
class ResourceMetadata implements ResourceMetadataInterface
{
    /**
     * @var \Doctrine\Common\Persistence\Mapping\ClassMetadata
     */
    protected $classMetadata;

    /**
     * @var string|null
     */
    protected $controllerName;

    /**
     * @var string|null
     */
    protected $inputFilterName;

    /**
     * @var string|null
     */
    protected $hydratorName;

    /**
     * @var array|string[]
     */
    protected $encoderNames = array();

    /**
     * @var array|string[]
     */
    protected $decoderNames = array();

    /**
     * @var array|string[]
     */
    protected $associations = array();

    /**
     * @param \Doctrine\Common\Persistence\Mapping\ClassMetadata $classMetadata
     */
    public function __construct(ClassMetadata $classMetadata)
    {
        $this->classMetadata = $classMetadata;
    }

    /**
     * {@inheritDoc}
     */
    public function getClassMetadata()
    {
        return $this->classMetadata;
    }

    /**
     * @param string|null $controllerName
     */
    public function setControllerName($controllerName)
    {
        $this->controllerName = null === $controllerName ? null : (string) $controllerName;
    }

    /**
     * {@inheritDoc}
     */
    public function getControllerName()
    {
        return $this->controllerName;
    }

    /**
     * @param string|null $inputFilterName
     */
    public function setInputFilterName($inputFilterName)
    {
        $this->inputFilterName = null === $inputFilterName ? null : (string) $inputFilterName;
    }

    /**
     * {@inheritDoc}
     */
    public function getInputFilterName()
    {
        return $this->inputFilterName;
    }

    /**
     * @param string|null $hydratorName
     */
    public function setHydratorName($hydratorName)
    {
        $this->hydratorName = null === $hydratorName ? null : (string) $hydratorName;
    }

    /**
     * {@inheritDoc}
     */
    public function getHydratorName()
    {
        return $this->hydratorName;
    }

    /**
     * @param array|string[] $encoderNames
     */
    public function setEncoderNames(array $encoderNames)
    {
        $this->encoderNames = $encoderNames;
    }

    /**
     * {@inheritDoc}
     */
    public function getEncoderNames()
    {
        return $this->encoderNames;
    }

    /**
     * @param array|string[] $decoderNames
     */
    public function setDecoderNames(array $decoderNames)
    {
        $this->decoderNames = $decoderNames;
    }

    /**
     * {@inheritDoc}
     */
    public function getDecoderNames()
    {
        return $this->decoderNames;
    }

    /**
     * @param array|string[] $associations
     */
    public function setAssociations(array $associations)
    {
        $this->associations = $associations;
    }

    /**
     * {@inheritDoc}
     */
    public function getAssociations()
    {
        return $this->associations;
    }
}