ap20-ands (aka Yggdrasil, a population genealogy application)
=============================================================

This is the AP20 (ANDS funded) FAS Geneological Connections Project Source Code repository.

This project is source code for a relational database (postgresql) web application (php)
which assists in researching the genealogy of historical populations.

For more information about the project's aims and background refer to:
 * development blog: http://fasconn.blogspot.com.au/
 * project website: http://founders-and-survivors.org/y/

Acknowledgements:
 * Leif Kristensen kindly donated his open source code https://code.google.com/archive/p/yggdrasil-genealogy/ without which this version would not have been possible (thankyou Leif);
 * Australian National Data Service for funding the ands-ap20 development

See detailed installation documentation in doc/getting-started.md

Release notes (as at feb 2017)
------------------------------

The "master" branch requires a PostgreSQL installation using a version 9.3.2 or higher (some experimental use is made of no-sql features xml, hstore, ltree and json).

Other branches may be developed from time to time where required to handle variations in infrastructure and/or domain specific functionality. Where at all possible we try to accomodate a common code base in the master branch. The code is designed to be shared by multiple research domains and databases (e.g. you might have a production, testing, and development database for a particular domain all on the one server, sharing the same common installed code base). 

To keep things simple, the code base for the application is a single github repository. Deployment scripts (bash) are provided to install code in your preferred locations with appropriate permissions. Utility scripts are provided to detect differences between your clone of the repository and your deployed software.

Note that any future changes to the database schema and/or postgresql functions, where at all possible, should be automated (scripts, files plus installation instructions) and be added to the "var" directory of the repository, with a subdirectory there for each related group of data changes. This should help to facilitate the synchronisation of database changes with related web application code. 
