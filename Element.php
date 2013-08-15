<?php

namespace Techfever\View;

use Traversable;
use Zend\Stdlib\ArrayUtils;
use Zend\Stdlib\InitializableInterface;

class Element implements ElementAttributeRemovalInterface, ElementInterface, InitializableInterface {
	/**
	 * @var array
	 */
	protected $attributes = array();

	/**
	 * @var array custom options
	 */
	protected $options = array();

	/**
	 * @param  null|int|string  $name    Optional name for the element
	 * @param  array            $options Optional options for the element
	 * @throws Exception\InvalidArgumentException
	 */
	public function __construct($name = null, $options = array()) {
		if (null !== $name) {
			$this->setName($name);
		}

		if (!empty($options)) {
			$this->setOptions($options);
		}
	}

	/**
	 * This function is automatically called when creating element with factory. It
	 * allows to perform various operations (add elements...)
	 *
	 * @return void
	 */
	public function init() {
	}

	/**
	 * Set value for name
	 *
	 * @param  string $name
	 * @return Element|ElementInterface
	 */
	public function setName($name) {
		$this->setAttribute('name', $name);
		return $this;
	}

	/**
	 * Get value for name
	 *
	 * @return string|int
	 */
	public function getName() {
		return $this->getAttribute('name');
	}

	/**
	 * Set options for an element. Accepted options are:
	 *
	 * @param  array|Traversable $options
	 * @return Element|ElementInterface
	 * @throws Exception\InvalidArgumentException
	 */
	public function setOptions($options) {
		if ($options instanceof Traversable) {
			$options = ArrayUtils::iteratorToArray($options);
		} elseif (!is_array($options)) {
			throw new Exception\InvalidArgumentException('The options parameter must be an array or a Traversable');
		}
		if (isset($options['attributes'])) {
			$this->setAttributes($options['attributes']);
		}
		$this->options = $options;

		return $this;
	}

	/**
	 * Get defined options
	 *
	 * @return array
	 */
	public function getOptions() {
		return $this->options;
	}

	/**
	 * Return the specified option
	 *
	 * @param string $option
	 * @return NULL|mixed
	 */
	public function getOption($option) {
		if (!isset($this->options[$option])) {
			return null;
		}

		return $this->options[$option];
	}

	/**
	 * Retrieve the type used for this element
	 *
	 * @return string
	 */
	public function getType() {
		if (!isset($this->options['type'])) {
			return null;
		}
		return $this->options['type'];
	}

	/**
	 * Retrieve the label used for this element
	 *
	 * @return string
	 */
	public function getLabel() {
		if (!isset($this->options['label'])) {
			return null;
		}
		return $this->options['label'];
	}

	/**
	 * Retrieve the title used for this element
	 *
	 * @return string
	 */
	public function getTitle() {
		if (!isset($this->options['title'])) {
			return null;
		}
		return $this->options['title'];
	}

	/**
	 * Return the is password
	 *
	 * @return boolean
	 */
	public function isPassword() {
		$options = $this->getOption('options');
		if (isset($options['isPassword'])) {
			if ($options['isPassword'] == 'True') {
				$options['isPassword'] = True;
			}
			return $options['isPassword'];
		}
		return false;
	}

	/**
	 * Set a single element attribute
	 *
	 * @param  string $key
	 * @param  mixed  $value
	 * @return Element|ElementInterface
	 */
	public function setAttribute($key, $value) {
		$this->attributes[$key] = $value;
		return $this;
	}

	/**
	 * Retrieve a single element attribute
	 *
	 * @param  $key
	 * @return mixed|null
	 */
	public function getAttribute($key) {
		if (!array_key_exists($key, $this->attributes)) {
			return null;
		}
		return $this->attributes[$key];
	}

	/**
	 * Remove a single attribute
	 *
	 * @param string $key
	 * @return ElementInterface
	 */
	public function removeAttribute($key) {
		unset($this->attributes[$key]);
		return $this;
	}

	/**
	 * Does the element has a specific attribute ?
	 *
	 * @param  string $key
	 * @return bool
	 */
	public function hasAttribute($key) {
		return array_key_exists($key, $this->attributes);
	}

	/**
	 * Set many attributes at once
	 *
	 * Implementation will decide if this will overwrite or merge.
	 *
	 * @param  array|Traversable $arrayOrTraversable
	 * @return Element|ElementInterface
	 * @throws Exception\InvalidArgumentException
	 */
	public function setAttributes($arrayOrTraversable) {
		if (!is_array($arrayOrTraversable) && !$arrayOrTraversable instanceof Traversable) {
			throw new Exception\InvalidArgumentException(sprintf('%s expects an array or Traversable argument; received "%s"', __METHOD__, (is_object($arrayOrTraversable) ? get_class($arrayOrTraversable) : gettype($arrayOrTraversable))));
		}
		foreach ($arrayOrTraversable as $key => $value) {
			$this->setAttribute($key, $value);
		}
		return $this;
	}

	/**
	 * Retrieve all attributes at once
	 *
	 * @return array|Traversable
	 */
	public function getAttributes() {
		return $this->attributes;
	}

	/**
	 * Remove many attributes at once
	 *
	 * @param array $keys
	 * @return ElementInterface
	 */
	public function removeAttributes(array $keys) {
		foreach ($keys as $key) {
			unset($this->attributes[$key]);
		}

		return $this;
	}

	/**
	 * Clear all attributes
	 *
	 * @return Element|ElementInterface
	 */
	public function clearAttributes() {
		$this->attributes = array();
		return $this;
	}
}
