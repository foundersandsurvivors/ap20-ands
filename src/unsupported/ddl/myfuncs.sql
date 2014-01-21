/***************************************************************************
 *   myfuncs.sql                                                           *
 *   Yggrasil: Auxiliary PostgreSQL Functions                              *
 *                                                                         *
 *   Copyright (C) 2006-2011 by Leif B. Kristensen <leif@solumslekt.org>   *
 *   All rights reserved. For terms of use, see LICENSE.txt                *
 ***************************************************************************/

-- 'nice to have' functions not called from yggdrasil

CREATE OR REPLACE FUNCTION db_size() RETURNS TEXT AS $$
-- cute little function to determine physical size of db
    SELECT pg_size_pretty(pg_database_size(current_database()));
$$ LANGUAGE sql STABLE;

CREATE OR REPLACE FUNCTION ss_link_expand(TEXT) RETURNS TEXT AS $$
-- expand all compacted links
-- this is the version used by my export module ss_dump.py
DECLARE
    str TEXT;
    tmp TEXT;
BEGIN
    -- the easy part: replace [p=xxx|yyy] with full link
    str := REGEXP_REPLACE($1, E'\\[p=(\\d+?)\\|(.+?)\\]',
            E'<a href="/slekta/famark.php?person=\\1" title="x\\1x">\\2</a>', 'g');
    -- the hard part: replace [p=xxx] with full link
    WHILE str SIMILAR TO E'%\\[p=\\d+\\]%' LOOP
        str := REGEXP_REPLACE(str, E'\\[p=(\\d+?)\\]',
                E'<a href="/slekta/famark.php?person=\\1" title="x\\1x">#\\1#</a>');
        tmp := SUBSTRING(str, E'#\\d+?#');
        str := REPLACE(str, tmp, get_person_name(BTRIM(tmp, '#')::INTEGER));
    END LOOP;
    -- show name with lifespan as tooltip
    WHILE str SIMILAR TO E'%x\\d+x%' LOOP
        tmp := SUBSTRING(str, E'x\\d+?x');
        str := REPLACE(str, tmp, get_person_title(BTRIM(tmp, 'x')::INTEGER));
    END LOOP;
    str := _my_expand(str);
    RETURN str;
END
$$ LANGUAGE plpgsql STABLE;

CREATE OR REPLACE FUNCTION update_family_birthplace(INTEGER, INTEGER) RETURNS VOID AS $$
-- updates birthplace ($1) of all children of a parent ($2) in one operation.
-- use with caution.
UPDATE EVENTS SET place_fk = $1
WHERE event_id IN (
    SELECT event_id FROM events e, participants p, relations r
    WHERE e.tag_fk = 2
    AND e.event_id = p.event_fk
    AND p.person_fk = r.child_fk
    AND r.parent_fk = $2
);
$$ LANGUAGE SQL VOLATILE;

CREATE OR REPLACE FUNCTION child_of(INTEGER,INTEGER) RETURNS SETOF INTEGER AS $$
SELECT p.child_fk FROM relations p, relations q
WHERE p.parent_fk = $1
AND q.parent_fk = $2
AND p.child_fk = q.child_fk
$$ LANGUAGE SQL STABLE;

CREATE OR REPLACE FUNCTION strip_tags(TEXT) RETURNS TEXT AS $$
-- found at http://www.siafoo.net/snippet/148
    SELECT REGEXP_REPLACE(REGEXP_REPLACE($1,
        E'(?x)<[^>]*?(\s alt \s* = \s* ([\'"]) ([^>]*?) \2) [^>]*? >', E'\3'), E'(?x)(< [^>]*? >)', '', 'g')
$$ LANGUAGE SQL IMMUTABLE;

CREATE OR REPLACE FUNCTION set_source_group_date(INTEGER) RETURNS VOID AS $$
-- sets source date of eg. all the pages of a church book, which makes
-- it easy to look up a page from the Source Manager. the source_text
-- update is because at one point in time i inserted years as comments
-- and will eventually be removed from this routine.
DECLARE
    grp INTEGER;
BEGIN
    FOR grp IN SELECT source_id FROM sources WHERE parent_id = $1 LOOP
        UPDATE sources SET
            source_text = BTRIM(REGEXP_REPLACE(source_text, '{.*?}', '', 'g'), ' '),
            source_date =
                (SELECT MIN(source_date) FROM sources WHERE parent_id = grp)
        WHERE source_id = grp;
    END LOOP;
    RETURN;
END
$$ LANGUAGE plpgsql VOLATILE;

CREATE OR REPLACE FUNCTION add_churchbook(node INTEGER, aref TEXT, st TEXT) RETURNS VOID AS $$
-- add churchbook (kb) node and the usual 4 subsources with templates.
-- usage example: select add_churchbook(35,'5935','#7 Mini 7 (1840-1877).');
DECLARE
kbnode  INTEGER; -- node of new kb
sbtext  TEXT; -- template text, inserted into all subnodes
sbarr   TEXT[] := '{"#1 Døpte","#2 Konfirmerte","#3 Viede","#4 Begravde"}';

BEGIN
    kbnode := add_source(node, st); -- returns node of new source
    RAISE NOTICE 'Added node % %', kbnode, st;
    sbtext := '[kb=' || aref || '|-|side ].';
    FOR i IN 1..array_length(sbarr, 1) LOOP
        INSERT INTO templates (source_fk, template)
            VALUES (add_source(kbnode, sbarr[i]), sbtext);
        RAISE NOTICE 'Added subnode %.', sbarr[i];
    END LOOP;
    RETURN;
END
$$ LANGUAGE plpgsql VOLATILE;

CREATE OR REPLACE FUNCTION dp(INTEGER) RETURNS SETOF TEXT AS $$
-- simple html dump of sources
    SELECT '<p>' || strip_tags(ss_link_expand(source_text)) || '</p>'
    FROM sources
    WHERE parent_id=$1
    ORDER BY sort_order
$$ LANGUAGE SQL STABLE;

CREATE OR REPLACE FUNCTION dpp(INTEGER) RETURNS SETOF TEXT AS $$
    SELECT '<p class="packed">' || ss_link_expand(source_text) || '</p>'
    FROM sources
    WHERE parent_id=$1
    ORDER BY sort_order
$$ LANGUAGE SQL STABLE;

CREATE OR REPLACE FUNCTION dn(INTEGER) RETURNS SETOF TEXT AS $$
-- simple numbered dump of sources
    SELECT '#' || sort_order || ' ' || source_text
    FROM sources
    WHERE parent_id=$1
    ORDER BY sort_order
$$ LANGUAGE SQL STABLE;

-- defined here because of dependency with ss_link_expand(TEXT)
CREATE OR REPLACE VIEW public_tmg_events AS
SELECT
    event_id,
    tag_fk,
    person1_fk,
    person2_fk,
    place_fk,
    event_date,
    sort_date,
    ss_link_expand(event_note) AS event_note,
    tag_type
FROM tmg_events
WHERE is_public(person1_fk)
    AND (is_public(person2_fk) OR person2_fk=0)
    AND tag_fk <> 1040;

