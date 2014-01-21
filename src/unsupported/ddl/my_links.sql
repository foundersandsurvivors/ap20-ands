INSERT INTO my_links (link_type, short_link, long_link, description) VALUES
('rg', E'\\[rg=(.+?)\\|(.+?)\\|(.+?)\\]',
E'<a href="//www.arkivverket.no/URN:rg_read/\\1/\\2" title="Lenke til bilde av tingbokside">\\3</a>',
'Scanned court protocols [sk=protocol|image id|link text]');
