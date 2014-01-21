-- patches_001 started on 2011-06-09

-- Rev. 13, 2011-06-09

-- Extend user settings
-- See also new page forms/user_settings.php
-- And changes to settings/settings.php
ALTER TABLE user_settings ADD COLUMN user_lang TEXT NOT NULL DEFAULT 'en';
ALTER TABLE user_settings ADD COLUMN user_tz TEXT NOT NULL DEFAULT 'Europe/Oslo';
ALTER TABLE user_settings ADD COLUMN user_full_name TEXT NOT NULL DEFAULT '';
ALTER TABLE user_settings ADD COLUMN user_email TEXT NOT NULL DEFAULT '';

-- Place level descriptions
CREATE TABLE place_level_desc (
    place_level_id      INTEGER PRIMARY KEY,
    place_level_name    TEXT NOT NULL DEFAULT '',
    desc_en             TEXT NOT NULL DEFAULT '',
    desc_nb             TEXT NOT NULL DEFAULT ''
);

-- Initial definitions
INSERT INTO place_level_desc VALUES (1, 'level_1', 'Detail', 'Detalj');
INSERT INTO place_level_desc VALUES (2, 'level_2', 'City', 'Sogn');
INSERT INTO place_level_desc VALUES (3, 'level_3', 'County', 'Herred');
INSERT INTO place_level_desc VALUES (4, 'level_4', 'State', 'Fylke');
INSERT INTO place_level_desc VALUES (5, 'level_5', 'Country', 'Land');

-- language dependent version of get_lsurety()
CREATE OR REPLACE FUNCTION get_lsurety(INTEGER, TEXT) RETURNS TEXT AS $$
SELECT CASE WHEN $2 = 'nb' THEN surety_no ELSE surety_en END
FROM sureties WHERE surety_id = $1
$$ LANGUAGE sql STABLE;

-- Above queries have all been integrated in datadef.sql and functions.sql

-- Rev. 14, 2011-06-09
-- Extend source_part_types and add some basic definitions

ALTER TABLE source_part_types ADD COLUMN label_en TEXT NOT NULL DEFAULT '';
ALTER TABLE source_part_types ADD COLUMN label_nb TEXT NOT NULL DEFAULT '';

-- Default value; should never be used in a live database
-- INSERT INTO source_part_types VALUES (0, 'Undefined', FALSE, 'Undef', 'Udef');
UPDATE source_part_types SET label_en='Undef.', label_nb='Udef.' WHERE part_type_id = 0;

-- The following definitions are suggestions only; you may comment out this
-- section if you have another plan. I'd love to discuss the general outline
-- and maybe arrive at a 'canonical' version of this table.

-- 1. add some very basic source part types. Keep labels short and concise.
-- note the is_leaf attribute; it should be used for source types reserved for
-- actual source transcripts, and means that they can't have subsources
INSERT INTO source_part_types VALUES (1, 'Birth record', TRUE, 'Birth', 'Fødsel');
INSERT INTO source_part_types VALUES (2, 'Marriage record', TRUE, 'Marriage', 'Ekteskap');
INSERT INTO source_part_types VALUES (3, 'Death record', TRUE, 'Death', 'Død');
-- I'm leaving a gap here. Although the numbering is unessential, I suggest to
-- add frequently used primary source record transcript types as 4-10
-- I'm using Type 4 for confirmations

-- Here's one that may be confusing. Use type 15 ('area') below for branches in
-- your enumeration subtree. The household record is a leaf.
INSERT INTO source_part_types VALUES (5, 'Enumeration household', TRUE, 'Enum.', 'Enum.');

-- 2. The following part types are 'branches'
INSERT INTO source_part_types VALUES (11, 'Page', FALSE, 'Page', 'Side');
INSERT INTO source_part_types VALUES (12, 'Chapter', FALSE, 'Chapter', 'Kapittel');
INSERT INTO source_part_types VALUES (13, 'Section', FALSE, 'Section', 'Seksjon');
INSERT INTO source_part_types VALUES (14, 'Volume', FALSE, 'Volume', 'Bind');
INSERT INTO source_part_types VALUES (15, 'Book', FALSE, 'Book', 'Bok');
-- 'jurisdiction' in a very general sense; any kind of area within legal limits
INSERT INTO source_part_types VALUES (16, 'Jurisdiction', FALSE, 'Area', 'Område');
INSERT INTO source_part_types VALUES (17, 'Main Category', FALSE, 'Main cat.', 'Hovedgruppe');

-- Above queries have all been integrated in datadef.sql

-- Rev. 16/17/18/19, 2011-06-12
-- Adding sequence to persons
-- Cf. changes to ddl/datadef.sql and forms/person_insert.php
-- Cf. blog post http://solumslekt.org/blog/?p=321
CREATE SEQUENCE persons_person_id_seq;
SELECT SETVAL('persons_person_id_seq', MAX(person_id)) FROM persons;
ALTER TABLE persons ALTER COLUMN person_id SET DEFAULT NEXTVAL('persons_person_id_seq');
ALTER SEQUENCE persons_person_id_seq OWNED BY persons.person_id;
-- delete 'Enoch Root'
DELETE FROM persons WHERE person_id = 0;

-- Above queries have all been integrated in datadef.sql and dbinit.sql

-- Rev. 20, 2011-06-12
-- Renamed cols in tag_groups to facilitate i18n
-- Affected files:
--      ddl/datadef.sql
--      forms/forms.php
--      tag_manager.php
ALTER TABLE tag_groups RENAME COLUMN tag_group_name TO tag_group_name_en;
ALTER TABLE tag_groups RENAME COLUMN tag_group_label TO tag_group_name_nb;

-- Added sequence to places
-- Affected files:
--      ddl/datadef.sql
--      forms/place_edit.php
CREATE SEQUENCE places_place_id_seq;
SELECT SETVAL('places_place_id_seq', MAX(place_id)) FROM places;
ALTER TABLE places ALTER COLUMN place_id SET DEFAULT NEXTVAL('places_place_id_seq');
ALTER SEQUENCE places_place_id_seq OWNED BY places.place_id;

-- Above queries have all been integrated in datadef.sql

-- Rev. 24, 2011-06-13
-- Cleanup, drop some obsolete funcs to access source part type labels,
-- replaced by joins
-- Affected files:
--      ddl/functions.sql
DROP FUNCTION part_desc(INTEGER);
DROP FUNCTION get_part_type_string(INTEGER);

-- Above queries have all been integrated in functions.sql

-- Rev. 25, 2011-06-13
-- Added sequence to sources
-- Affected files:
--      ddl/datadef.sql
--      ddl/functions.sql
CREATE SEQUENCE sources_source_id_seq;
SELECT SETVAL('sources_source_id_seq', MAX(source_id)) FROM sources;
ALTER TABLE sources ALTER COLUMN source_id SET DEFAULT NEXTVAL('sources_source_id_seq');
ALTER SEQUENCE sources_source_id_seq OWNED BY sources.source_id;

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

-- Above queries have all been integrated in datadef.sql and functions.sql

-- Rev. 26, 2011-06-14
-- Added sequence to events
-- Affected files:
--      ddl/datadef.sql
--      ddl/functions.sql
--      forms/event_insert.php
--      forms/person_insert.php
--      forms/person_merge.php
CREATE SEQUENCE events_event_id_seq;
SELECT SETVAL('events_event_id_seq', MAX(event_id)) FROM events;
ALTER TABLE events ALTER COLUMN event_id SET DEFAULT NEXTVAL('events_event_id_seq');
ALTER SEQUENCE events_event_id_seq OWNED BY events.event_id;

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

-- Above queries have all been integrated in datadef.sql and functions.sql

-- Rev. 27, 2011-06-14
-- Added sequence to relations
-- Affected files:
--      ddl/datadef.sql
--      forms/person_insert.php
--      forms/relation_edit.php
CREATE SEQUENCE relations_relation_id_seq;
SELECT SETVAL('relations_relation_id_seq', MAX(relation_id)) FROM relations;
ALTER TABLE relations ALTER COLUMN relation_id SET DEFAULT NEXTVAL('relations_relation_id_seq');
ALTER SEQUENCE relations_relation_id_seq OWNED BY relations.relation_id;

-- Above queries have all been integrated in datadef.sql

-- Rev. 29, 2011-06-18
-- Added function get_lang(); amended function get_sort()
-- Affected files:
--      ddl/functions.sql
CREATE OR REPLACE FUNCTION get_lang() RETURNS TEXT AS $$
SELECT user_lang FROM user_settings WHERE username = current_user
$$ LANGUAGE SQL STABLE;

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

-- Above functions have been integrated in functions.sql
