--
-- PostgreSQL database dump
--

SET statement_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SET check_function_bodies = false;
SET client_min_messages = warning;

SET search_path = public, pg_catalog;

--
-- Data for Name: tags; Type: TABLE DATA; Schema: public; Owner: ubuntu
--

INSERT INTO tags VALUES (1, 8, 1, 'Adopted', 'ADOP ', 'Adoptert');
INSERT INTO tags VALUES (2, 1, 1, 'Born', 'BIRT ', 'Født');
INSERT INTO tags VALUES (3, 3, 1, 'Died', 'DEAT ', 'Død');
INSERT INTO tags VALUES (4, 2, 2, 'Married', 'MARR ', 'Gift');
INSERT INTO tags VALUES (5, 5, 2, 'Divorced', 'DIV  ', 'Skilt');
INSERT INTO tags VALUES (6, 4, 1, 'Buried', 'BURI ', 'Gravlagt');
INSERT INTO tags VALUES (10, 8, 3, 'Residence', 'RESI ', 'Bosted');
INSERT INTO tags VALUES (12, 1, 1, 'Baptized', 'BAPM ', 'Døpt');
INSERT INTO tags VALUES (19, 8, 3, 'Census', 'CENS ', 'Folketelling');
INSERT INTO tags VALUES (23, 2, 2, 'Engaged', 'ENGA ', 'Forlovet');
-- note hard-coded reference to 31 in event_insert.php and event_update.php
INSERT INTO tags VALUES (31, 4, 3, 'Probate', 'PROB ', 'Skifte');
INSERT INTO tags VALUES (46, 8, 1, 'Confirmed', 'CONF ', 'Konfirmert');
INSERT INTO tags VALUES (49, 8, 3, 'Emigrated', 'EMIG ', 'Utvandret');
INSERT INTO tags VALUES (62, 1, 1, 'Stillborn', 'STIL ', 'Dødfødt');
INSERT INTO tags VALUES (66, 8, 3, 'Occupation', 'OCCU ', 'Yrke');
INSERT INTO tags VALUES (72, 8, 3, 'Anecdote', 'NOTE ', 'Anekdote');
INSERT INTO tags VALUES (78, 8, 3, 'Note', 'NOTE ', 'Merknad');
INSERT INTO tags VALUES (1000, 2, 2, 'Common-law marriage', 'MARR ', 'Samboende');
INSERT INTO tags VALUES (1003, 8, 3, 'Tenant', 'NOTE ', 'Feste');
INSERT INTO tags VALUES (1005, 8, 3, 'Moved', 'RESI ', 'Flyttet');
INSERT INTO tags VALUES (1006, 8, 2, 'Probably identical', 'NOTE ', 'Kan være identisk');
INSERT INTO tags VALUES (1033, 2, 2, 'Affair', 'EVEN ', 'Forhold');
INSERT INTO tags VALUES (1035, 1, 1, 'Probably born', 'BIRT ', 'Trolig født');
INSERT INTO tags VALUES (1039, 8, 2, 'Confused', 'NOTE ', 'Forvekslet');
-- note hard-coded reference to 1040 in person_merge.php
-- ##### check ##### WAS 1040, 8, 2 BUT Leif old/new was 1040, 8, 1 ??????????
INSERT INTO tags VALUES (1040, 8, 1, 'Identical to', 'NOTE ', 'Identisk med');
INSERT INTO tags VALUES (1041, 8, 1, 'Matricle', 'NOTE ', 'Matrikkel');
-- custom tags for khrd/diggers
INSERT INTO tags VALUES (100, 8, 3, 'Sighting', 'EVEN ', 'Sighting');
INSERT INTO tags VALUES (102, 8, 3, 'Enlisted AIF WWI', 'EVEN ', 'Enlisted AIF');
INSERT INTO tags VALUES (101, 3, 1, 'Cause of death DATA', 'EVEN ', 'Cause of death');
INSERT INTO tags VALUES (8, 9, 2, 'LINK', 'TASK ', 'LINK');
INSERT INTO tags VALUES (9, 9, 1, 'REVIEW', 'TASK ', 'REVIEW');

--
-- PostgreSQL database dump complete
--

