#! /usr/bin/env python
# -*- coding: utf-8 -*-
# msrc.py - merge sources
# Usage: merge <node_1> <node_2>
# Merged node will have number of node_2
# part of Exodus/Yggdrasil, leifbk 2005-2011

import sys, psycopg2

"""
# strict version; requires common parent_id
def msrc(old,new):
    connection = psycopg2.connect("dbname=pgslekt")
    sql = connection.cursor()
    sql.execute("SELECT parent_id FROM sources WHERE source_id = %s" % old)
    old_par = sql.fetchone()
    sql.execute("SELECT parent_id FROM sources WHERE source_id = %s" % new)
    new_par = sql.fetchone()
    if new_par != old_par:
        print "Error: Parent id must be the same for both sources"
    else:
        sql.execute("UPDATE event_citations SET source_fk = %s WHERE source_fk = %s" % (new,old))
        sql.execute("UPDATE relation_citations SET source_fk = %s WHERE source_fk = %s" % (new,old))
        sql.execute("UPDATE sources SET parent_id = %s WHERE parent_id = %s" % (new,old))
        sql.execute("DELETE FROM sources WHERE source_id = %s" % (old,))
        sql.execute("COMMIT")
"""

# relaxed version
def msrc(old,new):
    connection = psycopg2.connect("dbname=pgslekt")
    sql = connection.cursor()
    sql.execute("UPDATE event_citations SET source_fk = %s WHERE source_fk = %s" % (new,old))
    sql.execute("UPDATE relation_citations SET source_fk = %s WHERE source_fk = %s" % (new,old))
    sql.execute("UPDATE sources SET parent_id = %s WHERE parent_id = %s" % (new,old))
    sql.execute("DELETE FROM sources WHERE source_id = %s" % (old,))
    sql.execute("COMMIT")


if __name__ == '__main__':
    old = int(sys.argv[1])
    new = int(sys.argv[2])
    msrc(old,new)
