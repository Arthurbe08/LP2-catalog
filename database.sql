
CREATE TABLE users(
	id_user bigint PRIMARY KEY AUTO_INCREMENT,

	password varchar(255) not null,
	username varchar(255)

);

CREATE TABLE maps(
	id_map bigint PRIMARY KEY AUTO_INCREMENT,
    id_user bigint,

	name varchar(255),
	description varchar(255),
	rating float,
	author varchar(255),
	duration bigint,

    FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE CASCADE

);

CREATE TABLE challenges(
    id_challenge bigint PRIMARY KEY AUTO_INCREMENT,
    id_map bigint,

	difficulty float,
	name varchar(255),
	completion boolean,
	deaths int,
	description varchar(1000),
	main_challenge boolean,

    FOREIGN KEY (id_map) REFERENCES maps(id_map) ON DELETE CASCADE
);

CREATE TABLE collectables(
	id_collectable bigint PRIMARY KEY AUTO_INCREMENT,
    id_map bigint,

	current_amount INT,
	max_amount INT,
	image varchar(500),
	name varchar(255),

	FOREIGN KEY (id_map) REFERENCES maps(id_map) ON DELETE CASCADE
);




