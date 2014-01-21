#! /usr/bin/perl

# add_source.pl - add source nodes from standard input
# example: cat my.txt | add_source.pl 46528
# this script utilizes the two-param add_source() function,
# which infers sort order and source date from the text.
# part of the Exodus/Yggdrasil project, leifbk 2005-2011

# I have put this bash script in /usr/local/bin, called "add":

#! /bin/bash
# /home/leif/scripts/add_source $1 < /tmp/temp.txt > /dev/null

# and just write "add 86539"

use strict;
use DBI;

my $parent = shift;
if ((!$parent) || !($parent =~ /^\d+$/)) {
    print "Bad or missing parameter $parent\n";
    exit;
}
my $dbh = DBI->connect("dbi:Pg:dbname=pgslekt", '', '',
                {AutoCommit => 1}) or die $DBI::errstr;
my $sth = $dbh->prepare("SELECT add_source(?, ?)");
while (my $text = <STDIN>) {
    chomp($text);
    $sth->execute($parent, $text);
    my $node = $sth->fetch()->[0];
    if ($node < 0) {
        $node = abs($node);
        print "Duplicate of node $node, not added.\n";
    }
    else {
        print "Node $node added.\n";
    }
}
$sth->finish;
$dbh->disconnect;
