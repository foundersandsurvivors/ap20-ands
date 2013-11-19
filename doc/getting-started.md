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

VERY IMPORTANT: First time configuration of username/password
-------------------------------------------------------------

You ___MUST___ do this to protect your web application from unauthorised public access.

In your web browser, enter ___http://IPNUM/cgi-bin/first___

This runs the script at /usr/local/bin/ap20init.sh, which in turn, if new credentials are sumbitted, will execute /usr/local/bin/ap20init.sh.

You will be prompted for a username and password. This will be used as the default username when generating initial .htaccess files which protect the system documentation and the demo application. 

The initial configuration script will generate .htaccess files using the credentials you supply.  You can of course modify .htaccess files and the web server configuration to meet your particular security requirements. 

If you need/want to rerun the initial configuration again, see above the section "Manual Installation" under the dot point "To enable the first time web config script to run" and then go to ___http://IPNUM/cgi-bin/first___. 

