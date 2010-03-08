CREATE SEQUENCE ezscheduled_script_s
    START 1
    INCREMENT 1
    MAXVALUE 9223372036854775807
    MINVALUE 1
    CACHE 1;

CREATE TABLE ezscheduled_script (
  id                    integer DEFAULT nextval('ezscheduled_script_s'::text) NOT NULL,
  process_id            INT   DEFAULT '0'   NOT NULL,
  NAME                  VARCHAR(50)   DEFAULT ''   NOT NULL,
  command               VARCHAR(255)   NOT NULL,
  last_report_timestamp INT   DEFAULT '0'   NOT NULL,
  progress              SMALLINT   DEFAULT '0'   NOT NULL,
  user_id               INT   DEFAULT '0'   NOT NULL,
  CONSTRAINT pk_ezscheduled_script PRIMARY KEY ( id ));

CREATE INDEX ezscheduled_script_timestamp ON ezscheduled_script (
      last_report_timestamp);

SELECT setval('ezscheduled_script_s', max(id)) , 'ezscheduled_script' as tablename FROM ezscheduled_script;