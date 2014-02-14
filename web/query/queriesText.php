<?php

function initQueriesText() {
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
        and bfr.mimetype in (
             'text/plain',
             'text/html',
             'application/msword',
             'text/xml',
	         'application/msword',
             'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
             'application/vnd.ms-powerpoint',
	         'application/vnd.openxmlformats-officedocument.presentationml.presentation',
             'application/vnd.ms-excel',
             'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
       )
    ) 
EOF;
new query("itemCountDocNonPDF","Num Non-PDF doc Items",$subq,"text", new testValZero(),array("OrigName","Creator")); 

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
        and bit.size_bytes < 20000
      inner join bitstreamformatregistry bfr on bit.bitstream_format_id = bfr.bitstream_format_id 
        and bfr.mimetype in ('application/pdf')
    ) 
EOF;
new query("itemCountBadPdf","Possible Bad PDF (smaller than 20K)",$subq,"text", new testValZero(),array("Accession","OrigName","SizeKB")); 

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
        and bit.size_bytes > 25000000
      inner join bitstreamformatregistry bfr on bit.bitstream_format_id = bfr.bitstream_format_id
        and bfr.mimetype in ('application/pdf')
    ) 
EOF;
new query("itemCountBadPdf","Large PDF (greater than 25M)",$subq,"text", new testValZero(),array("Accession","OrigName","SizeMB")); 

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
        and bfr.mimetype in ('application/pdf')
    ) 
EOF;
new query("xitemCountBadPdf","xLarge PDF (greater than 25M)",$subq,"text", new testValZero(),array("Accession","OrigName","SizeMB")); 

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
        and bfr.mimetype in (
             'text/plain',
             'text/html',
             'application/msword',
             'text/xml',
	         'application/msword',
             'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
             'application/vnd.ms-powerpoint',
	         'application/vnd.openxmlformats-officedocument.presentationml.presentation',
             'application/vnd.ms-excel',
             'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
       )
    ) 
    and not exists 
    (
      select 1
      from item2bundle i2b
      inner join bundle b 
        on i2b.bundle_id = b.bundle_id
        and b.name = 'TEXT'
        and i.item_id = i2b.item_id
    ) 
EOF;
new query("itemCountWithoutTEXT","Num Doc Items without Text Extract",$subq,"text", new testValZero(),array("Accession","Format"));
}
?>