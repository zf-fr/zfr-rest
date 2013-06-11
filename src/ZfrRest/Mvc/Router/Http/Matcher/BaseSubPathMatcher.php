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

namespace ZfrRest\Mvc\Router\Http\Matcher;

use Doctrine\Common\Collections\Criteria;
use Zend\Http\Request;
use ZfrRest\Mvc\Exception;
use ZfrRest\Mvc\Exception\RuntimeException;
use ZfrRest\Resource\ResourceInterface;

/**
 * {@inheritDoc}
 *
 * Base sub-path matcher - passes the sub-path to either an association or
 * a collection matcher depending on the case
 *
 * @license MIT
 * @author  Marco Pivetta <ocramius@gmail.com>
 */
class BaseSubPathMatcher implements SubPathMatcherInterface
{
    /**
     * @var CollectionSubPathMatcher
     */
    private $collectionMatcher;

    /**
     * @var AssociationSubPathMatcher
     */
    private $associationMatcher;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->collectionMatcher  = new CollectionSubPathMatcher();
        $this->associationMatcher = new AssociationSubPathMatcher();
    }

    /**
     * {@inheritDoc}
     */
    public function matchSubPath(ResourceInterface $resource, $subPath, Request $request)
    {
        $path = trim($subPath, '/');

        if (empty($path)) {
            return new SubPathMatch($resource, $subPath);
        }

        if ($resource->isCollection()) {
            $match = $this->collectionMatcher->matchSubPath($resource, $path, $request);
        } else {
            $match = $this->associationMatcher->matchSubPath($resource, $path, $request);
        }

        if (! $match) {
            return null;
        }

        return $this->matchSubPath(
            $match->matchedResource,
            substr($path, strlen($match->matchedPath)),
            $request
        );
    }
}
