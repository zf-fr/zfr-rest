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

namespace ZfrRest\Resource\Normalizer;

use Doctrine\Common\Inflector\Inflector;
use ZfrRest\Resource\Exception\RuntimeException;

/**
 * The default normalizer is a very simple normalizer that does not wrap resources, and just convert all keys
 * to underscore_separated
 *
 * @licence MIT
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 */
class DefaultNormalizer implements ResourceNormalizerInterface
{
    /**
     * {@inheritDoc}
     */
    public function shouldWrapResource()
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function getWrapperKey($resourceClass, $isCollection)
    {
        throw new RuntimeException('This normalizer does not allow to wrap resource');
    }

    /**
     * {@inheritDoc}
     */
    public function normalizeKeyForProperty($name)
    {
        return Inflector::tableize($name);
    }

    /**
     * {@inheritDoc}
     */
    public function denormalizeKeyForProperty($name)
    {
        return Inflector::camelize($name);
    }

    /**
     * {@inheritDoc}
     */
    public function normalizeKeyForHasOneAssociation($name)
    {
        return Inflector::tableize($name);
    }

    /**
     * {@inheritDoc}
     */
    public function denormalizeKeyForHasOneAssociation($name)
    {
        return Inflector::camelize($name);
    }

    /**
     * {@inheritDoc}
     */
    public function normalizeKeyForHasManyAssociation($name)
    {
        return Inflector::tableize($name);
    }

    /**
     * {@inheritDoc}
     */
    public function denormalizeKeyForHasManyAssociation($name)
    {
        return Inflector::camelize($name);
    }
}
