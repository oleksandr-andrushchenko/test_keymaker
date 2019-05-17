-- create database if not exists `test.keymaker`;

drop table if exists `url`;

create table `url` (
  `short` varchar(32) not null,
  `long` varchar(2048) not null,
  primary key (`short`)
) engine=myisam charset=utf8;
