Organisation of source code in ap20-yggdrasil: "src"
====================================================

This directory includes all the application code:

* cgi-bin: utility  "first" cgi used in initial configuration 
* etc: file fragments required for system configuration
* usr-local-bin : utility scripts
* www : web server root; various support files e.g. css, and of course the Yggdrasil php application
* xml : optional xml components of the data management system

Note: The above are deployed to their implementation locations by the `bin/local-deploy.sh` script based on the variables defined in `bin/.env`. Refer to the "Getting Started" guide for more details.
