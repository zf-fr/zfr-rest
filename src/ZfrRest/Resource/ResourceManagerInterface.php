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
 * @license MIT
 * @author  Marco Pivetta <ocramius@gmail.com>
 */
interface ResourceManagerInterface
{
    /**
     * Retrieves whether the resource manager knows the given resource name or metadata instance
     *
     * @param string|\Doctrine\Common\Persistence\Mapping\ClassMetadata $resource
     *
     * @return bool
     */
    public function hasResource($resource);

    /**
     * Retrieves whether the resource manager knows the given association on the provided resource
     *
     * @param string $resourceName
     * @param string $associationName
     *
     * @return boolean
     */
    public function hasResourceAssociation($resourceName, $associationName);

    /**
     * Retrieves class metadata for the provided resource name
     *
     * @param string $name
     *
     * @return \Doctrine\Common\Persistence\Mapping\ClassMetadata
     *
     * @throws \ZfrRest\Resource\Exception\UnknownResourceException
     */
    public function getResourceClassMetadata($name);

    /**
     * Retrieves the resource name for the provided metadata
     *
     * @param \Doctrine\Common\Persistence\Mapping\ClassMetadata $metadata
     *
     * @return string
     *
     * @throws \ZfrRest\Resource\Exception\UnknownResourceException
     */
    public function getResourceName(ClassMetadata $metadata);
}
