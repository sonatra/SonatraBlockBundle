<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\BlockBundle\Block;

/**
 * A block group bundling multiple block views.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
interface BlockInterface extends \ArrayAccess, \Traversable, \Countable
{
    /**
     * Sets the parent block.
     *
     * @param BlockInterface $parent The parent block
     *
     * @return BlockInterface The block instance
     */
    public function setParent(BlockInterface $parent = null);

    /**
     * Returns the parent block.
     *
     * @return BlockInterface The parent block
     */
    public function getParent();

    /**
     * Returns whether the block has a parent.
     *
     * @return Boolean
     */
    public function hasParent();

    /**
     * Adds a child to the block.
     *
     * @param BlockInterface|string|integer $child   The BlockInterface instance or the name of the child.
     * @param string|null                   $type    The child's type, if a name was passed.
     * @param array                         $options The child's options, if a name was passed.
     *
     * @return BlockInterface The block instance
     *
     * @throws Exception\BlockException          When trying to add a child to a non-compound block.
     * @throws Exception\UnexpectedTypeException If $child or $type has an unexpected type.
     */
    public function add($child, $type = null, array $options = array());

    /**
     * Returns the child with the given name.
     *
     * @param string $name The name of the child
     *
     * @return BlockInterface The child block
     */
    public function get($name);

    /**
     * Returns whether a child with the given name exists.
     *
     * @param string $name The name of the child
     *
     * @return Boolean
     */
    public function has($name);

    /**
     * Removes a child block the block.
     *
     * @param string $name The name of the child to remove
     *
     * @return BlockInterface The block instance
     */
    public function remove($name);

    /**
     * Returns all children in this group.
     *
     * @return array An array of BlockInterface instances
     */
    public function all();

    /**
     * Updates the field with default data.
     *
     * @param array $modelData The data formatted as expected for the underlying object
     *
     * @return BlockInterface The block instance
     */
    public function setData($modelData);

    /**
     * Returns the data in the format needed for the underlying object.
     *
     * @return mixed
     */
    public function getData();

    /**
     * Returns the normalized data of the field.
     *
     * @return mixed When the field is not bound, the default data is returned.
     *               When the field is bound, the normalized bound data is
     *               returned if the field is valid, null otherwise.
     */
    public function getNormData();

    /**
     * Returns the data transformed by the value transformer.
     *
     * @return string
     */
    public function getViewData();

    /**
     * Returns the block's configuration.
     *
     * @return BlockConfigInterface The configuration.
     */
    public function getConfig();

    /**
     * Returns the name by which the block is identified in blocks.
     *
     * @return string The name of the block.
     */
    public function getName();

    /**
     * Returns the property path that the block is mapped to.
     *
     * @return \Symfony\Component\PropertyAccess\PropertyPathInterface The property path.
     */
    public function getPropertyPath();

    /**
     * Returns whether the block is empty.
     *
     * @return Boolean
     */
    public function isEmpty();

    /**
     * Returns the root of the block tree.
     *
     * @return BlockInterface The root of the tree
     */
    public function getRoot();

    /**
     * Returns whether the field is the root of the block tree.
     *
     * @return Boolean
     */
    public function isRoot();

    /**
     * Returns the form.
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    public function getForm();

    /**
     * Creates a block.
     *
     * @param BlockView $parent The parent block
     *
     * @return BlockView The block
     */
    public function createView(BlockView $parent = null);
}
