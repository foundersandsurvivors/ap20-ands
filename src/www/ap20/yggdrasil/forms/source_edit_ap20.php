<?php

/***************************************************************************
 *   source_edit_ap20.php  (AP20 extensions)                               *
 *   Yggdrasil: Extra data in sources to support actions and workflows     *
 ***************************************************************************/

// for meta sources we allow templates with sprintf specs, and apply a passed key to them
// to enable automatically loaded sources and crowdsourcing workflows to be managed
function apply_key_to_template ($i,$tpl,$n) {
    if     ( $n == 1 ) { return sprintf ($tpl, $i ); }
    elseif ( $n == 2 ) { return sprintf ($tpl, $i, $i); }
    elseif ( $n == 3 ) { return sprintf ($tpl, $i, $i, $i); }
    elseif ( $n == 4 ) { return sprintf ($tpl, $i, $i, $i, $i); }
    elseif ( $n == 5 ) { return sprintf ($tpl, $i, $i, $i, $i, $i); }
    else               { return $tpl; }
}

if (!isset($_POST['posted'])) {

    $_help['sdata'] = "AP20 extension: (hstore) structured data mined from text. Comma delimited field/value pairs. Formatting: f1=&gt;val1, f2=&gt; val2, .... (note re quotes)";
    textarea_input('Structured data:', 5, 100, 'sdata', $row['sdata'] );

    $_help['stree'] = "AP20 extension: Source label as an stree e.g. aot.c31a";
    text_input('Entity:', 100, 'stree', $row['stree'] );
}
else {
    $error = 0;
    $log->debug("\n==== start source_edit_ap20.php authuser[$authuser] post source[$source] part_type[$part_type] ch_part_type[$ch_part_type]" );
    
    // additional fields for ap20
    $structured_data = pg_escape_string($_POST['sdata']);
    $entity_label = $_POST['stree'];

    pg_prepare("query", "UPDATE sources SET
            parent_id = $1,
            sort_order = $2,
            source_text = $3,
            source_date = $4,
            part_type = $5,
            ch_part_type = $6,
            sdata = $7,
            stree = $8
        WHERE source_id = $9"
    );
    $rc = pg_execute("query", array( $psource, $sort, $text, $source_date, $part_type, $ch_part_type, $structured_data, $entity_label, $source));
    // sms: if no $rc there was a problem (need to trap it? Editor has probably stuffed up the hstore formatting. )
    $model = hstore_to_array($structured_data);
    $log->debug("model: $model");

    /******************************************
     Configure a workflow controller       uri=>"workflow/crowdsource.php"
     ******************************************/
    if ( isset($model['ACTION']) ) { 
        if ( $model['ACTION'] == "crowdsource" ) {
            $log->debug( "-- crowdsource entity_label[$entity_label] structured_data[$structured_data]");
         
        }
        elseif ( $model['ACTION'] == "gensubsources:batch:random_numeric_key" ) {

            // custom code loading a hierarchical set of sources from ert
            // keys have been preloaded

            $keyname =  $model['keyname'];
            $total_keys = $model['total_keys'];     
            $rectype= $model['rectype'];
            $keysfile = $metasource_config_dir . "/keys/$rectype";
            $no_of_substitutions = substr_count ( $template, "%" );
            if ( is_readable($keysfile) ) {
                 $FH = fopen( $keysfile, "r" );
                 $log->debug("opened $keysfile");
                 $nkeys_read = 0;
                 while ( $key = fgets($FH) ) {
                    $key = rtrim($key);
                    $nkeys_read++;
                        if ( $nkeys_read < 10 ) {
                         $load_text = apply_key_to_template($key,$template,$no_of_substitutions);
                         $log->debug("[$key] load_text[$load_text]");
                    }
                 }
                 fclose($FH);
                 $log->debug("nkeys_read : $nkeys_read");
            }
            else {
                 echo "$keysfile not readable. Did you specify a rectype and upload a keys file?";
            }
        }
    }

    /******************************************
     check for ACTION key in the sdata hstore
     ******************************************/

    elseif (preg_match("/\bACTION\b/i", $structured_data)) {
        $model = hstore_to_array($structured_data);
        if ( preg_match("/^gensubsources:number:(\d+),(\d+):(\d+)$/", $model['ACTION'], $spec) ) {
             $start = $spec[1];
             $count = $spec[2];
             $my_ch_part_type = $spec[3];
             $end = $start + $count - 1;
             $n = substr_count ( $template, "%" ); // how many parameters in the template substitution?
             $label_template = $model['stree_append'];
             $log->debug( "####ACTION:gensubsources n[$n] parent=part_type[$part_type] ch_part_type[$ch_part_type] template[$template]  start[$start] count[$count]" );
             // apply the template
             pg_query("BEGIN");
             for ($i=$start; $i<=$end; $i++) {
                 if ( $label_template )
                      $my_entity_label = $entity_label . "." . sprintf($label_template,$i);
                 if     ( $n == 1 ) { $my_text = sprintf ($template, $i ); }
                 elseif ( $n == 2 ) { $my_text = sprintf ($template, $i, $i); }
                 elseif ( $n == 3 ) { $my_text = sprintf ($template, $i, $i, $i); }
                 elseif ( $n == 4 ) { $my_text = sprintf ($template, $i, $i, $i, $i); }
                 elseif ( $n == 5 ) { $my_text = sprintf ($template, $i, $i, $i, $i, $i); }
                 else               { $my_text = $template; }
                 $log->debug( "add_source my_ch_part_type[$my_ch_part_type] stree:[$my_entity_label] parent[$source] TEXT:[$my_text]" );

                 $source_id = add_source(0, 0, 0, $source, $my_text, $i);

                 if ($source_id > 0) {
                     pg_query("UPDATE sources
                               SET part_type=$ch_part_type, ch_part_type=$my_ch_part_type,
                                   sdata=('auto=>true'),
                                   stree='$my_entity_label'
                               WHERE source_id = $source_id");
                 }
                 else {
                     $error++;
                 }
             }
             if ( $error ) { pg_query("ROLLBACK"); $log->warn("ROLLBACK!!"); } else { pg_query("COMMIT"); }
        } 
        else {
             $log->error("Bad ACTION value. See manual. [".$model['ACTION']."]");
        } 
    }

    $log->debug("---- source_edit_ap20.php post end rc[$rc] error[$error]");
}
?>
