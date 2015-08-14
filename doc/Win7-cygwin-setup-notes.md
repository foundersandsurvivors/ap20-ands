Setting up on a windows (7) machine - ymmv
==========================================

Some very informal notes about my ap20-ands Yggdrasil setup on a windows 7 machine using Cygwin.

So far:
* apache2 (cygwin) works
* basex (windows distro) works
* apache2 redirect to basex works

testing/inprogress:

* Cygwin 32 bit.
* dir "data" at root of C drive
* dir "srv" at root of C drive
* in cygwin: C:\cygwin we mkdir /srv and symlink to c:\srv locations:

Cygwin                    C:
/data      -> /cygdrive/c/data/
/srv/basex -> /cygdrive/c/srv/basex/
/srv/www   -> /cygdrive/c/srv/www/

These are symlinks of convenience as I am nearly always in cygwin when on Windows.


BaseX
-----

As ever, BaseX was pretty straightforward and generally just works.

I have the BaseX distro set up within /srv/basex i.e. C:\srv\basex\basex which emulates my unix setup.

 * in the .basex file at /srv/basex/basex I have: DBPATH = C:\data\bx\db
 * I locate xml sources at C:\data\bx\xml and the basex db binaries at C:\data\bx\db
   * basex logs are at C:\data\bx\db\.logs or /cygdrive/c/data/bx/db/.logs
 * to start: /srv/basex/basex/bin/basexhttp.bat & (put it into background)

In apache I use redirection from /restxq/ to basex:7874/restxq and ditto for /rest/.

Had to fiddle with the web.xml file a little to get locations right.

ssilcot@zen /srv/basex/basex/webapp
$ la
total 16
drwxr-xr-x 1 ssilcot None    0 Jan 27 14:12 ./
drwxr-xr-x 1 ssilcot None    0 Jan 27 13:35 ../
drwxr-xr-x 1 ssilcot None    0 Jan 27 13:54 q/      : vanilla rest scripts e.g.
                                                      /rest/dbname/?run=q/test.xq
drwxr-xr-x 1 ssilcot None    0 Jan 27 14:18 restxq/ 
drwxr-xr-x 1 ssilcot None    0 Jan 27 14:16 static/ : imgs and css here
                                            # refer to these as /static in restxq scripts
drwxr-xr-x 1 ssilcot None    0 Jan 27 14:04 WEB-INF/


Apache
------
Using apache2 from cygwin (a bit flaky/did NOT play well with cygwin postgresql):
* /etc/apache2/httpd.conf
  * additional config for basex
* start: /usr/sbin/apachectl2 start
  ps afx|grep http2


Postgresql
----------

todo.
