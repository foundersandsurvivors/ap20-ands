How To: Add a new domain
========================

A "domain" is an area of interest which forms a Yggdrasil database name.

You may have multiple versions of a postgres database in a domain e.g. test, dev, release etc..

When using a Nectar image, the system comes with a pre-installed "demo" domain for experimentation.

The web server and the application is configured to expect a 2 level url e.g. http://your-ip-no/demo/db/ where:
 * demo: the domain name
 * db[VERSION]: the postgres instance of the "demo" database whith an optional version
 
e.g. For /demo/dbtest the postgres database name is "demotest"
     
To create a new domain you need to tell the web server about the domain name like so:

 * ssh to your host
 * sudo vi /etc/apache2/sites-enabled/000-default
