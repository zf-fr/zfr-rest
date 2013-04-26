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

/**
 * Resource normalizer for Ember-Data. This is up to date with latest revision 12
 *
 * @licence MIT
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 */
class EmberDataNormalizer implements ResourceNormalizerInterface
{
    /**
     * {@inheritDoc}
     */
    public function shouldWrapResource()
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function getWrapperKey($resourceClass, $isCollection)
    {
        $resourceKey = explode('\\', $resourceClass);

        if ($isCollection) {
            return Inflector::tableize(Inflector::pluralize(end($resourceKey)));
        }

        return Inflector::tableize(Inflector::singularize(end($resourceKey)));
    }

    /**
     * {@inheritDoc}
     */
    public function getKeyForProperty($name)
    {
        return Inflector::tableize($name);
    }

    /**
     * {@inheritDoc}
     */
    public function getKeyForHasOneAssociation($name)
    {
        return Inflector::tableize($name) . '_id';
    }

    /**
     * {@inheritDoc}
     */
    public function getKeyForHasManyAssociation($name)
    {
        return Inflector::tableize(Inflector::singularize($name)) . '_ids';
    }
}
