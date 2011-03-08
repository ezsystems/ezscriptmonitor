CREATE SEQUENCE s_scheduled_script;

CREATE TABLE ezscheduled_script (
  command VARCHAR2(255) NOT NULL,
  id INTEGER NOT NULL,
  last_report_timestamp INTEGER DEFAULT 0 NOT NULL,
  name VARCHAR2(50) NOT NULL,
  process_id INTEGER DEFAULT 0 NOT NULL,
  progress INTEGER DEFAULT 0,
  user_id INTEGER DEFAULT 0 NOT NULL,
  PRIMARY KEY ( id )
);

CREATE OR REPLACE TRIGGER ezscheduled_script_id_tr
BEFORE INSERT ON ezscheduled_script FOR EACH ROW WHEN (new.id IS NULL)
BEGIN
  SELECT s_scheduled_script.nextval INTO :new.id FROM dual;
END;
/

CREATE INDEX ezscheduled_script_timestamp ON ezscheduled_script ( last_report_timestamp );


