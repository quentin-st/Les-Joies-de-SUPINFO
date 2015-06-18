<?php
namespace LjdsBundle\Helper;

class Util {
    public static function startsWith($haystack, $needle) {
        // search backwards starting from haystack length characters from the end
        return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
    }
    public static function endsWith($haystack, $needle) {
        // search forward starting from end minus needle length characters
        return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== FALSE);
    }

	public static function getFileExtension($fileUri) {
		return pathinfo($fileUri)['extension'];
	}

	public static function extensionMatches($fileUri, $extension) {
		return Util::getFileExtension($fileUri) == $extension;
	}
}
