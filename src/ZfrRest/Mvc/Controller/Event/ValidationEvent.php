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

namespace ZfrRest\Mvc\Controller\Event;

use Zend\EventManager\Event;
use Zend\InputFilter\InputFilterInterface;
use Zend\ServiceManager\AbstractPluginManager;
use ZfrRest\Resource\ResourceInterface;

/**
 * @author  Daniel Gimenes <daniel@danielgimenes.com.br>
 * @licence MIT
 */
class ValidationEvent extends Event
{
    /**
     * Event names
     */
    const EVENT_VALIDATE_PRE     = 'validate.pre';
    const EVENT_VALIDATE_ERROR   = 'validate.error';
    const EVENT_VALIDATE_SUCCESS = 'validate.success';

    /**
     * @var bool
     */
    protected $autoValidate = true;

    /**
     * @var ResourceInterface
     */
    protected $resource;

    /**
     * @var AbstractPluginManager
     */
    protected $inputFilterManager;

    /**
     * @var null|InputFilterInterface
     */
    protected $inputFilter;

    /**
     * @param ResourceInterface     $resource
     * @param AbstractPluginManager $inputFilterManager
     */
    public function __construct(ResourceInterface $resource, AbstractPluginManager $inputFilterManager)
    {
        $this->resource           = $resource;
        $this->inputFilterManager = $inputFilterManager;
    }

    /**
     * @param bool $autoValidate
     */
    public function setAutoValidate($autoValidate)
    {
        $this->autoValidate = (bool) $autoValidate;
    }

    /**
     * @return bool
     */
    public function getAutoValidate()
    {
        return $this->autoValidate;
    }

    /**
     * @return ResourceInterface
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * @return AbstractPluginManager
     */
    public function getInputFilterManager()
    {
        return $this->inputFilterManager;
    }

    /**
     * @param InputFilterInterface $inputFilter
     */
    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        $this->inputFilter = $inputFilter;
    }

    /**
     * @return null|InputFilterInterface
     */
    public function getInputFilter()
    {
        return $this->inputFilter;
    }
}
