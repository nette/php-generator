<?php

declare(strict_types=1);

namespace Abc;

// Property hooks - all signature variants
class PropertyHookSignatures
{
	// Get variants
	public string $basic {
		get => 'x';
	}

	public string $fullGet {
		get { return 'x'; }
	}

	protected string $refGet {
		&get { return 'x'; }
	}

	protected string $finalGet {
		final get => 'x';
	}

	// Set variants
	public string $basicSet {
		set => 'x';
	}

	public string $fullSet {
		set { }
	}

	public string $setWithParam {
		set(string $foo) { }
	}

	public string $setWithParam2 {
		set(string|int $value) => '';
	}

	public string $finalSet {
		final set { }
	}

	// Combinations
	public string $combined {
		set(string $value) { }
		get => 'x';
	}

	final public string $combinedFinal {
		/** comment set */
		#[Set]
		set { }
		/** comment get */
		#[Get]
		get => 'x';
	}

	public string $virtualProp {
		set { }
		&get => 'x';
	}
}

// Abstract hooks
abstract class AbstractHookSignatures
{
	// Abstract variants
	abstract public string $abstractGet { get; }

	abstract protected string $abstractSet { set; }

	abstract public string $abstractBoth { set; get; }
	// Combination of abstract/concrete
	abstract public string $mixedGet {
		set => 'x';
		get;
	}

	abstract public string $mixedSet {
		set;
		get => 'x';
	}
}

// Interface with hooks
interface InterfaceHookSignatures
{
	public string $get { get; }

	public string $set { #[Set] set; }

	public string $both { set; get; }

	// Get can be forced as reference
	public string $refGet { &get; }
}

// Asymmetric visibility - all valid combinations
class AsymmetricVisibilitySignatures
{
	// Basic variants
	public private(set) string $first;
	public protected(set) string $second;
	protected private(set) string $third;
	private(set) string $fourth;
	protected(set) string $fifth;

	// With readonly
	public readonly string $implicit;
	public private(set) readonly string $readFirst;
	private(set) readonly string $readSecond;
	protected protected(set) readonly string $readThird;
	public public(set) readonly string $readFourth;

	// With final
	final public private(set) string $firstFinal;
	final public protected(set) string $secondFinal;
	final protected private(set) string $thirdFinal;
	final private(set) string $fourthFinal;
	final protected(set) string $fifthFinal;
}

// Combination of hooks and asymmetric visibility
class CombinedSignatures
{
	public protected(set) string $prop2 {
		final set { }
		get { return 'x'; }
	}

	protected private(set) string $prop3 {
		set(string $value) { }
		final get => 'x';
	}
}

// Constructor property promotion with asymmetric visibility
class ConstructorAllSignatures
{
	public function __construct(
		// Basic asymmetric visibility
		public private(set) string $prop1,
		public protected(set) string $prop2,
		protected private(set) string $prop3,
		private(set) string $prop4,
		protected(set) string $prop5,

		// With readonly
		public private(set) readonly string $readProp1,
		private(set) readonly string $readProp2,
		protected protected(set) readonly string $readProp3,
		public public(set) readonly string $readProp4,

		// With hooks
		public string $hookProp1 {
			get => 'x';
		},

		// Combination of hooks and asymmetric visibility
		public protected(set) string $mixedProp1 {
			set { }
			get { return 'x'; }
		},
	) {}
}

class PropertyHookSignaturesChild extends PropertyHookSignatures
{
}
