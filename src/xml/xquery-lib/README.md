AP20 Xquery Library Modules
===========================

This directory contains a library of xquery modules used in both BaseX and Saxon xquery processors. This modules have maily been used in processing domain specific data.

As there is no standard way of locating library modules across different Xquery environments, the following local convention has been used:

* All modules are deployed in ___/usr/local/lib/xquery___. 
* Each module is given a namespace and is sumbolically linked using its module name
* Modules are referenced at their location in /usr/local/lib/xquery using the symlink identical to the module name
* If a Saxon version of a module is required that is indicated by the name, and Saxon will refer to the modules filename
<pre>
    Filename:  fasutil-1.0.xqm
    Namespace: module namespace fu = "http://fas/fu";
    Symlink:   ln -s fasutil-1.0.xqm fu
</pre>

This allows Xquery programs deployed in both BaseX and/or Saxon to reference modules as follows:
<pre>
    import module namespace fu = 'http://fas/fu' at '/usr/local/lib/xquery/fu';
</pre>

If a Saxon version of a module is required that is indicated by the name, and Saxon will refer to the modules filename:

e.g. fasutil-1.0-SAXONEE.xqm

It is possible but not necessary to install modules in BaseX from a BaseX client shell like so:
<pre>
    basexclient -U **** -P **** -c 'REPO INSTALL file:///usr/local/lib/xquery/fasrecode-1.0.xqm'
</pre>

Module Descriptions
-------------------

<pre>
ap20   ./yggdrasil-load-2012-lib.xq    Generates Yggdrasil database table insert statements

bdm    ./bdmUtils-1.0.xq               Tas BDMb/diggers work utility functions

condig ./vdlbdm-load-2013-lib.xq       Utility functions for loading convicts/diggers/khrd

dd     ./ddUtils-1.0.xqm               DDI and metadata (data description) functions

fenv   ./fas-ENVIRONMENT\_HOST.xqm     HOST specific variable definitions (for BaseX Webapps) #####usernames#####

fr     ./fasrecode-1.0.xqm             General purpose recoding and xml rewriting functions with actions logged in SYSTEM element

fui    ./fasuserinterface-1.0.xqm      BaseX Web User Interface library

fu     ./fasutil-1.0.xqm               FAS xml to ap20 recid mapping and misc/common utilities
       ./fasutil-1.0-SAXONEE.xqm       (saxon version)

functx ./functx-1.0.xqm                Local copy of Priscilla Walmsley's excellent FunctX library (see http://www.xqueryfunctions.com/)

psql   ./psql-1.0.xqm                  Basex xquery utilities for talking with postgresql (connect; table to xml,html functions)

ssana  ./spreadsheet-ana-utils.xqm     Utility functions for processing generic table/row/column spreadsheet data

str-compare ./str-compare-1.0.xqm      Levenshtein distance function
                                       ( adaped from http://bennettweb.wordpress.com/2009/11/13/levenshtein-distance-in-xquery/ )

vjs-ss ./vjs-spreadsheet-ana-utils.xqm Defines columns specifications for Founders and Survivors "vjs" ships research project.
</pre>



