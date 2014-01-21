CREATE TABLE linkage_roles (
    role_id     INTEGER PRIMARY KEY,
    role_en     TEXT,
    role_no     TEXT
);

INSERT INTO linkage_roles (role_id, role_en, role_no) VALUES (0, 'undefined', 'udefinert');
INSERT INTO linkage_roles (role_id, role_en, role_no) VALUES (1, 'child', 'barn');
INSERT INTO linkage_roles (role_id, role_en, role_no) VALUES (2, 'father', 'far');
INSERT INTO linkage_roles (role_id, role_en, role_no) VALUES (3, 'mother', 'mor');
INSERT INTO linkage_roles (role_id, role_en, role_no) VALUES (4, 'godparent', 'fadder');
INSERT INTO linkage_roles (role_id, role_en, role_no) VALUES (99, 'other', 'andre');

CREATE TABLE sureties (
    surety_id   INTEGER PRIMARY KEY,
    surety_en   TEXT,
    surety_no   TEXT
);

INSERT INTO sureties (surety_id, surety_en, surety_no) VALUES (3, 'certain', 'sikker');
INSERT INTO sureties (surety_id, surety_en, surety_no) VALUES (2, 'probable', 'trolig');
INSERT INTO sureties (surety_id, surety_en, surety_no) VALUES (1, 'possible', 'mulig');
INSERT INTO sureties (surety_id, surety_en, surety_no) VALUES (0, 'unknown', 'ukjent');
INSERT INTO sureties (surety_id, surety_en, surety_no) VALUES (-1, 'wrong', 'feil');

CREATE TABLE source_linkage (
    source_fk   INTEGER NOT NULL REFERENCES sources (source_id),
    per_id      INTEGER NOT NULL, -- running id of name in source
    role_fk     INTEGER REFERENCES linkage_roles (role_id),
    person_fk   INTEGER REFERENCES persons (person_id),
    surety_fk   INTEGER REFERENCES sureties (surety_id),
    s_name      TEXT, -- person name (and contextual info) as mentioned in source
    sl_note     TEXT, -- notes and inferences
    PRIMARY KEY (source_fk, per_id)
);
