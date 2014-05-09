<?php
class EbayEnterprise_Dom_Helper
{
	public static function coerceValue($value)
	{
		return ($value instanceof DOMNode) ? $value : new DOMCdataSection((string) $value);
	}
}
