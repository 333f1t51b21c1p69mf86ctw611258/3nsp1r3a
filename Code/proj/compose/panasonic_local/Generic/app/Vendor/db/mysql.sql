load data local infile 'directorydump.csv' into table users fields terminated by ',' enclosed by '"' lines terminated by '\n' (DN, username, department, mail, manager, name, title, password);

