/***************************************************************************
 *   functions.sql                                                         *
 *   Yggdrasil: Essential PostgreSQL Functions                             *
 *                                                                         *
 *   Copyright (C) 2006-2011 by Leif B. Kristensen <leif@solumslekt.org>   *
 *   All rights reserved. For terms of use, see LICENSE.txt                *
 ***************************************************************************/

CREATE OR REPLACE FUNCTION get_lang() RETURNS TEXT AS $$
    SELECT user_lang FROM user_settings WHERE username = current_user
$$ LANGUAGE SQL STABLE;


CREATE OR REPLACE FUNCTION get_parent(INTEGER,INTEGER) RETURNS INTEGER AS $$
SELECT COALESCE(
    (SELECT parent_fk FROM relations r, persons p
        WHERE r.child_fk = $1
            AND r.parent_fk = p.person_id
            AND p.gender = $2), 0)
$$ LANGUAGE sql STABLE;

CREATE OR REPLACE FUNCTION get_pbdate(INTEGER) RETURNS TEXT AS $$
SELECT COALESCE(
    (SELECT event_date::TEXT FROM events e, participants p
        WHERE e.event_id = p.event_fk
        AND p.person_fk = $1
        AND e.tag_fk IN (2,62,1035) LIMIT 1),
    '000000003000000001'::TEXT)
$$ LANGUAGE sql STABLE;

CREATE OR REPLACE FUNCTION get_pddate(INTEGER) RETURNS TEXT AS $$
SELECT COALESCE(
    (SELECT event_date::TEXT FROM events e, participants p
        WHERE e.event_id = p.event_fk
        AND p.person_fk = $1
        AND e.tag_fk IN (3,62) LIMIT 1),
    '000000003000000001'::TEXT)
$$ LANGUAGE sql STABLE;

CREATE OR REPLACE FUNCTION f_year(TEXT) RETURNS INTEGER AS $$
SELECT SUBSTR($1,1,4)::INTEGER
$$ LANGUAGE sql STABLE;

CREATE OR REPLACE FUNCTION age_at_death(INTEGER) RETURNS INTEGER AS $$
SELECT ABS(f_year(get_pddate($1)) - f_year(get_pbdate($1)))::INTEGER
$$ LANGUAGE sql STABLE;

CREATE OR REPLACE FUNCTION get_gender(INTEGER) RETURNS INTEGER AS $$
SELECT
    gender::INTEGER
FROM
    persons
WHERE
    person_id = $1;
$$ LANGUAGE sql STABLE;

CREATE OR REPLACE FUNCTION get_event_type(INTEGER) RETURNS INTEGER AS $$
SELECT
    t.tag_type_fk
FROM
    events e,
    tags t
WHERE
    e.tag_fk = t.tag_id
AND
    e.event_id = $1
$$ LANGUAGE sql STABLE;

CREATE OR REPLACE FUNCTION get_tag_type(INTEGER) RETURNS INTEGER AS $$
SELECT
    t.tag_type_fk
FROM
    events e,
    tags t
WHERE
    e.tag_fk = t.tag_id
AND
    e.event_id = $1
$$ LANGUAGE sql STABLE;

CREATE OR REPLACE FUNCTION get_tag_group(INTEGER) RETURNS INTEGER AS $$
SELECT
    t.tag_group_fk
FROM
    events e,
    tags t
WHERE
    e.tag_fk = t.tag_id
AND
    e.event_id = $1
$$ LANGUAGE sql STABLE;

CREATE OR REPLACE FUNCTION _append(TEXT, TEXT, TEXT) RETURNS TEXT AS $$
-- private string concatenation function
-- used with get_place_name() and get_person_name() below
SELECT
    CASE WHEN $2 <> '' AND $2 NOT LIKE '-%'
    THEN
        $1 || $3 || $2
    ELSE
        $1
    END
$$ LANGUAGE sql STABLE;

CREATE OR REPLACE FUNCTION get_place_name(INTEGER) RETURNS TEXT AS $$
-- return full place name as text
DECLARE
    pl places%ROWTYPE;
    str TEXT;
BEGIN
    str := '';
    SELECT * INTO pl FROM places WHERE place_id = $1;
    str := _append(str, pl.level_1, ', ');
    str := _append(str, pl.level_2, ', ');
    str := _append(str, pl.level_3, ', ');
    str := _append(str, pl.level_4, ', ');
    str := _append(str, pl.level_5, ', ');
    RETURN BTRIM(str, ', ');
END;
$$ LANGUAGE plpgsql STABLE;

CREATE OR REPLACE FUNCTION get_person_name(INTEGER) RETURNS TEXT AS $$
-- return full person name as text
DECLARE
    pe persons%ROWTYPE;
    str TEXT;
BEGIN
    str := '';
    SELECT * INTO pe FROM persons WHERE person_id = $1;
    str := _append(str, pe.given, ' ');
    str := _append(str, pe.patronym, ' ');
    str := _append(str, pe.toponym, ' ');
    str := _append(str, pe.surname, ' ');
    str := _append(str, pe.occupation, ' ');
    str := _append(str, pe.epithet, ' ');
    str := BTRIM(str, ' ');
    IF NOT is_public($1) THEN
        str := str || ' *';
    ELSEIF dead_child($1) THEN
        str := str || ' &#x2020;';
    END IF;
    RETURN str;
END;
$$ LANGUAGE plpgsql STABLE;

CREATE OR REPLACE FUNCTION get_tag_name(INTEGER) RETURNS TEXT AS $$
SELECT COALESCE(
    (SELECT tag_label FROM tags WHERE tag_id = $1),
    '[undefined]')
$$ LANGUAGE sql STABLE;

CREATE OR REPLACE FUNCTION get_tag_name(INTEGER, TEXT) RETURNS TEXT AS $$
SELECT COALESCE(
    CASE WHEN $2 = 'nb'
    THEN
        (SELECT tag_label FROM tags WHERE tag_id = $1)
    ELSE
        (SELECT tag_name FROM tags WHERE tag_id = $1)
    END,
    '[undefined]')
$$ LANGUAGE sql STABLE;

CREATE OR REPLACE FUNCTION get_spt_label(INTEGER) RETURNS TEXT AS $$
-- get localized source part type label
DECLARE
    lbl TEXT;

BEGIN
    EXECUTE
        'SELECT label_' || get_lang() ||
        ' FROM source_part_types WHERE part_type_id = ' || $1
        INTO lbl;
    RETURN lbl;
END
$$ LANGUAGE plpgsql STABLE;

CREATE OR REPLACE FUNCTION _my_expand(TEXT) RETURNS TEXT AS $$
-- private func, expand external compact links
DECLARE
    str TEXT := $1;
    links RECORD;

BEGIN
    FOR links IN SELECT short_link, long_link FROM my_links LOOP
        str := REGEXP_REPLACE(str, links.short_link, links.long_link, 'g');
    END LOOP;
    RETURN str;
END
$$ LANGUAGE plpgsql STABLE;

CREATE OR REPLACE FUNCTION link_expand(TEXT) RETURNS TEXT AS $$
-- expand all compacted links
DECLARE
    str TEXT := $1;
    tmp TEXT;
BEGIN
    -- expand internal shortlinks to persons
    -- replace [p=xxx|yyy] with full link
    str := REGEXP_REPLACE(str, E'\\[p=(\\d+?)\\|(.+?)\\]',
            E'<a href="./family.php?person=\\1" title="@\\1@">\\2</a>', 'g');
    -- replace [p=xxx] with full link
    WHILE str SIMILAR TO E'%\\[p=\\d+\\]%' LOOP
        str := REGEXP_REPLACE(str, E'\\[p=(\\d+?)\\]',
                E'<a href="./family.php?person=\\1" title="@\\1@">#\\1#</a>');
        tmp := SUBSTRING(str, E'#\\d+?#');
        str := REPLACE(str, tmp, get_person_name(BTRIM(tmp, '#')::INTEGER));
    END LOOP;
    -- show person name with lifespan as tooltip
    WHILE str SIMILAR TO E'%@\\d+@%' LOOP
        tmp := SUBSTRING(str, E'@\\d+?@');
        str := REPLACE(str, tmp, get_person_title(BTRIM(tmp, '@')::INTEGER));
    END LOOP;
    -- expand custom external shortlinks
    str := _my_expand(str);
    str := REGEXP_REPLACE(str,
                E'<name role="child" n="(.*?)">(.+?)</name>',
                E'<span class="child" title="\\1">\\2</span>', 'g');
    RETURN str;
END
$$ LANGUAGE plpgsql STABLE;

CREATE OR REPLACE FUNCTION cite_seq(INTEGER) RETURNS INTEGER AS $$
-- stores unique citations in the temporary table tmp_sources.
-- see family.php, function cite()
DECLARE
    seq INTEGER; -- sequence number
BEGIN
    -- check if the source is already cited
    SELECT citation_id FROM tmp_sources WHERE source_id = $1 INTO seq;
    -- if not, store new citation
    IF NOT FOUND THEN
        INSERT INTO tmp_sources (source_id) VALUES ($1)
            RETURNING citation_id INTO seq;
    END IF;
    RETURN seq;
END
$$ LANGUAGE plpgsql VOLATILE;

CREATE OR REPLACE FUNCTION cite_inline(TEXT) RETURNS TEXT AS $$
-- extract/expand all inline citations
DECLARE
    str TEXT := $1;
    tmp TEXT;
    seq TEXT;
BEGIN
    WHILE str SIMILAR TO E'%\\[s=\\d+\\]%' LOOP
        str := REGEXP_REPLACE(str, E'\\[s=(\\d+?)\\]', E'#\\1#');
        tmp := SUBSTRING(str, E'#\\d+?#');
        seq := cite_seq(BTRIM(tmp, '#')::INTEGER);
        str := REPLACE(str, tmp, '<sup>' || seq::TEXT || '</sup>');
    END LOOP;
    RETURN str;
END
$$ LANGUAGE plpgsql STABLE;

CREATE OR REPLACE FUNCTION get_source_text(INTEGER) RETURNS TEXT AS $$
-- return full source string by recursive concatenation
DECLARE
    src sources%ROWTYPE;
    str TEXT;
BEGIN
    SELECT * INTO src FROM sources WHERE source_id = $1;
    str := src.source_text;
    IF src.parent_id <> 0 THEN
        str := get_source_text(src.parent_id) || ' ' || str;
    END IF;
    -- if str ~= ^{.*}$, return as is, else suppress hidden text
    IF str NOT LIKE '{%}' THEN
        str := REGEXP_REPLACE(str, '{.*?}', '', 'g');
        str := REPLACE(str, '  ', ' ');
    END IF;
    str := link_expand(str);
    RETURN COALESCE(BTRIM(str, ' '), '[undefined]');
END;
$$ LANGUAGE plpgsql STABLE;

CREATE OR REPLACE FUNCTION get_coparent(INTEGER, INTEGER) RETURNS INTEGER AS $$
SELECT COALESCE(
    (SELECT parent_fk FROM relations WHERE child_fk = $2
        AND parent_fk <> $1), 0)
$$ LANGUAGE sql STABLE;

CREATE OR REPLACE FUNCTION get_principal(INTEGER) RETURNS INTEGER AS $$
SELECT COALESCE(
    (SELECT person_fk FROM participants WHERE event_fk = $1
        ORDER BY get_gender(person_fk) LIMIT 1), 0)
$$ LANGUAGE sql STABLE;

CREATE OR REPLACE FUNCTION get_principal(INTEGER,INTEGER) RETURNS INTEGER AS $$
-- get coprincipal
SELECT COALESCE(
    (SELECT person_fk FROM participants
        WHERE event_fk = $1 AND person_fk <> $2 LIMIT 1), 0)
$$ LANGUAGE sql STABLE;

CREATE OR REPLACE FUNCTION is_public(INTEGER) RETURNS BOOLEAN AS $$
SELECT CASE WHEN $1 IN (SELECT person_fk FROM private_persons)
THEN FALSE ELSE TRUE END
$$ LANGUAGE sql STABLE;

CREATE OR REPLACE FUNCTION dead_child(INTEGER) RETURNS BOOLEAN AS $$
SELECT CASE WHEN $1 IN (SELECT person_fk FROM dead_children)
THEN TRUE ELSE FALSE END
$$ LANGUAGE sql STABLE;

CREATE OR REPLACE FUNCTION public_id(INTEGER) RETURNS INTEGER AS $$
SELECT CASE WHEN is_public($1) THEN $1 ELSE 0 END
$$ LANGUAGE sql STABLE;

CREATE OR REPLACE FUNCTION get_surety_int(INTEGER, INTEGER) RETURNS INTEGER AS $$
SELECT COALESCE(
    (SELECT surety_fk FROM relations WHERE child_fk = $1 AND parent_fk = $2), 0)
$$ LANGUAGE sql STABLE;

CREATE OR REPLACE FUNCTION ecc(INTEGER) RETURNS INTEGER AS $$
-- count event citations for source node
SELECT COUNT(*)::INTEGER FROM event_citations WHERE source_fk = $1
$$ LANGUAGE sql STABLE;

CREATE OR REPLACE FUNCTION rcc(INTEGER) RETURNS INTEGER AS $$
-- count relation citations for source node
SELECT COUNT(*)::INTEGER FROM relation_citations WHERE source_fk = $1
$$ LANGUAGE sql STABLE;

CREATE OR REPLACE FUNCTION ssc(INTEGER) RETURNS INTEGER AS $$
-- count subsources for source node
SELECT COUNT(*)::INTEGER FROM sources WHERE parent_id = $1
$$ LANGUAGE sql STABLE;

CREATE OR REPLACE FUNCTION is_unused(INTEGER) RETURNS BOOLEAN AS $$
SELECT CASE WHEN (ecc($1) = 0 AND rcc($1) = 0 AND ssc($1) = 0)
THEN TRUE ELSE FALSE END
$$ LANGUAGE sql STABLE;

CREATE OR REPLACE FUNCTION is_used(INTEGER) RETURNS INTEGER AS $$
SELECT CASE WHEN (ecc($1) = 0 AND rcc($1) = 0 AND ssc($1) = 0)
THEN 0 ELSE 1 END
$$ LANGUAGE sql STABLE;

CREATE OR REPLACE FUNCTION usc(INTEGER) RETURNS INTEGER AS $$
-- count unused subsources for source node
SELECT COUNT(*)::INTEGER FROM sources WHERE parent_id = $1
    AND is_unused(source_id)
$$ LANGUAGE sql STABLE;

CREATE OR REPLACE FUNCTION tag_count(INTEGER) RETURNS INTEGER AS $$
SELECT COUNT(*)::INTEGER FROM events WHERE tag_fk = $1
$$ LANGUAGE sql STABLE;

CREATE OR REPLACE FUNCTION part_type_count(INTEGER) RETURNS INTEGER AS $$
SELECT COUNT(*)::INTEGER FROM sources WHERE part_type = $1
$$ LANGUAGE sql STABLE;

CREATE OR REPLACE FUNCTION get_part_type(INTEGER) RETURNS INTEGER AS $$
SELECT part_type FROM sources WHERE source_id = $1
$$ LANGUAGE sql STABLE;

CREATE OR REPLACE FUNCTION part_ldesc(INTEGER) RETURNS TEXT AS $$
SELECT description FROM source_part_types WHERE part_type_id = $1
$$ LANGUAGE sql STABLE;

CREATE OR REPLACE FUNCTION get_role(INTEGER) RETURNS TEXT AS $$
SELECT initcap(role_no) FROM linkage_roles WHERE role_id = $1
$$ LANGUAGE sql STABLE;

CREATE OR REPLACE FUNCTION get_lrole(INTEGER) RETURNS TEXT AS $$
SELECT role_no FROM linkage_roles WHERE role_id = $1
$$ LANGUAGE sql STABLE;

CREATE OR REPLACE FUNCTION get_surety(INTEGER) RETURNS TEXT AS $$
SELECT initcap(surety_no) FROM sureties WHERE surety_id = $1
$$ LANGUAGE sql STABLE;

CREATE OR REPLACE FUNCTION get_lsurety(INTEGER) RETURNS TEXT AS $$
SELECT surety_no FROM sureties WHERE surety_id = $1
$$ LANGUAGE sql STABLE;

CREATE OR REPLACE FUNCTION get_lsurety(INTEGER, TEXT) RETURNS TEXT AS $$
SELECT CASE WHEN $2 = 'nb' THEN surety_no ELSE surety_en END
FROM sureties WHERE surety_id = $1
$$ LANGUAGE sql STABLE;

CREATE OR REPLACE FUNCTION conn_count(INTEGER) RETURNS INTEGER AS $$
SELECT
    (SELECT COUNT(*)::INTEGER FROM participants
        WHERE person_fk = $1) +
    (SELECT COUNT(*)::INTEGER FROM relations
        WHERE child_fk = $1 OR parent_fk = $1)
$$ LANGUAGE sql STABLE;

CREATE OR REPLACE FUNCTION date2text(DATE) RETURNS TEXT AS $$
-- removes hyphens from a regular date
SELECT
    SUBSTR(TEXT($1),1,4) ||
    SUBSTR(TEXT($1),6,2) ||
    SUBSTR(TEXT($1),9,2)
$$ LANGUAGE sql STABLE;

CREATE OR REPLACE FUNCTION is_merged(INTEGER) RETURNS BOOLEAN AS $$
SELECT CASE WHEN $1 IN (SELECT old_person_fk FROM merged)
    THEN TRUE ELSE FALSE END
$$ LANGUAGE sql STABLE;

CREATE OR REPLACE FUNCTION place_count(INTEGER) RETURNS INTEGER AS $$
SELECT COUNT(*)::INTEGER FROM events WHERE place_fk = $1;
$$ LANGUAGE sql STABLE;

CREATE OR REPLACE FUNCTION get_parent_id(INTEGER) RETURNS INTEGER AS $$
-- get 'parent' of source id
SELECT parent_id FROM sources WHERE source_id = $1
$$ LANGUAGE sql STABLE;

CREATE OR REPLACE FUNCTION get_source_gp(INTEGER) RETURNS INTEGER AS $$
-- get 'grandparent' of source id
SELECT parent_id FROM sources WHERE source_id = (
    SELECT parent_id FROM sources WHERE source_id = $1
)
$$ LANGUAGE sql STABLE;

CREATE OR REPLACE FUNCTION get_prev_page(INTEGER) RETURNS INTEGER AS $$
-- return source_id with next lower sort_order from current
-- of set sharing same parent_id,
-- or max(sort_order) from same set
SELECT COALESCE(
    (SELECT source_id
        FROM sources
        WHERE parent_id = get_parent_id($1)
        AND sort_order < (
            SELECT sort_order
            FROM sources
            WHERE source_id = $1
        )
        ORDER BY sort_order DESC LIMIT 1
    ),
    (SELECT source_id
        FROM sources
        WHERE parent_id = get_parent_id($1)
        AND sort_order = (
            SELECT MAX(sort_order)
            FROM sources
            WHERE parent_id = get_parent_id($1)
        )
    ORDER BY source_id LIMIT 1
    )
)
$$ LANGUAGE sql STABLE;

CREATE OR REPLACE FUNCTION get_next_page(INTEGER) RETURNS INTEGER AS $$
-- return source_id with next higher sort_order from current
-- of set sharing same parent_id,
-- or min(sort_order) from same set
SELECT COALESCE(
    (SELECT source_id
        FROM sources
        WHERE parent_id = get_parent_id($1)
        AND sort_order > (
            SELECT sort_order
            FROM sources
            WHERE source_id = $1
        )
        ORDER BY sort_order ASC LIMIT 1
    ),
    (SELECT source_id
        FROM sources
        WHERE parent_id = get_parent_id($1)
        AND sort_order = (
            SELECT MIN(sort_order)
            FROM sources
            WHERE parent_id = get_parent_id($1)
        )
    ORDER BY source_id LIMIT 1
    )
)
$$ LANGUAGE sql STABLE;

CREATE OR REPLACE FUNCTION has_coprincipal(INTEGER) RETURNS BOOLEAN AS $$
SELECT
    CASE WHEN (SELECT tag_type_fk FROM tags WHERE tag_id = $1) = 2
    THEN TRUE ELSE FALSE END
$$ LANGUAGE sql STABLE;

CREATE OR REPLACE FUNCTION is_leaf(INTEGER) RETURNS BOOLEAN AS $$
SELECT is_leaf FROM source_part_types WHERE part_type_id = (
    SELECT part_type FROM sources WHERE source_id = $1
)
$$ LANGUAGE sql STABLE;

CREATE OR REPLACE FUNCTION set_last_selected_place(INTEGER) RETURNS VOID AS $$
-- updates recent_places
    DELETE FROM recent_places WHERE place_fk = $1;
    INSERT INTO recent_places (place_fk) VALUES ($1);
$$ LANGUAGE sql VOLATILE;

CREATE OR REPLACE FUNCTION date_extract(TEXT) RETURNS TEXT AS $$
-- used by Source Manager for sorting subsources in chron. order
-- extracts "German style" dates embedded in text
DECLARE
    s TEXT;
BEGIN
    SELECT SUBSTRING($1, E'\\d{2}\\.\\d{2}\\.\\d{4}') INTO s;
    RETURN
        SUBSTR(s,7,4) || '-' ||
        SUBSTR(s,4,2) || '-' ||
        SUBSTR(s,1,2);
END;
$$ LANGUAGE plpgsql STABLE;

CREATE OR REPLACE FUNCTION true_date_extract(TEXT) RETURNS DATE AS $$
-- get real date from string
-- extracts "German style" dates embedded in text
-- note the "relaxed" date checking; eg. 29.02.2009 is treated
-- as a valid date, but will be returned as '2009-03-01'
DECLARE
    s TEXT;
    d INTEGER;
    m INTEGER;
    y INTEGER;
    mydate DATE;
BEGIN
    SELECT SUBSTRING($1, E'\\d{2}\\.\\d{2}\\.\\d{4}') INTO s;
    d := SUBSTR(s,1,2)::INTEGER;
    m := SUBSTR(s,4,2)::INTEGER;
    y := SUBSTR(s,7,4)::INTEGER;
    IF d >= 1 AND d <= 31 THEN
        IF m >= 1 AND m <= 12 THEN
            IF y >= 1500 AND y <= 2100 THEN
                mydate := to_date(s, 'DD.MM.YYYY');
            END IF;
        END IF;
    END IF;
    RETURN mydate;
END
$$ LANGUAGE plpgsql STABLE;

CREATE OR REPLACE FUNCTION mydate(CHAR(10),TEXT) RETURNS TEXT AS $$
-- takes iso-8601 date string, returns localized date string
-- accepts partial dates eg. 2008-00-00 or 2008-05-00
DECLARE
    d INTEGER;
    m INTEGER;
    y INTEGER;
    mon TEXT;
    str TEXT;
BEGIN
    -- decompose datestring
    y := SUBSTR($1,1,4)::INTEGER;
    m := SUBSTR($1,6,2)::INTEGER;
    d := SUBSTR($1,9,2)::INTEGER;
    EXECUTE 'SELECT ' || $2 || ' FROM months WHERE id = ' || m INTO mon;
    IF $2 = 'en' THEN -- [M] [d] Y
        str := y::TEXT;
        IF d <> 0 THEN
            str := d::TEXT || ', ' || str;
        END IF;
        IF m <> 0 THEN
            str := mon || ' ' || str;
        END IF;
    END IF;
    IF $2 = 'nb' THEN -- [d.] [m] Y
        str := y::TEXT;
        IF m <> 0 THEN
            str := mon || ' ' || str;
        END IF;
        IF d <> 0 THEN
            str := d::TEXT || '. ' || str;
        END IF;
    END IF;
    -- catch "empty" dates
    IF y = 0 THEN
        str := '';
    END IF;
    RETURN str;
END;
$$ LANGUAGE plpgsql STABLE;

CREATE OR REPLACE FUNCTION fuzzydate(CHAR(18),TEXT) RETURNS TEXT AS $$
DECLARE
    q INTEGER;
    str TEXT;
    date1 TEXT;
    date2 TEXT;
BEGIN
    str := SUBSTR($1,1,4) || '-' || SUBSTR($1,5,2) || '-' || SUBSTR($1,7,2);
    date1 := mydate(str, $2);
    str := SUBSTR($1,10,4) || '-' || SUBSTR($1,14,2) || '-' || SUBSTR($1,16,2);
    date2 := mydate(str, $2);
    q := SUBSTR($1,9,1)::INTEGER;
    IF $2 = 'en' THEN
        IF q = 0 THEN
            str = 'before ' || date1;
        ELSIF q = 1 THEN
            str = 'around ' || date1;
        ELSIF q = 2 THEN
            str = 'ca. ' || date1;
        ELSIF q = 3 THEN
            str = date1;
        ELSIF q = 4 THEN
            str = 'after ' || date1;
        ELSIF q = 5 THEN
            str = 'between ' || date1 || ' and ' || date2;
        ELSIF q = 6 THEN
            str = date1 || ' or ' || date2;
        ELSIF q = 7 THEN
            str = 'from ' || date1 || ' to ' || date2;
        ELSE
            RAISE NOTICE 'Invalid qualifier %', q;
            str = '';
        END IF;
    END IF;
    IF $2 = 'nb' THEN
        IF q = 0 THEN
            str = 'før ' || date1;
        ELSIF q = 1 THEN
            str = 'rundt ' || date1;
        ELSIF q = 2 THEN
            str = 'ca. ' || date1;
        ELSIF q = 3 THEN
            str = date1;
        ELSIF q = 4 THEN
            str = 'etter ' || date1;
        ELSIF q = 5 THEN
            str = 'mellom ' || date1 || ' og ' || date2;
        ELSIF q = 6 THEN
            str = date1 || ' eller ' || date2;
        ELSIF q = 7 THEN
            str = 'fra ' || date1 || ' til ' || date2;
        ELSE
            RAISE NOTICE 'Invalid qualifier %', q;
            str = '';
        END IF;
    END IF;
    RETURN str;
END;
$$ LANGUAGE plpgsql STABLE;

CREATE OR REPLACE FUNCTION get_person_title(INTEGER) RETURNS TEXT AS $$
SELECT get_person_name($1) ||
    ' (' || fuzzydate(get_pbdate($1)::CHAR(18),'nb') ||
    ' - ' || fuzzydate(get_pddate($1)::CHAR(18),'nb') || ')';
$$ LANGUAGE SQL STABLE;

CREATE OR REPLACE FUNCTION prepose(INTEGER, TEXT) RETURNS TEXT AS $$
-- get connective preposition for type 2 event
SELECT COALESCE(
    (SELECT preposition
        FROM tag_prepositions
        WHERE tag_fk = $1
            AND lang_code = $2),
    (SELECT preposition
        FROM default_prepositions
        WHERE lang_code = $2)
)
$$ LANGUAGE sql STABLE;

CREATE OR REPLACE FUNCTION get_sort(INTEGER, INTEGER, TEXT) RETURNS int_text AS $$
-- parse source text to infer sort order. Note that this function utilizes a
-- micro-language of "commands" which may be embedded in the input text.
-- CREATE TYPE int_text AS (number INTEGER, string TEXT)
DECLARE
    par_id INTEGER := $1;
    srt INTEGER := $2;
    txt TEXT := $3;
    lang TEXT; -- language code
    sort_text int_text;
BEGIN
    -- default condition: if nothing is modified, return input values
    sort_text.number := srt;
    sort_text.string := txt;
    -- 1) use page number for sort order (low priority, may be overridden)
    SELECT get_lang() INTO lang;
    IF srt = 1 THEN -- don't apply this rule unless sort = default
        IF lang = 'en' THEN
            IF txt SIMILAR TO E'%page \\d+%' THEN
                sort_text.number := (REGEXP_MATCHES(txt, E'page (\\d+)'))[1]::INTEGER;
            END IF;
        END IF;
        IF lang = 'nb' THEN
            IF txt SIMILAR TO E'%side \\d+%' THEN
                sort_text.number := (REGEXP_MATCHES(txt, E'side (\\d+)'))[1]::INTEGER;
            END IF;
        END IF;
    END IF;
    -- 2) use ^#(\d+) for sort order
    IF txt SIMILAR TO E'#\\d+%' THEN
        sort_text.number := (REGEXP_MATCHES(txt, E'^#(\\d+)'))[1]::INTEGER;
        sort_text.string := REGEXP_REPLACE(txt, E'^#\\d+ ', ''); -- strip #n from text
    END IF;
    -- 3) use ^!(\d+) for sort order, increment sort order for those above
    -- in the same group, effectively making an insert
    IF txt SIMILAR TO E'!\\d+%' THEN
        sort_text.number := (REGEXP_MATCHES(txt, E'^!(\\d+)'))[1]::INTEGER;
        UPDATE sources SET sort_order = sort_order + 1
            WHERE get_source_gp(source_id) =
                (SELECT parent_id FROM sources WHERE source_id = par_id)
            AND sort_order >= sort_text.number;
        sort_text.string := REGEXP_REPLACE(txt, E'^!\\d+ ', ''); -- strip !n from text
    END IF;
    -- 4) use ^=(\d+) for sort order, increment sort order for those above
    -- with the same parent node, effectively making an insert
    IF txt SIMILAR TO E'=\\d+%' THEN
        sort_text.number := (REGEXP_MATCHES(txt, E'^=(\\d+)'))[1]::INTEGER;
        UPDATE sources SET sort_order = sort_order + 1
            WHERE parent_id = par_id
            AND sort_order >= sort_text.number;
        sort_text.string := REGEXP_REPLACE(txt, E'^=\\d+ ', ''); -- strip !n from text
    END IF;
    -- 5) increment from max(sort_order) of source group
    IF txt LIKE '++ %' THEN
        SELECT MAX(sort_order) + 1
            FROM sources
            WHERE get_source_gp(source_id) =
                (SELECT parent_id FROM sources WHERE source_id = par_id)
        INTO sort_text.number;
        sort_text.string := REPLACE(txt, '++ ', ''); -- strip symbol from text
    END IF;
    RETURN sort_text;
END
$$ LANGUAGE plpgsql VOLATILE;

CREATE OR REPLACE FUNCTION get_source_type(TEXT) RETURNS int_text AS $$
-- parse source text to get source type, modifies text.
-- source type input as §n first or immediately after sort directive
DECLARE
    txt TEXT := $1;
    src_text int_text;

BEGIN
    -- default condition: if nothing is modified, return input values
    src_text.number := 0;
    src_text.string := txt;
    IF txt SIMILAR TO E'§\\d+%' THEN
        src_text.number := (REGEXP_MATCHES(txt, E'^§(\\d+)'))[1]::INTEGER;
        src_text.string := REGEXP_REPLACE(txt, E'^§\\d+ ', ''); -- strip §n from text
    END IF;
    RETURN src_text;
END
$$ LANGUAGE plpgsql VOLATILE;

CREATE OR REPLACE FUNCTION add_source(INTEGER,INTEGER,INTEGER,INTEGER,TEXT,INTEGER) RETURNS INTEGER AS $$
-- Inserts sources and citations, returns current source_id
-- 2009-03-26: this func has finally been moved from PHP to the db.
-- 2011-06-13: Modified after changing source_id to type SERIAL
-- Should be called via the functions.php add_source() which is left as a gatekeeper.
DECLARE
    person  INTEGER := $1;
    tag     INTEGER := $2;
    event   INTEGER := $3;
    src_id  INTEGER := $4;
    txt     TEXT    := $5;
    srt     INTEGER := $6;
    par_id  INTEGER;
    rel_id  INTEGER;
    x       INTEGER;
    pt      INTEGER;
BEGIN
    IF LENGTH(txt) <> 0 THEN -- source text has been entered, add new node
        par_id := src_id;
        -- parse text to infer sort order
        SELECT number, string FROM get_sort(par_id, srt, txt) INTO srt, txt;
        -- get source type from parent source
        SELECT ch_part_type FROM sources WHERE source_id = par_id INTO pt;
        -- there's a unique constraint on (parent_id, source_text) in the sources table, don't violate it.
        -- get part type from text if part type is undefined
        IF pt = 0 THEN
            SELECT number, string FROM get_source_type(txt) INTO pt, txt;
        END IF;
        SELECT source_id FROM sources WHERE parent_id = par_id AND source_text = txt INTO x;
        IF NOT FOUND THEN
            INSERT INTO sources (parent_id, source_text, sort_order, source_date, part_type)
                VALUES (par_id, txt, srt, true_date_extract(txt), pt) RETURNING source_id INTO src_id;
        ELSE
            RAISE NOTICE 'Source % has the same parent id and text as you tried to enter.', x;
            RETURN -x; -- abort the transaction and return the offended source id as a negative number.
        END IF;
        -- the rest of the code will only be executed if the source is already associated with a person-event,
        -- ie. the source has been entered from the add/edit event forms.
        IF event <> 0 THEN
            -- if new cit. is expansion of an old one, we may remove the "parent node" citation
            DELETE FROM event_citations WHERE event_fk = event AND source_fk = par_id;
            -- Details about a birth event will (almost) always include parental evidence. Therefore, we'll
            -- update relation_citations if birth event (and new source is an expansion of existing source)
            IF tag = 2 THEN
                FOR rel_id IN SELECT relation_id FROM relations WHERE child_fk = person LOOP
                    INSERT INTO relation_citations (relation_fk, source_fk) VALUES (rel_id, src_id);
                    -- again, remove references to "parent node"
                    DELETE FROM relation_citations WHERE relation_fk = rel_id AND source_fk = par_id;
                END LOOP;
            END IF;
        END IF;
    END IF;
    -- associate source node with event
    IF event <> 0 THEN
        -- don't violate unique constraint on (source_fk, event_fk) in the event_citations table.
        -- if this source-event association already exists, it's rather pointless to repeat it.
        PERFORM * FROM event_citations WHERE event_fk = event AND source_fk = src_id;
        IF NOT FOUND THEN
                INSERT INTO event_citations (event_fk, source_fk) VALUES (event, src_id);
            ELSE
                RAISE NOTICE 'citation exists';
            END IF;
    END IF;
    RETURN src_id;
END
$$ LANGUAGE PLPGSQL VOLATILE;

CREATE OR REPLACE FUNCTION add_source(INTEGER,TEXT) RETURNS INTEGER AS $$
-- overload of above function with only two input params
-- parent node and text, everything else default
    SELECT add_source(0, 0, 0, $1, $2, 1);
$$ LANGUAGE SQL VOLATILE;


CREATE OR REPLACE FUNCTION add_birth(INTEGER, TEXT, INTEGER, INTEGER) RETURNS INTEGER AS $$
-- synthesize birth event based on age info
DECLARE
    person      INTEGER := $1;   -- person id
    mydate      TEXT    := $2;   -- fuzzy date of event
    age         INTEGER := $3;   -- age of person at event
    src_id      INTEGER := $4;   -- source id
    birth_year  INTEGER;         -- inferred year of birth
    event       INTEGER;         -- id of inserted birth event
BEGIN
    birth_year := SUBSTR(mydate, 1, 4)::INTEGER - age;
    INSERT INTO events (tag_fk, place_fk, event_date, sort_date)
        VALUES (2, 1, birth_year::TEXT || '00002000000001',
            (birth_year::TEXT || '-01-01')::DATE) RETURNING event_id INTO event;
    INSERT INTO participants (person_fk, event_fk) VALUES (person, event);
    IF src_id <> 0 THEN
        INSERT INTO event_citations VALUES (event, src_id);
    END IF;
    RETURN event;
END
$$ LANGUAGE PLPGSQL VOLATILE;

CREATE OR REPLACE FUNCTION filtered_places() RETURNS SETOF places AS $$
DECLARE
    fl RECORD;
    pl places%ROWTYPE;
    -- place_filter_level   | level_3
    -- place_filter_content | %

BEGIN
    SELECT
        place_filter_level AS _mkey,
        place_filter_content AS _mval
    FROM
        user_settings
    WHERE
        username = current_user
    INTO fl;
    FOR pl IN EXECUTE 'SELECT * FROM places WHERE ' || fl._mkey ||
            ' LIKE ' || QUOTE_LITERAL(fl._mval) || ' OR place_id = 1' LOOP
        RETURN NEXT pl;
    END LOOP;
    RETURN;
END
$$ LANGUAGE plpgsql STABLE;


CREATE OR REPLACE FUNCTION add_participant(INTEGER, INTEGER) RETURNS INTEGER AS $$
-- add participants to event, set sort order automatically
DECLARE
    srt INTEGER;
    per INTEGER := $1;
    evt INTEGER := $2;
BEGIN
    IF (SELECT tag_fk FROM events WHERE event_id = evt) IN (4,5,23) THEN
        srt := get_gender(per);
    ELSE
        SELECT COUNT(*) + 1 FROM participants WHERE event_fk = evt INTO srt;
    END IF;
    INSERT INTO participants (person_fk, event_fk, sort_order) VALUES (per, evt, srt);
    RETURN srt;
END
$$ LANGUAGE PLPGSQL VOLATILE;

CREATE OR REPLACE FUNCTION add_participants(TEXT, INTEGER) RETURNS INTEGER AS $$
-- add space-separated list of participant IDs to event,
-- intended for type 3 events such as census for an entire household
DECLARE
    arr TEXT ARRAY;
    ret INTEGER;
BEGIN
    arr := regexp_split_to_array($1, E'\\s+');
    FOR i IN 1..array_length(arr, 1) LOOP
        SELECT add_participant(arr[i]::INTEGER, $2) INTO ret;
        RAISE NOTICE 'Added %', get_person_name(arr[i]::INTEGER);
    END LOOP;
    RETURN ret;
END
$$ LANGUAGE plpgsql VOLATILE;

CREATE OR REPLACE FUNCTION add_participants_from_links(INTEGER) RETURNS INTEGER AS $$
-- auto-add participants to multi-person events from [p=\d+ type links
-- NOTE: this routine will remove previously associated participants
-- $1: id of source event
DECLARE
    link_id     INTEGER;        -- id of linked person
    srt         INTEGER;
    link_count  INTEGER := 0;   -- return value: number of linked persons
    mynote      TEXT;
BEGIN
    -- copy event note
    SELECT event_note FROM events WHERE event_id = $1 INTO mynote;
    -- auto-cleanup
    DELETE FROM participants WHERE event_fk = $1;
    WHILE mynote SIMILAR TO E'%\\[p=\\d+%' LOOP
        -- sort order = count of participants + 1
        SELECT COUNT(*) + 1 FROM participants WHERE event_fk = $1 INTO srt;
        -- extract linked person id from first non-processed link
        link_id := (REGEXP_MATCHES(mynote, E'\\[p=(\\d+)'))[1]::INTEGER;
        RAISE NOTICE 'Added %', get_person_name(link_id);
        INSERT INTO participants (person_fk, event_fk, sort_order, is_principal)
            VALUES (link_id, $1, srt, TRUE);
        -- mark this link as processed
        mynote := REGEXP_REPLACE(mynote, E'\\[p=', E'\\[#p=');
        link_count = link_count + 1;
    END LOOP;
    RETURN link_count;
END
$$ LANGUAGE PLPGSQL VOLATILE;

CREATE OR REPLACE FUNCTION generate_probate_witnesses(INTEGER) RETURNS INTEGER AS $$
-- auto-add "inheritors" to probate events from [p=\d+ type links
-- $1: id of source event
DECLARE
    link_id     INTEGER;        -- id of linked person
    srt         INTEGER;
    link_count  INTEGER := 0;   -- return value: number of linked persons
    mynote      TEXT;
BEGIN
    -- copy event note
    SELECT event_note FROM events WHERE event_id = $1 INTO mynote;
    -- as this routine performs automated and trivial changes in person info,
    -- i don't feel it warranted to update last_edit for the participants
    ALTER TABLE participants DISABLE TRIGGER update_last_edit;
    -- auto-cleanup in case procedure has been run before
    DELETE FROM participants WHERE event_fk = $1 AND is_principal IS FALSE;
    WHILE mynote SIMILAR TO E'%\\[p=\\d+%' LOOP
        -- sort order = count of participants + 1
        SELECT COUNT(*) + 1 FROM participants WHERE event_fk = $1 INTO srt;
        -- extract linked person id from first non-processed link
        link_id := (REGEXP_MATCHES(mynote, E'\\[p=(\\d+)'))[1]::INTEGER;
        RAISE NOTICE 'Added %', get_person_name(link_id);
        INSERT INTO participants (person_fk, event_fk, sort_order, is_principal)
            VALUES (link_id, $1, srt, FALSE);
        -- mark this link as processed
        mynote := REGEXP_REPLACE(mynote, E'\\[p=', E'\\[#p=');
        link_count = link_count + 1;
    END LOOP;
    ALTER TABLE participants ENABLE TRIGGER update_last_edit;
    RETURN link_count;
END
$$ LANGUAGE PLPGSQL VOLATILE;
