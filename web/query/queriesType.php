<?php

function initQueriesType() {
$subq = <<< EOF
    and exists 
    (
      select 1
      from item2bundle i2b
      inner join bundle b 
        on i2b.bundle_id = b.bundle_id
        and b.name = 'ORIGINAL'
        and i.item_id = i2b.item_id
      inner join bundle2bitstream b2b on b.bundle_id = b2b.bundle_id
      inner join bitstream bit on bit.bitstream_id = b2b.bitstream_id
      inner join bitstreamformatregistry bfr on bit.bitstream_format_id = bfr.bitstream_format_id
        and bfr.mimetype like 'video/%'
    ) 
EOF;
new query("itemCountVideo","Num Video Items",$subq,"type", new testValZero(),array("Accession","Format")); 

$subq = <<< EOF
    and exists 
    (
      select 1
      from item2bundle i2b
      inner join bundle b 
        on i2b.bundle_id = b.bundle_id
        and b.name = 'ORIGINAL'
        and i.item_id = i2b.item_id
      inner join bundle2bitstream b2b on b.bundle_id = b2b.bundle_id
      inner join bitstream bit on bit.bitstream_id = b2b.bitstream_id
      inner join bitstreamformatregistry bfr on bit.bitstream_format_id = bfr.bitstream_format_id
        and bfr.mimetype like 'text/html%'
    ) 
EOF;
new query("itemCountHtml","Num HTML Items",$subq,"type", new testValZero(),array("Accession","Format")); 

$subq = <<< EOF
    and exists 
    (
      select 1
      from item2bundle i2b
      inner join bundle b 
        on i2b.bundle_id = b.bundle_id
        and b.name = 'ORIGINAL'
        and i.item_id = i2b.item_id
      inner join bundle2bitstream b2b
        on b.bundle_id = b2b.bundle_id
      inner join bitstream bit 
        on b2b.bitstream_id = bit.bitstream_id
        and bit.name ~ '.*\.zip$'
    ) 
EOF;
new query("itemCountZip","Num Zip files",$subq,"type", new testValTrue(),array("Accession","Format","OrigName")); 

}
?>