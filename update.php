alter table room change category_id room_category_id integer;
alter table room change acc_id account_id integer;
alter table room change comment comment text;
alter table room change occupancy occupancy enum('Occupied', 'Vacant');
alter table room change status status enum('Clean', 'Dirty');