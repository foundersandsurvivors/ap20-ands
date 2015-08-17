ap20-ands: bin/ directory contents
==================================

This directory contains utility bash shell scripts for deploying and maintaining the ap20-ands installation.

local-deploy.sh
---------------

A script to which deploys the repository contents to your preferred locations.

.env.sample
-----------

Sample file containing the environment variables used by local-deploy.sh.
Copy this to ".env", modify that to suit your needs, before running local-deploy.sh.

bash.functions
--------------

Bash functions used in local-deploy.sh

diff.sh and rsync.phpapp.dep2repo.exclusions
--------------------------------------------

A script to show differences between this repository and the deployed application.

Differences are written to files in `/tmp`.

Sample output of `./diff.sh`:

    ubuntu@ap20y1: /srv/ap20/ap20-ands/bin $ ./diff.sh
    ##==== ./diff.sh: Whole dir diff for Yggdrasil webapp
    -- from deployed[/var/www/ap20/yggdrasil/]
    -- to   distro[../src/www/ap20/yggdrasil]
    -rw-rw-r-- 1 ubuntu ubuntu 8110129 Aug 14 16:54 /tmp/diff.log
    -rw-rw-r-- 1 ubuntu ubuntu  350286 Aug 14 16:54 /tmp/diff-summary.log

Exclusions used in diff.sh are in file `rsync.phpapp.dep2repo.exclusions`.

diff-xqlib.sh
-------------

Show differences between xquery library files in this repository and deployed xquery library files 
(as defined by environment variable: $AP20__XQLIB) used by optional Saxon and BaseX XML extensions.

Sample output of `./diff-xqlib.sh`:

    ##==== ./diff-xqlib.sh: differences between repo and deployed Yggdrasil xquery modules
    -- dir deployed[/usr/local/lib/xquery/] Environment variable: $AP20_XQLIB
    -- dir   distro[../src/xml/xquery-lib/]
       Note: The path for $AP20_XQLIB is hard coded into the modules
             because there is no standard way in xquery to do module deployment.
             We have chosen to hard code the location: /usr/local/lib/xquery.
    ............................................. bdmUtils-1.0.xq:
    -rw-rw-r-- 1 ubuntu ubuntu 13389 Jun  4  2013 ../src/xml/xquery-lib/bdmUtils-1.0.xq
    -rw-rw-r-- 1 ubuntu ubuntu 13389 Jun  4  2013 /usr/local/lib/xquery/bdmUtils-1.0.xq
    rc[0] No difference.
    .. and so on.
    ..Files present in the deployed dir NOT in this repo will be listed at end.

Optionally, you can pass the name of a different deployed directory to check that against this repository e.g.

    ./diff-xqlib.sh /usr/local/lib/xquery-devfas    
