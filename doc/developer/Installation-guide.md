ap20-ands/doc/developer: Installation-guide
===========================================

The code adapts and extends http://code.google.com/p/yggdrasil-genealogy/.

If you are installing into your own host rather than using a Nectar VM, this guide explains system requirements.

Development used Ubuntu 12.04:
* apache2
* postgresql
* php
* support utilities: bash, perl, python.

Optional XML functions require:
* Java (I used a manually installed Sun Java package installed into /opt/java and set JAVA_HOME=/opt/java/jdk1.6.0_33) [Note 1]
* BaseX XML database: http://basex.org/
* SaxonHE: from your distro, or from http://sourceforge.net/projects/saxon/files/Saxon-HE/

Note 1: I used a Sun JDK becuase I encountered issues with the free java and console usage in the BaseX database. YMMV.

Installing from the github distro https://github.com/foundersandsurvivors/ap20-ands
-----------------------------------------------------------------------------------

The installed system will require the prerequisites described in the following sections for full functionality. Basically its a Lamp stack using postgresql, php with Java/Saxon (some support functions require Xml) and the Java BaseX xml database for integrating XML (optional).

When those are in place (see below), come back here and clone the repo (and use the DEV branch for latest code) anywhere to your file system.

### Get the repository

<pre>
cd /srv
sudo mkdir ap20
sudo chown youruserid:yourgroup ap20
cd /srv/ap20
git clone github:foundersandsurvivors/ap20-ands.git
cd ap20-ands
</pre>

### Switch to the dev branch

To ensure you have latest code, in the _dev_ branch:
<pre>
vi .git/config
# insert:
[branch "dev"]
        remote = origin
        merge = refs/heads/dev
:wq # save, quit
# ensure the dev branch is checked out
git checkout -b dev
   Switched to a new branch "dev"
   Your branch is behind the tracked remote branch 'origin/dev' by 26 commits,
   and can be fast-forwarded.
# update 
git pull
    A bunch of stuff will happen
# Your working dir using the dev branch should look something like this:
git status
    # On branch dev
    nothing to commit (working directory clean)
ls -la /srv/ap20/ap20-ands 
total 32
drwxr-xr-x 6  4096 2013-12-19 13:10 .
drwxrwxr-x 4  4096 2013-12-19 13:05 ..
drwxrwxr-x 2  4096 2013-12-19 13:10 bin
drwxrwxr-x 3  4096 2013-12-19 13:10 doc
drwxrwxr-x 8  4096 2013-12-19 13:16 .git
-rw-rw-r-- 1    76 2013-12-19 13:10 .gitignore
-rw-rw-r-- 1   242 2013-12-19 13:05 README.md
drwxrwxr-x 8  4096 2013-12-19 13:10 src
</pre>

### Setup your preferred environment

IMPORTANT: you need to be a user with sudo root permissions.

To begin deployment, setup environment variables and locations to suit. From the repo as a user with sudo permissions:
<pre>
# asuming repo is cloned to /srv/ap20/ap20-ands
cd bin
cp .env.sample .env
# edit .env as required (this is YOUR) environement
./local-deploy.sh check
</pre>

Repeat above until the script reports the environment is ok. For example:
<pre>
ubuntu@ap20y1: /srv/ap20/ap20-ands $ bin/local-deploy.sh check
Deploying ap20-ands on ap20y1
== checkenv [AP20_DISTRO AP20_WEBROOT AP20_WEBPATH AP20_WEBWORK AP20_EXPORT AP20_HOME FASHOST ] start:
   Ok envt var[AP20_DISTRO]=[/srv/ap20/ap20-ands]
   Ok envt var[AP20_WEBROOT]=[/var/www]
   Ok envt var[AP20_WEBPATH]=[/var/www/ap20/yggdrasil]
   Ok envt var[AP20_WEBWORK]=[/var/webwork/ap20]
   Ok envt var[AP20_EXPORT]=[/data/job/ap20-export]
   Ok envt var[AP20_HOME]=[/srv/ydev]
   Ok envt var[FASHOST]=[y1]
== checkenv done. All good. Proceeding.
-- [AP20] environment vars:
...[snip]
-- [check] was specified. Not proceeding.
</pre>

### Run the deployment script with "do"

When you have setup the environment correctly and are happy with what is reported by _bin/./local-deploy.sh_ you can run the deployment for real, so that it will copy files to the appropriate locations and set permissions:
<pre>
   # deploy as determined in your .env
   bin/local-deploy.sh do
</pre>

### Modify /etc/sudoers

Add the fragment in src/etc/sudoers to your /etc/sudoers file. This will enable the web server userid e.g. www-data to run an initialisation script which protects the Yggdrasil installation from public access. The script needing sudo access will be installed in /usr/local/bin/ap20init.sh by the deployment script.

### Modify operational environment

The application needs a number of operational environment variables to be defined (used by various shell and php scripts).

These are defined in src/etc/environment. Note this file is read by the bin/local-deploy.sh script.

You need to modify the systems environment so these are defined. Easiest on debian/ubuntu would be to add the fragment in src/etc/environment to your /etc/environment, ensuring your preferences are set.

### VERY IMPORTANT: First time configuration of web username/password

You ___MUST___ do this to protect your web application from unauthorised public access.

To enable the first time web config script to run, as root:
<pre>
   echo -n "" > /srv/.first
   chown www-data:www-data /srv/.first
   chmod 600 /srv/.first
</pre>

The above is required to allow the one-time configuration script to be run.

The following will prompt you to assign a username and password to access more detailed documentation and the "demo" database application:
<pre>
   http://IPNUM/cgi-bin/first
</pre>

This cgi script will prompted for a username and password. if new credentials are sumbitted, it will then execute /usr/local/bin/ap20init.sh. 

The username and password you supply will be used as the default username when generating initial .htaccess files which protect the system documentation and the "demo" application.  You can of course modify .htaccess files and the web server configuration to meet your particular security requirements.

If you need/want to rerun the initial configuration again, see above to recreate the file /srv/.first, read/write by the web server user, to enable the first time web config script to be rerun. Then again go to ___http://IPNUM/cgi-bin/first___ with your browser.

### ensure postgresql is running

Run these commands so we have users in postgresql:
<pre>
cd /var/webwork/ap20/db_init/             # initialisation dir (#todo#: not yet added to repo)
sudo -u postgres psql -f init_roles.sql   # creates users
sudo -u postgres createuser ubuntu
psql postgres -tAc "alter user ubuntu with password '*' " # Note: password must match password in yggdrasil/settings/.settings.php
</pre>

Modify files as needed if you are unhappy with the above defaults.

### Setup your deployed settings.php

Copy the distro sample settings file to the deployed settings file and ensure correct permissions (readable but not writable by web server user):
<pre> 
    cp src/www/ap20/yggdrasil/settings/settings.php.sample $AP20_WEBPATH/settings/settings.php
    chmod 640 $AP20_WEBPATH/settings/settings.php
</pre> 

Modify the following to suit your requirements:
* for database access, modify the lines setting $username and $password as per the postgresql username and password above
* you may wish to add your own userid to the $administrator array line
* if you use BaseX and you need to pass credentials, modify the lines $xmluser and $xmlpassword

### Modify your apache configuration for Yggdrasil multiple domain/version support

We recommend using apache aliases to access the application. This enables multiple research domains and multiple database versions to be supported by the one installation.

Edit your Apache2 configuration using ___src/etc/apache-config-fragment.sample___ as a guide. It defines the "demo" domain using http basic authorisation setup/configured as above, with a production, dev and test database available.

Domains which form part of the Founders and Survivors project can use the YGGDRASIL_FASAUTH macro to form part of Fas Single Sign On so long as their host is part of the ___founders-and-survivors.org___ domain. The sample shows 3 such domains defined. The Fas authorisation module uses a slightly adapted form of mod_auth_pubtkt authorisation which generates a token name for the username and for each Ldap group the user is a member of. Those Ldap groupnames are identical to an Yggdrasil "domain". The settings.php file will check whether a user can access a domain/database (any version) if it finds a "auth_pubtkt" named cookie. The token "demo" is generated whether or not Fas or Basic auth is used, meaning that any authenticated use can always access the "demo" database.

Please modify the apache configuration and settings.php file according to your own authentication and authorisation requirements. Note that settings.php will check for a cookie named "auth_pubtkt" generated by the mod_auth_pubtkt SSO system. For more information see: https://neon1.net/mod_auth_pubtkt/

To create your own domain using basic authentication is as simple as modifying your apache config to:
<pre>
   use YGGDRASIL_DOMAIN yourdomainname
   restart apache
</pre>

This will provide 3 urls of the form (as per the apache config sample in src/etc/apache-config-fragment.sample):
<pre>
    /yourdomainname/db/     # production database named: yourdomainname
    /yourdomainname/dbdev/  # development    "      "  : yourdomainnamedev
    /yourdomainname/dbtest/ # test           "      "  : yourdomainnametest
</pre>

By default, the domain is protected by the same htaccess file which was created for you in the first time configuration process.

### Creating database(s) in your new domain

Yggdrasil has been extended from being originally a single database application to being able to support multiple research domains and multiple versions of databases from the same installation. This is done with the assistance of Apache configuration, as shown above.

When you access new domain URLSs, and the authenticated user is part of the ___$administrator___ array,  you will be advised the database needs to be created. The system generates a script in /tmp for you. Login and as use "ubuntu" run that script; the database will be created and an empty instance of Yggdrasil will be ready for you to use.

If you STILL get a message like:
<pre>
 Connection refused Is the server running on host "localhost" (127.0.0.1) and accepting TCP/IP
 connections on port 5432? in /var/www/ap20/khrd-dev/settings/settings.php on line 82 Create db
</pre>

you will need to check the postgresql configuration file (/etc/postgresql/9.X/main/postgresql.conf). 

For example, you may inadvertantly have multiple versions of postgresql running and the one you want might be on a different port than that which is expected in the ap20/yggdrasil/settings/settings.php file.


### Accessing the "demo" domain database

Point your browser at http://IPNUM/demo/db/ and follow the instructions.

If everything went well, you'll be able to use Yggdrasil. 

Enjoy.


### Adding other Users

As per normal apache htpasswd processing or your own custom security approach.

### Database backups

Normal postgresql system administration is required.

### Nectar host administration


Once you have everything running the way you want, you should take a snapshot.

If your database gets too large for the single volume, follow the instructions here:

<pre>
    https://github.com/foundersandsurvivors/nectar-admutils/tree/master/vdb-initialise
</pre>

NOTE: https://github.com/foundersandsurvivors/nectar-admutils has been installed already into your VM but a second volume, /dev/vdb, has NOT yet been created.


Updating the codebase from the github repository
------------------------------------------------

To do.

Contributing changes to the github repository
---------------------------------------------

To do.


System prerequisites
--------------------

### Apache2

The following apache packages were installed and modules enabled:
<pre>
ii  apache2                              2.2.22-1ubuntu1.4                   Apache HTTP Server metapackage
ii  apache2-mpm-prefork                  2.2.22-1ubuntu1.4                   Apache HTTP Server - traditional non-threaded model
ii  apache2-threaded-dev                 2.2.22-1ubuntu1.4                   Apache development headers - threaded MPM
ii  apache2-utils                        2.2.22-1ubuntu1.4                   utility programs for webservers
ii  apache2.2-bin                        2.2.22-1ubuntu1.4                   Apache HTTP Server common binary files
ii  apache2.2-common                     2.2.22-1ubuntu1.4                   Apache HTTP Server common files
ii  libapache2-mod-auth-pgsql            2.0.3-5build2                       Module for Apache2 which provides pgsql authentication
ii  libapache2-mod-fastcgi               2.4.7~0910052141-1                  Apache 2 FastCGI module for long-running CGI scripts
ii  libapache2-mod-macro                 1.1.4-3.2                           Create macros inside apache2 config files
ii  libapache2-mod-perl2                 2.0.5-5ubuntu1                      Integration of perl with the Apache2 web server
ii  libapache2-mod-php5                  5.3.10-1ubuntu3.8                   server-side, HTML-embedded scripting language (Apache 2 module)
ii  libapache2-reload-perl               0.11-2                              module for reloading Perl modules when changed on disk
</pre>

To enable some advanced BaseX rest functionality you need to enable macros and mod-rewrite:
* sudo a2enmod rewrite
* sudo a2enmod macro

### BaseX

I manually install the latest supplied .zip into a higher level directory e.g. /srv/basex. This leaves the basex installation at /srv/basex/basex. I then use /srv/basex for custom scripts and files outside the standard basex hierarchy. 

Minor amendments are made to basex/webapp/WEB-INF/web.xml for additional servlet mappings ("rx" as well as "rest"; "rxq" as well as "restxq").

A slightly customised basexhttp start/stop scripts are used which use additional local custom configuration, defined by files in /srv/basex/.mysettings. This allows multiple BaseX database deployments to share a common code infrastructure and JVM preferences:

This code is available at github:foundersandsurvivots/ap20-utils (see the "basex-admin" subdirectory).

<pre>
/usr/local/sbin/basex.sh [start|stop|status]
                ##### ==> reads /srv/basex/.mysettings/basex
/usr/local/sbin/basexdev.sh (sym link to /usr/local/sbin/basex.sh)
                ###i#### ==> /srv/basex/.mysettings/basexdev
</pre>

So the NAME of the startup script is softly linked to a file containing preferences for that instance in /srv/basex/.mysettings.

Per such "instance", you can then define the database location and JVM size like so:
<pre>
cat /srv/basex/.mysettings/basex
------------------------------------------------------------
# host specific settings for default basex installation
# this file is sourced by /usr/local/sbin/basexXXX_start.sh
# where this file is /srv/basex/.mysettings/basexXXX
BX_DBPATH="/data/bx/db"
BX_JVMSIZE="-d64 -Xms1024m -Xmx2048m"
------------------------------------------------------------
</pre>

These scripts and samples are available in a separate git hub repo:

