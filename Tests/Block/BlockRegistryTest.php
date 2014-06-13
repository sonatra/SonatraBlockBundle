<?php

/**
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license inBlockation, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\BlockBundle\Tests\Block;

use Sonatra\Bundle\BlockBundle\Block\BlockRegistry;
use Sonatra\Bundle\BlockBundle\Block\BlockTypeGuesserChain;
use Sonatra\Bundle\BlockBundle\Block\BlockTypeGuesserInterface;
use Sonatra\Bundle\BlockBundle\Block\ResolvedBlockTypeFactoryInterface;
use Sonatra\Bundle\BlockBundle\Tests\Block\Fixtures\TestCustomExtension;
use Sonatra\Bundle\BlockBundle\Tests\Block\Fixtures\Type\FooSubTypeWithParentInstance;
use Sonatra\Bundle\BlockBundle\Tests\Block\Fixtures\Type\FooSubType;
use Sonatra\Bundle\BlockBundle\Tests\Block\Fixtures\Extension\FooTypeBazExtension;
use Sonatra\Bundle\BlockBundle\Tests\Block\Fixtures\Extension\FooTypeBarExtension;
use Sonatra\Bundle\BlockBundle\Tests\Block\Fixtures\Type\FooType;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class BlockRegistryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var BlockRegistry
     */
    private $registry;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $resolvedTypeFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $guesser1;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $guesser2;

    /**
     * @var TestCustomExtension
     */
    private $extension1;

    /**
     * @var TestCustomExtension
     */
    private $extension2;

    protected function setUp()
    {
        $this->resolvedTypeFactory = $this->getMock('Sonatra\Bundle\BlockBundle\Block\ResolvedBlockTypeFactory');
        $this->guesser1 = $this->getMock('Sonatra\Bundle\BlockBundle\Block\BlockTypeGuesserInterface');
        $this->guesser2 = $this->getMock('Sonatra\Bundle\BlockBundle\Block\BlockTypeGuesserInterface');

        /* @var ResolvedBlockTypeFactoryInterface $rtf */
        $rtf = $this->resolvedTypeFactory;
        /* @var BlockTypeGuesserInterface $guesser1 */
        $guesser1 = $this->guesser1;
        /* @var BlockTypeGuesserInterface $guesser2 */
        $guesser2 = $this->guesser2;

        $this->extension1 = new TestCustomExtension($guesser1);
        $this->extension2 = new TestCustomExtension($guesser2);
        $this->registry = new BlockRegistry(array(
            $this->extension1,
            $this->extension2,
        ), $rtf);
    }

    public function testGetTypeFromExtension()
    {
        $type = new FooType();
        $resolvedType = $this->getMock('Sonatra\Bundle\BlockBundle\Block\ResolvedBlockTypeInterface');

        $this->extension2->addType($type);

        $this->resolvedTypeFactory->expects($this->once())
            ->method('createResolvedType')
            ->with($type)
            ->will($this->returnValue($resolvedType));

        $resolvedType->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('foo'));

        $resolvedType = $this->registry->getType('foo');

        $this->assertSame($resolvedType, $this->registry->getType('foo'));
    }

    public function testGetTypeWithTypeExtensions()
    {
        $type = new FooType();
        $ext1 = new FooTypeBarExtension();
        $ext2 = new FooTypeBazExtension();
        $resolvedType = $this->getMock('Sonatra\Bundle\BlockBundle\Block\ResolvedBlockTypeInterface');

        $this->extension2->addType($type);
        $this->extension1->addTypeExtension($ext1);
        $this->extension2->addTypeExtension($ext2);

        $this->resolvedTypeFactory->expects($this->once())
            ->method('createResolvedType')
            ->with($type, array($ext1, $ext2))
            ->will($this->returnValue($resolvedType));

        $resolvedType->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('foo'));

        $this->assertSame($resolvedType, $this->registry->getType('foo'));
    }

    public function testGetTypeConnectsParent()
    {
        $parentType = new FooType();
        $type = new FooSubType();
        $parentResolvedType = $this->getMock('Sonatra\Bundle\BlockBundle\Block\ResolvedBlockTypeInterface');
        $resolvedType = $this->getMock('Sonatra\Bundle\BlockBundle\Block\ResolvedBlockTypeInterface');

        $this->extension1->addType($parentType);
        $this->extension2->addType($type);

        $this->resolvedTypeFactory->expects($this->at(0))
            ->method('createResolvedType')
            ->with($parentType)
            ->will($this->returnValue($parentResolvedType));

        $this->resolvedTypeFactory->expects($this->at(1))
            ->method('createResolvedType')
            ->with($type, array(), $parentResolvedType)
            ->will($this->returnValue($resolvedType));

        $parentResolvedType->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('foo'));

        $resolvedType->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('foo_sub_type'));

        $this->assertSame($resolvedType, $this->registry->getType('foo_sub_type'));
    }

    public function testGetTypeConnectsParentIfGetParentReturnsInstance()
    {
        $type = new FooSubTypeWithParentInstance();
        $parentResolvedType = $this->getMock('Sonatra\Bundle\BlockBundle\Block\ResolvedBlockTypeInterface');
        $resolvedType = $this->getMock('Sonatra\Bundle\BlockBundle\Block\ResolvedBlockTypeInterface');

        $this->extension1->addType($type);

        $this->resolvedTypeFactory->expects($this->at(0))
            ->method('createResolvedType')
            ->with($this->isInstanceOf('Sonatra\Bundle\BlockBundle\Tests\Block\Fixtures\Type\FooType'))
            ->will($this->returnValue($parentResolvedType));

        $this->resolvedTypeFactory->expects($this->at(1))
            ->method('createResolvedType')
            ->with($type, array(), $parentResolvedType)
            ->will($this->returnValue($resolvedType));

        $parentResolvedType->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('foo'));

        $resolvedType->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('foo_sub_type_parent_instance'));

        $this->assertSame($resolvedType, $this->registry->getType('foo_sub_type_parent_instance'));
    }

    /**
     * @expectedException \Sonatra\Bundle\BlockBundle\Block\Exception\UnexpectedTypeException
     */
    public function testGetTypeThrowsExceptionIfParentNotFound()
    {
        $type = new FooSubType();

        $this->extension1->addType($type);

        $this->registry->getType($type);
    }

    /**
     * @expectedException \Sonatra\Bundle\BlockBundle\Block\Exception\InvalidArgumentException
     */
    public function testGetTypeThrowsExceptionIfTypeNotFound()
    {
        $this->registry->getType('bar');
    }

    /**
     * @expectedException \Sonatra\Bundle\BlockBundle\Block\Exception\UnexpectedTypeException
     */
    public function testGetTypeThrowsExceptionIfNoString()
    {
        $this->registry->getType(array());
    }

    public function testHasTypeAfterLoadingFromExtension()
    {
        $type = new FooType();
        $resolvedType = $this->getMock('Sonatra\Bundle\BlockBundle\Block\ResolvedBlockTypeInterface');

        $this->resolvedTypeFactory->expects($this->once())
            ->method('createResolvedType')
            ->with($type)
            ->will($this->returnValue($resolvedType));

        $resolvedType->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('foo'));

        $this->assertFalse($this->registry->hasType('foo'));

        $this->extension2->addType($type);

        $this->assertTrue($this->registry->hasType('foo'));
        $this->assertTrue($this->registry->hasType('foo'));
    }

    public function testGetTypeGuesser()
    {
        $expectedGuesser = new BlockTypeGuesserChain(array($this->guesser1, $this->guesser2));

        $this->assertEquals($expectedGuesser, $this->registry->getTypeGuesser());

        /* @var ResolvedBlockTypeFactoryInterface $rtf */
        $rtf = $this->resolvedTypeFactory;

        $registry = new BlockRegistry(
            array($this->getMock('Sonatra\Bundle\BlockBundle\Block\BlockExtensionInterface')),
            $rtf);

        $this->assertNull($registry->getTypeGuesser());
    }

    public function testGetExtensions()
    {
        $expectedExtensions = array($this->extension1, $this->extension2);

        $this->assertEquals($expectedExtensions, $this->registry->getExtensions());
    }

    public function testInvalidExtensions()
    {
        $this->setExpectedException('Sonatra\Bundle\BlockBundle\Block\Exception\UnexpectedTypeException');

        /* @var ResolvedBlockTypeFactoryInterface $rtf */
        $rtf = $this->resolvedTypeFactory;

        new BlockRegistry(array(42), $rtf);
    }
}