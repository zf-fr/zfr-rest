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

namespace ZfrRestTest\Factory;

use PHPUnit_Framework_TestCase as TestCase;
use ZfrRestTest\Util\ServiceManagerFactory;

/**
 * @author Michaël Gallego <mic.gallego@gmail.com>
 * @covers \ZfrRest\Factory\BaseSubPathMatcherFactory
 */
class BaseSubPathMatcherFactoryTest extends TestCase
{
    public function testCanCreateFromFactory()
    {
        $serviceManager = ServiceManagerFactory::getServiceManager();

        $object = $serviceManager->get('ZfrRest\Mvc\Router\Http\Matcher\CollectionSubPathMatcher');
        $this->assertInstanceOf('ZfrRest\Mvc\Router\Http\Matcher\CollectionSubPathMatcher', $object);

        $object = $serviceManager->get('ZfrRest\Mvc\Router\Http\Matcher\AssociationSubPathMatcher');
        $this->assertInstanceOf('ZfrRest\Mvc\Router\Http\Matcher\AssociationSubPathMatcher', $object);

        $object = $serviceManager->get('ZfrRest\Mvc\Router\Http\Matcher\BaseSubPathMatcher');
        $this->assertInstanceOf('ZfrRest\Mvc\Router\Http\Matcher\BaseSubPathMatcher', $object);
    }
}
