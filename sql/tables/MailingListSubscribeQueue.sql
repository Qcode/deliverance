create table MailingListSubscribeQueue (
	id serial,
	email varchar(255) not null,
	info text,
	send_welcome boolean not null default false,

	instance integer references Instance(id) on delete cascade,

	primary key (id)
);

create index MailingListSubscribeQueue_email_index on MailingListSubscribeQueue(email);
