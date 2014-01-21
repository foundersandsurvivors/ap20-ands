/***************************************************************************
 *   views.sql                                                             *
 *   Exodus: Essential PostgreSQL Views                                    *
 *                                                                         *
 *   Copyright (C) 2006-2011 by Leif B. Kristensen <leif@solumslekt.org>   *
 *   All rights reserved. For terms of use, see LICENSE.txt                *
 ***************************************************************************/

CREATE OR REPLACE VIEW tmg_persons AS
-- reconstruct TMG person table as view
SELECT
    persons.person_id AS person_id,
    get_parent(person_id,1) AS father_id,
    get_parent(person_id,2) AS mother_id,
    last_edit,
    get_pbdate(person_id) AS pb_date,
    get_pddate(person_id) AS pd_date,
    gender AS s,
    is_public(person_id) AS p
FROM persons;

CREATE OR REPLACE VIEW public_tmg_persons AS
SELECT
    person_id,
    CASE WHEN
        is_public(father_id)
            AND (SELECT get_surety_int(person_id, father_id) = 3)
        THEN father_id
        ELSE 0
    END AS father_id,
    CASE WHEN
        is_public(mother_id)
            AND (SELECT get_surety_int(person_id, mother_id) = 3)
        THEN mother_id
        ELSE 0
    END AS mother_id,
    last_edit,
    pb_date,
    pd_date,
    s
FROM tmg_persons
WHERE is_public(person_id);

CREATE OR REPLACE VIEW principals AS
-- find principals
-- in case of mixed-gender events (eg marriages), male is listed first
SELECT
    participants.person_fk AS person,
    events.event_id AS event,
    events.place_fk AS place,
    events.event_date AS event_date,
    events.sort_date AS sort_date,
    events.tag_fk AS tag_type
FROM
    events, participants
WHERE
    events.event_id = participants.event_fk
    AND get_tag_type(events.event_id) < 3
ORDER BY get_gender(participants.person_fk);

CREATE OR REPLACE VIEW person_events AS
SELECT
    participants.person_fk AS person,
    tags.tag_label AS event_name,
    events.event_date AS event_date,
    get_place_name(events.place_fk) AS event_place,
    link_expand(cite_inline(events.event_note)) AS event_note,
    events.event_id AS event_number,
    events.tag_fk AS event_type_number,
    events.sort_date AS sort_date
FROM participants, tags, events
WHERE tags.tag_id = events.tag_fk
AND events.event_id = participants.event_fk
-- AND participants.is_principal IS TRUE
ORDER BY sort_date;

CREATE OR REPLACE VIEW person_event_groups AS
SELECT
    p.person_fk AS person,
    e.sort_date AS sort_date,
    e.event_date AS event_date,
    e.event_id AS event_key,
    e.place_fk AS place_key,
    e.tag_fk AS tag_key,
    t.tag_group_fk AS group_key
FROM
    participants p, tags t, events e
WHERE
    t.tag_id = e.tag_fk
AND
    e.event_id = p.event_fk
ORDER BY
    e.sort_date;

CREATE OR REPLACE VIEW event_notes AS
SELECT
    event_citations.event_fk AS note_id,
    sources.source_id AS source_id,
    get_source_text(sources.source_id) AS source_text
FROM sources, event_citations
WHERE sources.source_id = event_citations.source_fk
ORDER BY source_date;

CREATE OR REPLACE VIEW relation_notes AS
SELECT
    relation_citations.relation_fk AS note_id,
    sources.source_id AS source_id,
    get_source_text(sources.source_id) AS source_text
FROM sources, relation_citations
WHERE sources.source_id = relation_citations.source_fk
ORDER BY source_date;

CREATE OR REPLACE VIEW tmg_events AS
SELECT
    event_id,
    tag_fk,
    get_principal(event_id) AS person1_fk,
    COALESCE (get_principal(event_id,get_principal(event_id)), 0) AS person2_fk,
    place_fk,
    event_date,
    sort_date,
    event_note,
    get_tag_type(event_id) AS tag_type
FROM events;

CREATE OR REPLACE VIEW source_persons AS
SELECT
    source_fk AS src,
    event,
    person,
    get_person_name(person) AS name
FROM principals, event_citations
WHERE event=event_fk;

CREATE OR REPLACE VIEW birth_sources AS
SELECT
    person, event, tag_type, source_fk, event_fk
FROM
    principals, event_citations
WHERE
    event=event_fk
AND
    tag_type=2;

CREATE OR REPLACE VIEW name_and_dates AS
SELECT
    person_id AS person,
    get_person_name(person_id) AS name,
    get_pbdate(person_id) AS born,
    get_pddate(person_id) AS died,
    is_public(person_id) AS is_public
FROM persons;

CREATE OR REPLACE VIEW marriages AS
SELECT
    p.person_fk AS person,
    e.event_id AS event,
    e.event_date,
    e.sort_date,
    get_place_name(e.place_fk) AS place_name,
    get_principal(e.event_id, p.person_fk) AS spouse,
    get_person_name(get_principal(e.event_id, p.person_fk)) AS spouse_name
FROM
    events e,
    participants p,
    tags t
WHERE
    p.event_fk = e.event_id
AND
    e.tag_fk = t.tag_id
AND
    t.tag_group_fk = 2
ORDER BY
    sort_date;

CREATE OR REPLACE VIEW missing_birth AS
SELECT person_id FROM persons WHERE f_year(get_pbdate(person_id)) = 0
AND is_merged(person_id) IS FALSE;

CREATE OR REPLACE VIEW multiple_births AS
SELECT
    participants.person_fk AS person,
    COUNT(participants.person_fk) AS birth_count
FROM
    events, participants
WHERE
    events.event_id = participants.event_fk
    AND events.tag_fk IN (2,1035)
GROUP BY participants.person_fk
    HAVING COUNT(participants.person_fk) > 1
ORDER BY participants.person_fk ASC;

CREATE OR REPLACE VIEW multiple_deaths AS
SELECT
    participants.person_fk AS person,
    COUNT(participants.person_fk) AS death_count
FROM
    events, participants
WHERE
    events.event_id = participants.event_fk
    AND events.tag_fk = 3
GROUP BY participants.person_fk
    HAVING COUNT(participants.person_fk) > 1
ORDER BY participants.person_fk ASC;

CREATE OR REPLACE VIEW source_events AS
SELECT
    events.event_id AS event_id,
    get_tag_type(events.event_id) AS tag_type,
    event_citations.source_fk AS source_id,
    get_tag_name(events.tag_fk) AS event_name,
    events.event_date AS event_date,
    events.sort_date AS sort_date,
    events.event_note AS note,
    get_place_name(events.place_fk) AS event_place,
    get_principal(events.event_id) AS p1,
    get_principal(events.event_id, get_principal(events.event_id)) AS p2
FROM
    events, event_citations
WHERE
    event_citations.event_fk = events.event_id
ORDER BY p1;

CREATE OR REPLACE VIEW place_events AS
SELECT
    event_id,
    get_tag_name(tag_fk) AS event_name,
    event_date,
    place_fk,
    get_principal(event_id) AS p1,
    get_principal(event_id, get_principal(event_id)) AS p2
FROM
    events
ORDER BY sort_date;

CREATE OR REPLACE VIEW tag_events AS
SELECT
    event_id,
    tag_fk,
    get_tag_name(tag_fk) AS event_name,
    event_date,
    place_fk,
    get_place_name(place_fk) AS place_name,
    get_principal(event_id) AS p1,
    get_principal(event_id, get_principal(event_id)) AS p2
FROM
    events
ORDER BY sort_date;

CREATE OR REPLACE VIEW couples AS
-- handy way of finding couples, like in
-- select * from couples where p1n like 'Jack%' and p2n like 'Jill%';
SELECT
    sort_date,
    person AS p1,
    get_person_name(person) AS p1n,
    spouse AS p2,
    spouse_name AS p2n
FROM
    marriages;

CREATE OR REPLACE VIEW pm_view AS -- used by place_manager.php
SELECT
    place_id,
    get_place_name(place_id) AS place_name,
    place_count(place_id) AS place_count
FROM
    places
WHERE
    place_id <> 1
ORDER BY
    place_name;

CREATE VIEW source_types_by_count AS
SELECT
    description AS kildetype,
    part_type_count(part_type_id) AS antall
FROM
    source_part_types
ORDER BY
    antall DESC;

CREATE VIEW event_types_by_count AS
SELECT
    tag_label AS hendelse,
    tag_count(tag_id) AS antall
FROM
    tags
ORDER BY
    antall DESC;
