
-- triggers.sql - leifbk 2005-2007

CREATE OR REPLACE FUNCTION update_last_edit() RETURNS TRIGGER AS $$
BEGIN
    UPDATE persons SET last_edit=NOW() WHERE person_id=NEW.person_fk;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER update_last_edit
    BEFORE INSERT OR UPDATE ON participants
        FOR EACH ROW EXECUTE PROCEDURE update_last_edit();

-- if you're going to do mass updates, you'll probably want to
-- disable this trigger temporarily, and re-enable it afterwards:

-- ALTER TABLE participants DISABLE TRIGGER update_last_edit;
-- ALTER TABLE participants ENABLE TRIGGER update_last_edit;

CREATE OR REPLACE FUNCTION update_last_edit_relations() RETURNS TRIGGER AS $$
BEGIN
    UPDATE persons SET last_edit=NOW() WHERE person_id=NEW.child_fk OR person_id=NEW.parent_fk;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER update_last_edit_relations
    BEFORE INSERT OR UPDATE ON relations
        FOR EACH ROW EXECUTE PROCEDURE update_last_edit_relations();
