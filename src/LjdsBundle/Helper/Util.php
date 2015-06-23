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

	public static function replaceAccentedCharacters($str) {
		$unwanted_array = array(
			'Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
			'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U',
			'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c',
			'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o',
			'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y'
		);
		return strtr($str, $unwanted_array);
	}

	public static function relativeTime(\DateTime $dateTime) {
		$now = new \DateTime();

		$delta = abs($now->getTimestamp() - $dateTime->getTimestamp());

		$second = 1;
		$minute = 60*$second;
		$hour = 60*$minute;
		$day = 24*$hour;
		$month = 30*$day;

		if ($delta < $minute)
			return "il y a " . $delta . " secondes";
		if ($delta < 2*$minute)
			return "il y a une minute";
		if ($delta < 45*$minute)
			return "il y a " . round($delta/$minute) . " minutes";
		if ($delta < 90*$minute)
			return "il y a une heure";
		if ($delta < 24*$hour)
			return "il y a " . round($delta/$hour) . " heures";
		if ($delta < 48*$hour)
			return "hier";
		if ($delta < 30*$day)
			return "il y a " . round($delta/$day) . " jours";
		if ($delta < 12*$month) {
			$months = round($delta/$day/30);
			return $months <= 1 ? "il y a un mois" : "il y a " . $months . " mois";
		}
		else {
			$years = round($delta/$day/365);
			return $years <= 1 ? "il y a un an" : "il y a " . $years . " ans";
		}
	}
}
