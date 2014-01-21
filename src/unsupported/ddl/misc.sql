/***************************************************************************
 *   misc.sql                                                              *
 *   Yggdrasil: Miscellaneous PostgreSQL Views and Functions,              *
 *   not essential to the PHP application                                  *
 *                                                                         *
 *   Copyright (C) 2006-2011 by Leif B. Kristensen <leif@solumslekt.org>   *
 *   All rights reserved. For terms of use, see LICENSE.txt                *
 ***************************************************************************/

/*
 * The functions and views of this file are experimental, obsolete or
 * non-essential to the application. They are mostly kept around for
 * "educational" purposes.
 */

CREATE OR REPLACE FUNCTION insp(INTEGER,INTEGER) RETURNS SETOF RECORD AS $$
    UPDATE participants SET is_principal=FALSE WHERE event_fk=$1 AND is_principal IS TRUE;
    INSERT INTO participants (event_fk, person_fk, is_principal, sort_order)
        VALUES ($1, $2, FALSE, (SELECT MAX(sort_order) + 1 FROM participants WHERE event_fk = $1));
    SELECT event_fk, person_fk, is_principal, sort_order FROM participants WHERE event_fk = $1;
$$ LANGUAGE sql VOLATILE;

CREATE OR REPLACE FUNCTION msrc(INTEGER,INTEGER) RETURNS VOID AS $$
    UPDATE event_citations SET source_fk = $2 WHERE source_fk = $1;
    UPDATE relation_citations SET source_fk = $2 WHERE source_fk = $1;
    UPDATE sources SET parent_id = $2 WHERE parent_id = $1;
    DELETE FROM sources WHERE source_id = $1;
$$ LANGUAGE sql VOLATILE;

CREATE OR REPLACE FUNCTION get_page_num(INTEGER,INTEGER) RETURNS INTEGER AS $$
-- extract page number from source text
-- takes parent id and page number
-- returns source number
DECLARE
    par_id ALIAS FOR $1;
    page ALIAS FOR $2;
    src_id INTEGER;
BEGIN
    SELECT source_id FROM sources INTO src_id
    WHERE (SELECT SUBSTRING(source_text, 'side ([0-9]{1,})')::INTEGER) = page
    AND parent_id = par_id;
    RETURN COALESCE(src_id,0);
END;
$$ LANGUAGE plpgsql STABLE;

CREATE OR REPLACE FUNCTION source_count(INTEGER) RETURNS TEXT AS $$
-- source count overview, returns number of citations and subsources
DECLARE
    ec INTEGER;
    rc INTEGER;
    sc INTEGER;
    sstr TEXT;
BEGIN
    SELECT ecc($1) INTO ec;
    SELECT rcc($1) INTO rc;
    SELECT ssc($1) INTO sc;
    sstr := ec || ' event citations, ' || rc || ' relation citations, ' || sc || ' subsources.';
    RETURN sstr;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION cit_count(INTEGER) RETURNS INTEGER AS $$
DECLARE
    ec INTEGER;
    rc INTEGER;
BEGIN
    SELECT COUNT(*) FROM event_citations INTO ec WHERE source_fk = $1;
    SELECT COUNT(*) FROM relation_citations INTO rc WHERE source_fk = $1;
    RETURN ec+rc;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION f_type(TEXT) RETURNS INTEGER AS $$
-- returns type of fuzzy date construct as integer.
SELECT
    SUBSTR(TEXT($1),9,1)::INTEGER
$$ LANGUAGE sql STABLE;

CREATE OR REPLACE FUNCTION age_diff(TEXT,TEXT) RETURNS INTEGER AS $$
    SELECT ABS(f_year($1) - f_year($2))
$$ LANGUAGE sql STABLE;

CREATE OR REPLACE FUNCTION get_free_source_number(INTEGER) RETURNS INTEGER AS $$
-- finds first free source number counting downwards from $1
-- this func was obsoleted when I started to backup with pg_dump
DECLARE
    n INTEGER;
    counter INTEGER;
BEGIN
    counter = $1;
    <<indefinite>>
    LOOP
        SELECT source_id FROM sources INTO n WHERE source_id = counter;
        EXIT indefinite WHEN n IS NULL;
        counter = counter - 1;
    END LOOP;
    RETURN counter;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION pc_count(INTEGER) RETURNS INTEGER AS $$
-- finds number of parents and children for a person.
-- its primary use is for finding unlinked persons
    SELECT COUNT(*)::INTEGER FROM relations WHERE (child_fk = $1 OR parent_fk = $1);
$$ LANGUAGE sql STABLE;

CREATE OR REPLACE FUNCTION spouse_count(INTEGER) RETURNS INTEGER AS $$
-- finds number of spouses for a person.
-- its primary use is for finding unlinked persons
    SELECT COUNT(*)::INTEGER FROM marriages WHERE person = $1;
$$ LANGUAGE sql STABLE;

CREATE OR REPLACE FUNCTION page_extract(INTEGER) RETURNS INTEGER AS $$
-- extracts "page number" (page = side) from text
DECLARE
    s INTEGER;
    t TEXT;
BEGIN
    SELECT source_text FROM sources INTO t
        WHERE source_id = $1;
    SELECT SUBSTRING(t, 'side ([0-9]{1,})')::INTEGER INTO s;
    RETURN COALESCE(s,0);
END;
$$ LANGUAGE plpgsql STABLE;

CREATE OR REPLACE FUNCTION ftup(INTEGER,INTEGER) RETURNS TEXT AS $$
-- special func for mass updating
DECLARE
    person ALIAS FOR $1;
    src ALIAS FOR $2;
    ec INTEGER;
    rc INTEGER;
BEGIN
    UPDATE event_citations SET source_fk = src
    WHERE event_fk IN
        (SELECT event_fk FROM participants
        WHERE person_fk = person)
    AND source_fk = 27;
    GET DIAGNOSTICS ec = ROW_COUNT;
    UPDATE relation_citations SET source_fk = src
    WHERE relation_fk IN
        (SELECT relation_id FROM relations
         WHERE child_fk = person)
    AND source_fk = 27;
    GET DIAGNOSTICS rc = ROW_COUNT;
    RETURN ec || ' events, ' || rc || ' relations.';
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE VIEW unlinked AS
SELECT persons.person_id
    FROM persons
    WHERE pc_count(persons.person_id) = 0
    AND spouse_count(persons.person_id) = 0
    AND NOT (persons.person_id IN (SELECT merged.old_person_fk FROM merged))
ORDER BY persons.person_id;

CREATE FUNCTION pl_update(integer, integer) RETURNS text AS $$
    -- update places in census events, one household at a time
DECLARE
    rc INTEGER;
    plc ALIAS FOR $1;
    src ALIAS FOR $2;
BEGIN
    UPDATE EVENTS SET place_fk = plc
    WHERE event_id IN
        (SELECT event_id FROM events, event_citations
        WHERE event_id = event_fk
        AND source_fk = src
        AND tag_fk = 19);
    GET DIAGNOSTICS rc = ROW_COUNT;
    RETURN 'updated ' || rc;
END;
$$ LANGUAGE plpgsql VOLATILE;
