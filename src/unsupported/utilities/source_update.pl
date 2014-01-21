#! /usr/bin/perl
# update sources from standard input
# NOTE that sort_order must be unique.
# part of the Exodus/Yggdrasil project, leifbk 2005-2011

use strict;
use DBI;

my $parent = shift;
if (!$parent) {
    print "Bad or missing parameter.\n";
    exit;
}
my $sort = 1;
my $count = 1;
my $text = '';
my $database = "DBI:Pg:dbname=pgslekt";
my $dbh = DBI->connect("$database") or die $DBI::errstr;
my $sth = $dbh->prepare("
    UPDATE sources SET source_text = ?
        WHERE parent_id = ?
        AND sort_order = ?
");
while (my $line = <STDIN>) {
    if ($line =~ /^#(\d+) (.+)/o) {
        $sort = $1;
        $text = $2;
        $sth->execute($text, $parent, $sort);
    }
}
$dbh->disconnect;
