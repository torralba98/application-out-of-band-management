CREATE DATABASE db;

USE db;
 
CREATE TABLE device_group
 ( Id INT(6),
 group_name VARCHAR(8) DEFAULT NULL UNIQUE,
 CONSTRAINT device_group_id_cp PRIMARY KEY (Id)
);

CREATE TABLE user_group
 ( Id INT(6),
 group_name VARCHAR(8) DEFAULT NULL UNIQUE,
 device_group_id_assigned INT(6) DEFAULT NULL UNIQUE,
 CONSTRAINT user_group_id_cp PRIMARY KEY (Id),
 CONSTRAINT user_groups_assignment_cf FOREIGN KEY (device_group_id_assigned) REFERENCES device_group(Id)
);

CREATE TABLE user
 ( Id INT(6) AUTO_INCREMENT,
 username VARCHAR(64) NOT NULL UNIQUE,
 email VARCHAR(32) NOT NULL UNIQUE,
 password VARCHAR(128) NOT NULL,
 user_group_id INT(6) DEFAULT NULL,
 reset_link_token VARCHAR(64),
 exp_date VARCHAR(24),
 verified VARCHAR(4) DEFAULT 'NO',
 verify_token VARCHAR(64),
 is_admin INT(1) DEFAULT 0,
 CONSTRAINT users_id_cp PRIMARY KEY (Id),
 CONSTRAINT user_groups_cf FOREIGN KEY (user_group_id) REFERENCES user_group(Id)
 );

CREATE TABLE device
 ( Id INT(6) AUTO_INCREMENT,
 in_use VARCHAR(4) DEFAULT 'NO',
 server_status VARCHAR(4) DEFAULT 'OFF',
 device_group_id INT(6) DEFAULT NULL,
 token VARCHAR(64),
 used_by VARCHAR(32),
 last_command_time VARCHAR(32) DEFAULT NULL,
 server_up_time VARCHAR(32) DEFAULT NULL,
 CONSTRAINT dev_cod_cp PRIMARY KEY (Id),
 CONSTRAINT device_groups_cf FOREIGN KEY (device_group_id) REFERENCES device_group(Id)
);
 
INSERT INTO user (username,email,password,verified,is_admin) VALUES ('admin','emailprueba@prueba.es',md5('prueba7'),'YES',1);
