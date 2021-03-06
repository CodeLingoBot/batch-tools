<?php
include '../header.php';
include 'solrFacets.php';

function expandCommunityId($arr, $field, $prefix = "(", $suff = ")") {
	if ($arr == "") return "";
    $q = $prefix;
    foreach(explode(",",$arr) as $col) {
    	if ($q != $prefix) {
    		$q .= "+OR+";
    	}
    	$q .= "{$field}:{$col}";
    }
    $q .= $suff;
    return $q;
}

$CUSTOM = custom::instance();

$bfacet = "&facet.field=bundleName";

solrFacets::init($CUSTOM);

$wake=util::getArg("wake","");
$comm=util::getArg("comm","");
$coll=util::getArg("coll","");
$time=util::getArg("time","");

if ($time != "") $time="+AND+time:" . str_replace(" ","+",$time);

$typearg = solrFacets::getTypeArg();

if ($wake != "" && $CUSTOM->getDSpaceVer() == "5") {
    $q = "wake:" . $wake;
} else if ($comm != "") {
    if ($typearg == "ALLV") {
        $q="(owningComm:{$comm}+OR+id:{$comm})";
    } else if ($typearg == "REPV" && $comm == "0") {
        $q="";
    } else if ($typearg == "REPV") {
        $q="id:{$comm}";
    } else if ($typearg == "COMMV") {
        $q="(owningComm:{$comm}+OR+id:{$comm})+AND+type:4";
    } else if ($typearg == "COLLV") {
        $q="owningComm:{$comm}+AND+type:3";
    } else if (($typearg == "SEARCH" || $typearg == "SEARCHU" || $typearg == "SEARCHF") && $comm == "0") {
        $q="NOT(scopeType:*)";
    } else if ($typearg == "SEARCH" || $typearg == "SEARCHU" || $typearg == "SEARCHF") {
        $q="scopeType:4+AND+scopeId:{$comm}";
    } else {
        $q="owningComm:{$comm}";
    }
} else if ($coll != "") {
    if ($typearg == "ALLV") {
        $q="na:na";
    } else if ($typearg == "COLLV") {
        $q="(owningColl:{$coll}+OR+id:{$coll})";
    } else if ($typearg == "SEARCH" || $typearg == "SEARCHU" || $typearg == "SEARCHF") {
        $q="(scopeType:3+AND+scopeId:{$coll})";
    } else {
        $q="owningColl:{$coll}";
    }
} else {
	$q="owningComm:1";
}

$q = ($q == "") ? "*:*" : $q;

$duration = solrFacets::getDuration();
$type = solrFacets::getType();
$auth = solrFacets::getAuth();
$ip = solrFacets::getIp();

$bots = $CUSTOM->getStatsBots();

$botstr = "&fq=NOT(";
foreach($bots as $k => $v) {
	if ($k != 0) $botstr .= "+OR+";
	$botstr .= $v;
}
$botstr .= ")";

$qparm = $q . $type['query'] . $auth['query'] . $ip['query'] . $time . $botstr;

$shards = $CUSTOM->getSolrShards();

if (!isset($_GET["debug"])){
  header('Content-type: application/json');
  $rows = 0;
  $req = $CUSTOM->getSolrPath() . "statistics/select?shards={$shards}&indent=on&q=". $qparm .
	   "&rows=" . $rows . "&fl=*%2Cscore&qt=&wt=json&explainOther=&hl.fl=" .
	   "&facet=true&facet.date=time" .
       $duration['query'];
  $ret = file_get_contents($req);
  echo $ret;
  return;
} else if ($_GET["debug"] == "rpt"){
} else if ($_GET["debug"] == "xml"){
  header('Content-type: text');
  $rows=2000;
  $req = $CUSTOM->getSolrPath() . "statistics/select?shards={$shards}&indent=on&q=". $qparm .
       "&rows=" . $rows . "&fl=*%2Cscore&qt=&explainOther=&hl.fl=" .
	   "&facet=true&facet.field=userAgent&facet.date=time" . $bfacet .
       $duration['query'];
  $ret = file_get_contents($req);
  echo $ret;
  return;
} else {
  header('Content-type: text');
  $rows=100;
  $req = $CUSTOM->getSolrPath() . "statistics/select?shards={$shards}&indent=on&q=". $qparm .
       "&rows=" . $rows . "&fl=*%2Cscore&qt=&wt=json&explainOther=&hl.fl=" .
	   "&facet=true&facet.date=time" .
       $duration['query'];
  $ret = file_get_contents($req);
  echo $ret;
  return;
}

header('Content-type: text/html; charset=UTF-8');
?>
<html>
<head>
<?php
$header = new LitHeader("Detailed Statistics");
$header->litPageHeader();
?>
</head>
<body>
<?php
$header->litHeader(array());

 $str = "solrStats.php?" . str_replace("debug=rpt","debug=xml",$_SERVER["QUERY_STRING"]);
 echo "<a href='" . $str . "'>XML View</a>";
 //echo "<h4>" . $qparm . "</h4>";

$rows=2000;

 $req = $CUSTOM->getSolrPath() . "statistics/select?shards={$shards}&indent=on&q=". $qparm .
       "&rows=" . $rows . "&fl=*%2Cscore&qt=&explainOther=&hl.fl=" .
	   "&facet=true&facet.date=time" .
       $duration['query'];
 $ret = file_get_contents($req);

 $xml = new DOMDocument();
 $stat = $xml->loadXML($ret);

 $xsl = new DOMDocument();
 $xsl->load("solrStats.xsl");

 $proc = new XSLTProcessor();
 $proc->importStylesheet($xsl);

 $res = $proc->transformToDoc($xml);
 echo $res->saveHTML();


?>
<?php $header->litFooter();?>
</body>
</html>
