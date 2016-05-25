<?php
namespace LjdsBundle\Helper;

use Knp\Component\Pager\Pagination\PaginationInterface;

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
		// Remove possible query string from $fileUri
		if (strpos($fileUri, '?') !== false)
			$fileUri = substr($fileUri, 0, strpos($fileUri, '?'));

		return pathinfo($fileUri)['extension'];
	}

	/**
	 * Adapted from JS to PHP, source: https://stackoverflow.com/questions/11/how-do-i-calculate-relative-time
	 * @param \DateTime $dateTime DateTime to be compared to now. We assume it is in the past
	 * @return string Human-readable relative time
	 */
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

	/**
	 * There's a bug with Symfony 2.8 where generated URLs can have two '/' after the domain
	 * name. This is a hard fix for this issue:
	 * @param $url
	 * @return mixed
	 */
	public static function fixSymfonyGeneratedURLs($url)
	{
		$url = str_replace('//', '/', $url);
		// Put back http:// (it has become http:/)
		$url = str_replace('http:/', 'http://', $url);
		// Same for https://
		$url = str_replace('https:/', 'https://', $url);;
		return $url;
	}

    /**
     * @param PaginationInterface $pagination
     * @return int
     */
    public static function getPaginationTotalCount($pagination)
    {
        $ref = new \ReflectionClass(get_class($pagination));
        $totalCountAttr = $ref->getProperty('totalCount');
        $totalCountAttr->setAccessible(true);
        return $totalCountAttr->getValue($pagination);
    }
}
