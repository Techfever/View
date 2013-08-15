<?php
namespace Techfever\View;

use Traversable;
use Zend\Stdlib\ArrayUtils;
use Zend\Stdlib\PriorityQueue;
use Techfever\View\Exception;

class View extends Element implements ViewInterface {

	/**
	 * Data being validated
	 *
	 * @var null|array|Traversable
	 */
	protected $data;

	/**
	 * Is the View prepared ?
	 *
	 * @var bool
	 */
	protected $isPrepared = false;

	/**
	 * @var Factory
	 */
	protected $factory;

	/**
	 * @var array
	 */
	protected $byName = array();

	/**
	 * @var array
	 */
	protected $elements = array();

	/**
	 * @var PriorityQueue
	 */
	protected $iterator;

	/**
	 * @param  null|int|string  $name    Optional name for the element
	 * @param  array            $options Optional options for the element
	 */
	public function __construct($name = null, $options = array()) {
		$this->iterator = new PriorityQueue();
		parent::__construct($name, $options);
	}

	/**
	 * Set options. Accepted options are:
	 *
	 * @param  array|Traversable $options
	 * @return Element|ElementInterface
	 * @throws Exception\InvalidArgumentException
	 */
	public function setOptions($options) {
		parent::setOptions($options);

		return $this;
	}

	/**
	 * Compose a View factory to use when calling add() with a non-element
	 *
	 * @param  Factory $factory
	 * @return View
	 */
	public function setViewFactory(Factory $factory) {
		$this->factory = $factory;
		return $this;
	}

	/**
	 * Retrieve composed View factory
	 *
	 * Lazy-loads one if none present.
	 *
	 * @return Factory
	 */
	public function getViewFactory() {
		if (null === $this->factory) {
			$this->setViewFactory(new Factory());
		}

		return $this->factory;
	}

	/**
	 * Add an element
	 *
	 * If $element is an array or Traversable, passes the argument on
	 * to the composed factory to create the object before attaching it.
	 *
	 * $flags could contain metadata such as the alias under which to register
	 * the element, order in which to prioritize it, etc.
	 *
	 * @param  array|Traversable|ElementInterface $element
	 * @param  array                              $flags
	 * @return \Techfever\View\ViewInterface
	 */
	public function add($element, array $flags = array()) {
		$options = $element;
		if (is_array($element) || ($element instanceof Traversable && !$element instanceof ElementInterface)) {
			$factory = $this->getViewFactory();
			$element = $factory->create($element);
		}

		if (!$element instanceof ElementInterface) {
			throw new Exception\InvalidArgumentException(sprintf('%s requires that $element be an object implementing %s; received "%s"', __METHOD__, __NAMESPACE__ . '\ElementInterface', (is_object($element) ? get_class($element) : gettype($element))));
		}

		$name = $element->getName();
		if ((null === $name || '' === $name) && (!array_key_exists('name', $flags) || $flags['name'] === '')) {
			throw new Exception\InvalidArgumentException(sprintf('%s: element or provided is not named, and no name provided in flags', __METHOD__));
		}

		if (array_key_exists('name', $flags) && $flags['name'] !== '') {
			$name = $flags['name'];

			// Rename the element to the specified alias
			$element->setName($name);
		}
		$element->setOptions($options);

		$order = 0;
		if (array_key_exists('priority', $flags)) {
			$order = $flags['priority'];
		}

		$this->iterator->insert($element, $order);
		$this->byName[$name] = $element;
		$this->elements[$name] = $element;

		return $this;
	}

	/**
	 * Does have an element by the given name?
	 *
	 * @param  string $element
	 * @return bool
	 */
	public function has($element) {
		return array_key_exists($element, $this->byName);
	}

	/**
	 * Retrieve a named element
	 *
	 * @param  string $element
	 * @return ElementInterface
	 */
	public function get($element) {
		if (!$this->has($element)) {
			throw new Exception\InvalidElementException(sprintf("No element by the name of [%s] found in View", $element));
		}
		return $this->byName[$element];
	}

	/**
	 * Remove a named element
	 *
	 * @param  string $element
	 * @return ViewInterface
	 */
	public function remove($element) {
		if (!$this->has($element)) {
			return $this;
		}

		$entry = $this->byName[$element];
		unset($this->byName[$element]);

		$this->iterator->remove($entry);

		unset($this->elements[$element]);
		return $this;
	}

	/**
	 * Set/change the priority of an element
	 *
	 * @param string $element
	 * @param int $priority
	 * @return ViewInterface
	 */
	public function setPriority($element, $priority) {
		$element = $this->get($element);
		$this->remove($element);
		$this->add($element, array(
						'priority' => $priority
				));
		return $this;
	}

	/**
	 * Retrieve all attached elements
	 *
	 * Storage is an implementation detail of the concrete class.
	 *
	 * @return array|Traversable
	 */
	public function getElements() {
		return $this->elements;
	}

	/**
	 * Ensures state is ready for use
	 *
	 * Marshalls the input, to ensure  are
	 * available, and prepares any elements that require
	 * preparation.
	 *
	 * @return View
	 */
	public function prepare() {
		if ($this->isPrepared) {
			return $this;
		}
		foreach ($this->getIterator() as $element) {
			if ($element instanceof ViewInterface) {
				$element->prepare();
			} elseif ($element instanceof ElementPrepareAwareInterface) {
				$element->prepareElement($this);
			}
		}

		$this->isPrepared = true;
		return $this;
	}

	/**
	 * Ensures state is ready for use. Here, we append the name of to every elements in order to avoid
	 * name clashes if the same is used multiple times
	 *
	 * @param  ViewInterface $View
	 * @return mixed|void
	 */
	public function prepareElement(ViewInterface $view) {
		$name = $this->getName();

		foreach ($this->byName as $element) {
			// Recursively prepare elements
			if ($element instanceof ElementPrepareAwareInterface) {
				$element->prepareElement($view);
			}
		}
	}

	/**
	 * Countable: return count of attached elements
	 *
	 * @return int
	 */
	public function count() {
		return $this->iterator->count();
	}

	/**
	 * IteratorAggregate: return internal iterator
	 *
	 * @return PriorityQueue
	 */
	public function getIterator() {
		return $this->iterator;
	}

	/**
	 * Make a deep clone
	 *
	 * @return void
	 */
	public function __clone() {
		$items = $this->iterator->toArray(PriorityQueue::EXTR_BOTH);

		$this->byName = array();
		$this->elements = array();
		$this->iterator = new PriorityQueue();

		foreach ($items as $item) {
			$element = clone $item['data'];
			$name = $element->getName();

			$this->iterator->insert($element, $item['priority']);
			$this->byName[$name] = $element;

			if ($element instanceof ElementInterface) {
				$this->elements[$name] = $element;
			}
		}
	}
}
