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

namespace ZfrRestTest\Mvc\Controller\Plugin;

use PHPUnit_Framework_TestCase;
use ZfrRest\Mvc\Controller\Plugin\PaginatorWrapper;

/**
 * @licence MIT
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 *
 * @group Coverage
 * @covers \ZfrRest\Mvc\Controller\Plugin\PaginatorWrapper
 */
class AbstractRestfulControllerTest extends PHPUnit_Framework_TestCase
{
    public function testCanCreatePaginatorFromCollection()
    {
        $collection = $this->getMock('Doctrine\Common\Collections\Collection');
        $plugin     = new PaginatorWrapper();

        $paginator = $plugin($collection);

        $this->assertInstanceOf('Zend\Paginator\Paginator', $paginator);
        $this->assertInstanceOf('DoctrineModule\Paginator\Adapter\Collection', $paginator->getAdapter());
    }

    public function testCanCreatePaginatorFromSelectableUsingCriteriaArray()
    {
        $selectable = $this->getMock('Doctrine\Common\Collections\Selectable');
        $plugin     = new PaginatorWrapper();

        $paginator = $plugin($selectable);

        $this->assertInstanceOf('Zend\Paginator\Paginator', $paginator);
        $this->assertInstanceOf('DoctrineModule\Paginator\Adapter\Selectable', $paginator->getAdapter());
    }
}
