DROP DATABASE devstagram_api;
CREATE DATABASE devstagram_api;
USE devstagram_api;

CREATE TABLE users(
   id int primary key auto_increment,
   name varchar(100) not null,
   email varchar(100) not null,
   password varchar(255) not null,
   avatar varchar(100) not null
);

CREATE TABLE photos(
   id int primary key auto_increment,
   user_id int not null,
   url varchar(100) not null,
   foreign key (user_id) references users(id)
);

CREATE TABLE photos_has_likes(
   id int primary key auto_increment,
   user_id int not null,
   photo_id int not null,
   foreign key (user_id) references users(id),
   foreign key (photo_id) references photos(id)
);

CREATE TABLE photos_has_comments(
   id int primary key auto_increment,
   user_id int not null,
   photo_id int not null,
   created_at datetime not null,
   comment text not null,
   foreign key (user_id) references users(id),
   foreign key (photo_id) references photos(id)
);

CREATE TABLE followers(
   id int primary key auto_increment,
   first_user int,
   second_user int,
   foreign key (first_user) references users(id),
   foreign key (second_user) references users(id)
);
