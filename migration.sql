 CREATE TABLE IF NOT EXISTS totp_profiles (
     id int primary key auto_increment,
     name varchar(255) not null,
     secret varchar(255) not null,
     hash_algo varchar(255) default 'sha1',
     period int DEFAULT 30
);