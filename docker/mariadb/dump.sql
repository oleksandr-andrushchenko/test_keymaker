-- create database if not exists `test.keymaker`;

drop table if exists `url_id_provider`;

create table `url_id_provider` (
  `id` int unsigned not null auto_increment,
  `url` varchar(2048) not null,
  primary key (`id`)
) engine=innodb charset=utf8;

drop table if exists `url_md5_provider`;

create table `url_md5_provider` (
  `md5` varchar(32) not null,
  `url` varchar(2048) not null,
  primary key (`md5`)
) engine=innodb charset=utf8;
