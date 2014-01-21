This directory contains sql scripts for your database. As you may call that
database anything you want, and some manual configuration is ABSOLUTELY REQUIRED
to get this software up and running, I have decided not to write a general
install script. After all, you should know what you're doing when you start
messing around with this software. Please read the README.txt in the main
directory.

Here is a short description of each file.

datadef.sql - basic table definitions.

dbinit.sql - initializes some tables with required starting values. If you're
importing your own data from another source, apply whatever you'll need from
this script.

functions.sql - essential functions for Exodus.

views.sql - essential views for Exodus.

triggers.sql - optional trigger for updating persons.last_edit field on changes
to participants.

misc.sql - deprecated views and functions, kept around for "educational"
purposes.

un_misc.sql - removes the misc stuff from the database. If you have installed
your database from the deprecated views_and_functions.sql, or have run the
misc.sql script, you can remove the cruft with this script.

patch_xxxxxxxx.sql - changes since last "baseline". Those changes are already
incorporated in the main sql scripts, so if you're running an initial setup, you
don't have to worry about this.

