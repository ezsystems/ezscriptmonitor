CREATE TABLE ezscheduled_script (
  ID                    SERIAL   NOT NULL,
  process_id            INT   DEFAULT '0'   NOT NULL,
  NAME                  VARCHAR(50)   DEFAULT ''   NOT NULL,
  command               VARCHAR(255)   NOT NULL,
  last_report_timestamp INT   DEFAULT '0'   NOT NULL,
  progress              SMALLINT   DEFAULT '0'   NOT NULL,
  user_id               INT   DEFAULT '0'   NOT NULL,
  CONSTRAINT pk_ezscheduled_script PRIMARY KEY ( id ));

CREATE INDEX ezscheduled_script_timestamp ON ezscheduled_script (
      last_report_timestamp);