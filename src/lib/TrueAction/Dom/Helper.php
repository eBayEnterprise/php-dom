<?php
class TrueAction_Dom_Helper
{
	public static function coerceValue($value)
	{
		return (is_a($value, 'DOMNode')) ? $value : new DOMCdataSection((string)$value);
	}
}
