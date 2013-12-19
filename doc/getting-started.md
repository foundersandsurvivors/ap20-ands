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

This section provides a quick overview. For COMPLETE details, see doc/developer/Installation-guide.md.

You'll need a webserver with postgresql and php installed. See the Installation-guide.md for complete details of core and optional components. 

This repository includes the php web application code and supporting shell/perl/saxon utilities which assist in automating the creation of empty Yggdrasil databases. 

Clone the repo into a convenient location e.g. /srv/ap20/ap20-ands, for example :
<pre>
    cd /srv
    sudo mkdir /srv/ap20; sudo chown ubuntu:ubuntu /srv/ap20
    cd /srv/ap20 
    git clone git@github.com:foundersandsurvivors/ap20-ands
    cd ap20-ands
</pre>

Switch to the dev branch as explained in doc/developer/Installation-guide.md for the latest version.

From the repo as a user with sudo permissions (the deployment script contains sudo commands), run _bin/./local-deploy.sh check_ and follow the instructions given to construct your preferred Yggdrasil environment variables. These will determine locations iand unix permissions for the php code, workareas and various related files.

When you have setup the environment correctly and are happy with what is reported by _bin/./local-deploy.sh_ you can run the deployment for real (change "check" to "do"). This will copy files to the appropriate locations for your web server and set permissions as you have specified.

Finally, you will also need to:
* add the fragment in src/etc/sudoers to your /etc/sudoers file (allows user www-data to run a setup script)
* add the fragment in src/etc/environment to your /etc/environment (sets operational environment variables AP20_HOME, AP20_DISTRO, AP20_WEBROOT and FASHOST)
* run the first time configuration and postgresql users are created, as described in detail in the Installation manual
* modify your apache2 config as decribed in the Installation manual
* modify settings.php for your database access userid/password

Yggdrasil should now be ready at: _http://IPNUM/demo/db/_

For how to create other domains, create databases, and other installation/setup information, refer to the detailed installetion guide.

