<?php

class solrFacets {
	public static $DURATION;
	public static $TYPE;
	public static $AUTH;
	public static $IP;
	
	public static function getArg($name, $def) {
		if (isset($_GET[$name])) return $_GET[$name];
		return $def; 
	}
	public static function init($CUSTOM) {
	  $bfacet = (($CUSTOM->getDSpaceVer() == "1.7") ? "" : "+bundleName:ORIGINAL");
	  self::$DURATION = array(
		"7DAYS" => array(
			"desc" => "Last 7 days",
			"colcount" => 8,
			"query" => "&facet.date.start=NOW/DAY-7DAYS&facet.date.end=NOW&facet.date.gap=".urlencode("+1DAY")
		),
		"4WEEKS" => array(
			"desc" => "Last 4 weeks",
			"colcount" => 5,
			"query" => "&facet.date.start=NOW/DAY-28DAYS&facet.date.end=NOW&facet.date.gap=".urlencode("+7DAYS")
		),
		"6MONTHS" => array(
			"desc" => "Last 6 months",
			"colcount" => 7,
			"query" => "&facet.date.start=NOW/MONTH/DAY-6MONTHS&facet.date.end=NOW&facet.date.gap=".urlencode("+1MONTH")
		),
		"2YEAR" => array(
			"desc" => "Quarterly, 2 Years",
			"colcount" => 8,
			"query" => "&facet.date.start=NOW/YEAR/DAY-1YEAR&facet.date.end=NOW/YEAR/DAY" . urlencode("+1YEAR") . "&facet.date.gap=".urlencode("+3MONTHS")
		),
	  );

	  self::$TYPE = array(
		"ITEMV" => array(
			"desc" => "Item Views",
			"query" => "+AND+type:2"
		),
		"COLLV" => array(
			"desc" => "Collection Views",
			"query" => "+AND+type:3"
		),
		"COMMV" => array(
			"desc" => "Community Views",
			"query" => "+AND+type:4"
		),
		"BITV" => array(
			"desc" => "Original Bitstream Views/downloads",
			"query" => "+AND+type:0" . $bfacet
		),
		"BITVALL" => array(
			"desc" => "All Bitstream Views",
			"query" => "+AND+type:0"
		),
	  );

	  self::$AUTH = array(
		"ALL" => array(
			"desc" => "All Users",
			"query" => ""
		),
		"AUTH" => array(
			"desc" => "Authenticated Users",
			"query" => "+AND+(epersonid:[0+TO+*])"
		),
		"UNAUTH" => array(
			"desc" => "Unauthenticated Users",
			"query" => "+AND+NOT(epersonid:[0+TO+*])"
		),
	  );

	  self::$IP = $CUSTOM->getStatsIPs();
	}
	
	public static function getDurationArg() {
		$duration = self::getArg("duration","6MONTHS");
		if (!isset(self::$DURATION[$duration])) $duration = "6MONTHS";
		return $duration;
	}
	public static function getDuration() {
		return self::$DURATION[self::getDurationArg()];
	}

	public static function getDurationKey($k) {
		$arr = self::getDuration();
		return $arr[$k];
	}

	public static function getTypeArg() {
		$type = self::getArg("type","ITEMV");
		if (!isset(self::$TYPE[$type])) $type = "ITEMV";
		return $type;
	}

	public static function getType() {
		return self::$TYPE[self::getTypeArg()];
	}
	public static function getTypeKey($k) {
		$arr = self::getType();
		return $arr[$k];
	}
	
	public static function getAuthArg() {
		$auth = self::getArg("auth","ALL");
		if (!isset(self::$AUTH[$auth])) $auth = "ALL";
		return $auth;
	}
	public static function getAuth() {
		return self::$AUTH[self::getAuthArg()];
	}
	public static function getAuthKey($k) {
		$arr = self::getAuth();
		return $arr[$k];
	}

	public static function getIpArg() {
		$ip = self::getArg("ip","ALL");
		if (!isset(self::$IP[$ip])) $ip = "ALL";
		return $ip;
	}
	public static function getIp() {
		return self::$IP[self::getIpArg()];
	}
	public static function getIpKey($k) {
		$arr = self::getIp();
		return $arr[$k];
	}
	
}
?>