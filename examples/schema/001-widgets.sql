create table if not exists widgets
(
	id int auto_increment
		primary key,
	user int not null,
	foo text not null,
	bar int null,
	created timestamp default CURRENT_TIMESTAMP not null,
	modified timestamp default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
	constraint files_users_id_fk
		foreign key (user) references users (id)
);

