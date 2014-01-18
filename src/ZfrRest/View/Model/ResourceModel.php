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

use Zend\View\Model\ViewModel;
use ZfrRest\Resource\Metadata\ResourceMetadataInterface;
use ZfrRest\Resource\ResourceInterface;

/**
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 * @licence MIT
 */
class ResourceModel extends ViewModel
{
    /**
     * ResourceModel is usually terminate
     *
     * @var bool
     */
    protected $terminate = true;

    /**
     * The resource to be rendered
     *
     * @var ResourceInterface
     */
    protected $resource;

    /**
     * Constructor
     *
     * @param ResourceInterface $resource
     */
    public function __construct(ResourceInterface $resource)
    {
        $this->resource = $resource;
    }

    /**
     * Get the resource
     *
     * @return ResourceInterface
     */
    public function getResource()
    {
        return $this->resource;
    }
}
