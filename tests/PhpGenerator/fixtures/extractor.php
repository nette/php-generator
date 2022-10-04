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
}

function () {};

/** doc */
function foo(A $a): B|C
{
	function bar()
	{
	}
}
