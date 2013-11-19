Getting started
===============

Legend
------

IPNUM -- the ip number of your host machine. You may of course use a domain name instead, if you map IPNUM to a Domain Name using a DNS service. 

Using a Nectar Virtual Machine
------------------------------

The application comes fully installed with an empty "demo" database as a Nectar Public Instance.

The name of this Instance is AP20-YGGDRASIL-n.n (n.n is a major/minor release No).

When launching the instance:
* enter a name for this host in the userdata area. It should be a name you can easily remember and should indicate the function of this VM.
* ensure that the security settings for the VM allow HTTP (Port 80) and SSH (Port 22).
* when the VM is up and running, note the IPNUM of the running VM

When using a Nectar Virtual Machine you can skip the "Manual Installation" section because everything is already pre-installed and working.


Manual Installation 
-------------------

[SKIP this if running a Nectar VM]

From the repo as a user with sudo permissions:
* in the repo, cd bin
* cp .env.sample ,env and edit .env as required
* run bin/local-deploy.sh (copies from repo to deployment locations and sets user/permissions)
* add the fragment in src/etc/sudoers to your /etc/sudoers file (allows user www-data to run a setup script)
* add the fragment in src/etc/environemnt to your /etc/environemnt (sets env vars AP20_HOME, AP20_WEBROOT and FASHOST)
* To enable the first time web config script to run, as root:
<pre>
   echo -n "" > /srv/.first
   chown www-data:www-data /srv/.first
   chmod 600 /srv/.first
</pre>
* install postgresql for your installation if not already installed
* run these commands so we have users
<pre>
cd /var/webwork/ap20/db_init/             # init dir (to be moved into repo)
sudo -u postgres psql -f init_roles.sql   # creates users
sudo -u postgres createuser ubuntu
psql postgres -tAc "alter user ubuntu with password '*****'" # password must match password in yggdrasil/settings/.settings.php
</pre>

VERY IMPORTANT: First time configuration of username/password
-------------------------------------------------------------

You ___MUST___ do this to protect your web application from unauthorised public access.

In your web browser, enter ___http://IPNUM/cgi-bin/first___

This runs the script at /usr/local/bin/ap20init.sh, which in turn, if new credentials are sumbitted, will execute /usr/local/bin/ap20init.sh.

You will be prompted for a username and password. This will be used as the default username when generating initial .htaccess files which protect the system documentation and the demo application. 

The initial configuration script will generate .htaccess files using the credentials you supply.  You can of course modify .htaccess files and the web server configuration to meet your particular security requirements. 

If you need/want to rerun the initial configuration again, see above the section "Manual Installation" under the dot point "To enable the first time web config script to run" and then go to ___http://IPNUM/cgi-bin/first___. 

Accessing the "demo" domain database
------------------------------------

Creating your own new domain
----------------------------
Add a line to your apache2 config and restart apache like so:

<pre>
sudo vi /etc/apache2/sites-enabled/000-default
After the definition of <Macro YGGDRASIL_DOMAIN $domain> add a line:
Use YGGDRASIL_DOMAIN yourDomainName
:wq (save your changes)
sudo /etc/init.d/apache2 restart
</pre>

That will provide 3 new URLS confifured to access to ap20/yggdrasil php application:

* http://YOURIP/yourDomainName/dbdev/ (for development)
* http://YOURIP/yourDomainName/dbtest/ (for testing)
* http://YOURIP/yourDomainName/db/ (for production usage)

By default, the domain is protected by the same htaccess file which was created for you above (See the "VERY IMPORTANT" section above).

Creating database(s) in your new domain
---------------------------------------

When you access new domain URLSs you will be advised the database needs to be created. The system generates a script in /tmp for you. Login and as use "ubuntu" run that script; the database will be created and an empty instance of Yggdrasil will be ready for you to use.

If you STILL get a message like:
<pre>
 Connection refused Is the server running on host "localhost" (127.0.0.1) and accepting TCP/IP 
 connections on port 5432? in /var/www/ap20/khrd-dev/settings/settings.php on line 82 Create db
</pre>

you will need to check the postgresql configuration file (/etc/postgresql/9.X/main/postgresql.conf). For example, you may inadvertantly have multiple versions of postgres running and the one you want might be on a different port than that which is expected in the ap20/yggdrasil/settings/settings.php file.

Adding other Users
------------------

To do.

Database backups
----------------

Normal postgresql system administration is required.

Nectar host administration
--------------------------

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
