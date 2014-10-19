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

namespace ZfrRestTest\Options;

use PHPUnit_Framework_TestCase;
use ZfrRest\Options\ModuleOptions;

/**
 * @licence MIT
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 *
 * @group Coverage
 * @covers \ZfrRest\Options\ModuleOptions
 */
class ModuleOptionsTest extends PHPUnit_Framework_TestCase
{
    public function testAssertDefaultValue()
    {
        $options = new ModuleOptions();

        $this->assertEmpty($options->getObjectManager());
        $this->assertCount(0, $options->getDrivers());
        $this->assertNull($options->getCache());
        $this->assertFalse($options->getRegisterHttpMethodOverrideListener());
    }

    public function testSettersAndGetters()
    {
        $options = new ModuleOptions([
            'object_manager'                         => 'doctrine',
            'register_http_method_override_listener' => false,
            'enable_coalesce_filtering'              => true,
            'coalesce_filtering_key'                 => 'id',
            'drivers'                                => [
                ['class' => 'foo']
            ],
            'cache' => 'myCache'
        ]);

        $this->assertEquals('doctrine', $options->getObjectManager());
        $this->assertFalse($options->getRegisterHttpMethodOverrideListener());
        $this->assertTrue($options->isEnableCoalesceFiltering());
        $this->assertEquals('id', $options->getCoalesceFilteringKey());
        $this->assertEquals('myCache', $options->getCache());
        $this->assertCount(1, $options->getDrivers());

        foreach ($options->getDrivers() as $driver) {
            $this->assertInstanceOf('ZfrRest\Options\DriverOptions', $driver);
        }
    }
}
