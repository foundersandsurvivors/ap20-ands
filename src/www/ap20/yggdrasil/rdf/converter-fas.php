<?php
    /**
     * Convert RDF from one format to another
     *
     * The source RDF data can either be fetched from the web
     * or typed into the Input box.
     *
     * The first thing that this script does is make a list the names of the
     * supported input and output formats. These options are then
     * displayed on the HTML form.
     *
     * The input data is loaded or parsed into an EasyRdf_Graph.
     * That graph is than outputted again in the desired output format.
     *
     * @package    EasyRdf
     * @copyright  Copyright (c) 2009-2012 Nicholas J Humfrey
     * @license    http://unlicense.org/
     */

    //set_include_path(get_include_path() . PATH_SEPARATOR . '../lib/');
    set_include_path(get_include_path() . PATH_SEPARATOR . '../x/easyrdf/lib/');
    require_once "../x/easyrdf/lib/EasyRdf.php";
    require_once "html_tag_helpers.php";

    EasyRdf_Namespace::set('f', 'http://convix.org/fas/1.0/');

$myns = EasyRdf_Namespace::getDefault();

    $input_format_options = array('Guess' => 'guess');
    $output_format_options = array();
    foreach (EasyRdf_Format::getFormats() as $format) {
        if ($format->getSerialiserClass()) {
            $output_format_options[$format->getLabel()] = $format->getName();
        }
        if ($format->getParserClass()) {
            $input_format_options[$format->getLabel()] = $format->getName();
        }
    }

    // Stupid PHP :(
    if (get_magic_quotes_gpc() and isset($_REQUEST['data'])) {
        $_REQUEST['data'] = stripslashes($_REQUEST['data']);
    }

    // Default to Guess input and Turtle output
    if (!isset($_REQUEST['output_format'])) {
        $_REQUEST['output_format'] = 'turtle';
    }
    if (!isset($_REQUEST['input_format'])) {
        $_REQUEST['input_format'] = 'guess';
    }

    // Display the form, if raw option isn't set
    if (!isset($_REQUEST['raw'])) {
        print <<<HTML_START
<html>
<head><title>EasyRdf 0.8.0 Converter</title>
<link rel="stylesheet" type="text/css" href="/css/useit_style.css" />
<link rel="stylesheet" type="text/css" href="/css/default.css" />
</head>
<body>
<h1>EasyRdf Converter (fas rdf testing)</h1>
<div style='margin: 10px'>
HTML_START;
        print form_tag();
        print label_tag('fasid', 'Fas Recid: ').text_field_tag('fasid', 'ccc86788', array('size'=>20)) . "<br />or<br />\n";
        print label_tag('data', 'Input Data: ').'<br />'.text_area_tag('data', '', array('cols'=>80, 'rows'=>10)) . "<br />\n";
        print label_tag('uri', 'or Uri: ').text_field_tag('uri', 'http://www.dajobe.org/foaf.rdf', array('size'=>80)) . "<br />\n";
        print label_tag('input_format', 'Input Format: ').select_tag('input_format', $input_format_options) . "<br />\n";
        print label_tag('output_format', 'Output Format: ').select_tag('output_format', $output_format_options) . "<br />\n";
        print label_tag('raw', 'Raw Output: ').check_box_tag('raw') . "<br />\n";
        print reset_tag() . submit_tag();
        print form_end_tag();
        print "</div>\n";
    }

    if (isset($_REQUEST['fasid']) or isset($_REQUEST['uri']) or isset($_REQUEST['data'])) {
        // prep uri if its a fasid
        $myUri = $_REQUEST['uri'];
        if (isset($_REQUEST['fasid'])) {
            $recid = $_REQUEST['fasid'];
            //$rectype = preg_replace ("^([a-zA-Z]\d*[a-zA-Z]+)\d+.*$","$1",$recid);
            $rectype = preg_replace ('/^([a-zA-Z]\d*[a-zA-Z]+).+$/','$1',$recid);
            if      ( $rectype == "ert" ) { $fasdb = "raw_ert"; }
            elseif  ( $rectype == "ccc" ) { $fasdb = "ccc_mediaflux"; }
            elseif  ( $rectype == "kgb" ) { $fasdb = "norm_kgb"; }
            elseif  ( $rectype == "kgd" ) { $fasdb = "norm_kgd"; }
            elseif  ( $rectype == "kgm" ) { $fasdb = "norm_kgm"; }
            else                          { $fasdb = "fasdb"; }
            $rdfHost = "http://dev.founders-and-survivors.org/rx/"; 
            $rdfQuery = '/?run=q/rdf.xq&$ID=';
            $myUri = $rdfHost . $fasdb . $rdfQuery . $_REQUEST['fasid'];
print "<p>..rectype[$rectype] recid[$recid] myUri[$myUri] myns[$myns]</p>";
        }
        
        // Parse the input
        $graph = new EasyRdf_Graph($myUri);
        if (empty($_REQUEST['data'])) {
            $graph->load($myUri, $_REQUEST['input_format']);
        } else {
            $graph->parse($_REQUEST['data'], $_REQUEST['input_format'], $myUri);
        }

        // Lookup the output format
        $format = EasyRdf_Format::getFormat($_REQUEST['output_format']);

        // Serialise to the new output format
        $output = $graph->serialise($format);
        if (!is_scalar($output)) {
            $output = var_export($output, true);
        }

        // Send the output back to the client
        if (isset($_REQUEST['raw'])) {
            header('Content-Type: '.$format->getDefaultMimeType());
            print $output;
        } else {
            print '<pre>'.htmlspecialchars($output).'</pre>';
        }
    }

    if (!isset($_REQUEST['raw'])) {
        print "</body>\n";
        print "</html>\n";
    }
