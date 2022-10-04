<?php
class Class1
{
	public function foo()
	{
		new class {
			function bar() {
			}
		};
	}

	function comment1()
	{
		/** comment */
		$a = 10;
	}

	function comment2()
	{
		// comment
		'bar';
	}

	function comment3()
	{
		// comment
		Foo\Bar::XX;
	}
}

function () {};

/** doc */
function foo(A $a): B|C
{
	function bar()
	{
	}
}
