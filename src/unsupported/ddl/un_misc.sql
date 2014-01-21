/*
un_misc.sql -- remove cruft
leifbk 2008-05-06

If you have installed an earlier version of Exodus with the deprecated
"views_and_functions.sql" script, you may want to clean up your
database by executing the code below. Most of the views and functions
below are retained in the misc.sql file.
*/

drop function insp(int,int);
drop function msrc(int,int);
drop function get_page_num(int,int);
drop function source_count(int);
drop function cit_count(int);
drop function f_type(text);
drop function age_diff(text,text);
drop function get_free_source_number(int);
drop function pc_count(int);
drop view unlinked;
drop function ftup(int,int);
drop view unlinked_count;
drop function spouse_count(int);
drop function page_extract(int);
drop function is_cleaned(int);
