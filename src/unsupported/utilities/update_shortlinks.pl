#! /usr/bin/perl

# update_shortlinks.pl
# finds notes and sources with shortlinks to merged persons
# and replaces old_person with new_person
# (C) leifbk 2008-2011

use strict;
use DBI;

my $database = "DBI:Pg:dbname=pgslekt";
my $dbh = DBI->connect("$database") or die $DBI::errstr;

my $get_merged = $dbh->prepare("SELECT old_person_fk, new_person_fk FROM merged ORDER BY new_person_fk ASC");
my $get_source = $dbh->prepare("SELECT source_id, source_text FROM sources WHERE source_text SIMILAR TO ?");
my $put_source = $dbh->prepare("UPDATE sources SET source_text = ? WHERE source_id = ?");
my $get_event = $dbh->prepare("SELECT event_id, event_note FROM events WHERE event_note SIMILAR TO ?");
my $put_event = $dbh->prepare("UPDATE events SET event_note = ? WHERE event_id = ?");

$get_merged->execute();
print "Updating links to merged persons\n";
while (my ($old_person, $new_person) = $get_merged->fetchrow_array()) {
    my $regex = "%\\[p=" . $old_person . "[\\|\\]]%";
    $get_source->execute($regex);
    while (my ($source_id, $source_text) = $get_source->fetchrow_array()) {
        print "Source $source_id, $source_text\n($old_person -> $new_person)\n";
        $source_text =~ s/(\[p=)$old_person([\|\]])/$1$new_person$2/g;
        $put_source->execute($source_text, $source_id);
    }
    $get_event->execute($regex);
    while (my ($event_id, $event_text) = $get_event->fetchrow_array()) {
        print "Event $event_id: $event_text\n($old_person -> $new_person)\n";
        $event_text =~ s/(\[p=)$old_person([\|\]])/$1$new_person$2/g;
        $put_event->execute($event_text, $event_id);
    }
}
$get_merged->finish;


# my $get_dc = $dbh->prepare("SELECT person_fk FROM dead_children ORDER BY person_fk ASC");
# my $get_name = $dbh->prepare("SELECT get_person_name(?)");
# $get_dc->execute();
# print "Removing links to dead children\n";
# while (my ($person) = $get_dc->fetchrow_array()) {
#     $get_name->execute($person);
#     my $name = $get_name->fetchrow();
#     $name =~ s/ &#x2020;//g; # strip dagger symbol
#     my $regex = "%\\[p=" . $person . "[\\|\\]]%";
#     $get_source->execute($regex);
#     while (my ($source_id, $source_text) = $get_source->fetchrow_array()) {
#         print "Source $source_id, $source_text\n($person $name)\n";
#         $source_text =~ s/\[p=$person\|(.+?)\]/$1/g;
#         $source_text =~ s/\[p=$person\]/$name/g;
#         $put_source->execute($source_text, $source_id);
#     }
#     $get_event->execute($regex);
#     while (my ($event_id, $event_text) = $get_event->fetchrow_array()) {
#         print "Event $event_id: $event_text\n($person $name)\n";
#         $event_text =~ s/\[p=$person\|(.+?)\]/$1/g;
#         $event_text =~ s/\[p=$person\]/$name/g;
#         $put_event->execute($event_text, $event_id);
#     }
# }
# $get_name->finish;
# $get_dc->finish;

$dbh->disconnect;
