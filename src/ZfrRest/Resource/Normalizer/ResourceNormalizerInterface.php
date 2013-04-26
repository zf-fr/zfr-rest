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

/**
 * RESTful API are used in various of contexts. Often, you will use your own API with a JavaScript MVC framework
 * like EmberJS, AngularJS or ExtJS. Most of those frameworks have some kind of integrated tools to handle
 * communication with your server. However, they all have different conventions which make it hard to work with,
 * because you must write a lot of boilerplate code to normalize/denormalize data.
 *
 * For instance, Ember-Data expects your payload to be sent by wrapping the resource data around a key whose name
 * is the resource name, as well as providing underscore_separated keys.
 *
 * @licence MIT
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 */
interface ResourceNormalizerInterface
{
    /**
     * Return true if the resource should be wrapped around a key
     *
     * @return bool
     */
    public function shouldWrapResource();

    /**
     * From a resource class name, get the wrapper key
     *
     * @param  string $resourceClass
     * @param  bool   $isCollection
     * @return string
     */
    public function getWrapperKey($resourceClass, $isCollection);

    /**
     * Get the key for a property
     *
     * @param  string $name
     * @return string
     */
    public function getKeyForProperty($name);

    /**
     * Get the key for a "has one" association
     *
     * @param  string $name
     * @return string
     */
    public function getKeyForHasOneAssociation($name);

    /**
     * Get the key for a "has many" association
     *
     * @param  string $name
     * @return string
     */
    public function getKeyForHasManyAssociation($name);
}
