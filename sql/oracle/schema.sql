CREATE TABLE ezscheduled_script (
  id                    INT    NOT NULL,
  process_id            INT    DEFAULT 0    NOT NULL,
  NAME                  VARCHAR2(50)    DEFAULT ''    NOT NULL,
  command               VARCHAR2(255)    NOT NULL,
  last_report_timestamp INT    DEFAULT 0    NOT NULL,
  progress              INT    DEFAULT 0    NOT NULL,
  user_id               INT    DEFAULT 0    NOT NULL,
  CONSTRAINT pk_ezscheduled_script PRIMARY KEY ( id ));

CREATE INDEX ezscheduled_script_timestamp ON ezscheduled_script (last_report_timestamp);

CREATE SEQUENCE s_ezscheduled_script;

CREATE OR REPLACE TRIGGER ezscheduled_script_id_tr
   BEFORE INSERT ON ezscheduled_script FOR EACH ROW WHEN ( NEW.id IS NULL )
   BEGIN
      SELECT seq_ezscheduled_script.nextval
      INTO :new.id
      FROM dual;
   END;
/
