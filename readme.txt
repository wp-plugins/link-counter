=== Plugin Name ===
Contributors: henryww
Tags: links, metadata, admin
Requires at least: 2.8.5
Tested up to: 3.3.1
Stable tag: 1.1

Link Counter is a Wordpress Plugin that produces a report of targeted links and domains from a blog's posts and pages.

== Description ==

Link Counter helps track patterns in your blog's outgoing links. The generated report can be organized alphabetically or by count. It can show links only, links by domain, or domains only. Other options help refine results.

Link Counter takes the following parameters (the first argument for each is the default):

* active="yes" or "no" where "yes" means only links in anchor tags are counted; "no" means that unlinked links are also counted.
* dupes="no" or "yes" where "no" means duplicates in individual posts or pages are ignored; "yes" means they are counted.
* output="all" or "domain" or "link" where "all" means report on all links, organized by domain; "domain" means report only on domains; "link" means report only on links.
* target="other" or "all" where "other" means count only links to external sites; "all" means include links to the blog's domain.
* sort="alpha" or "count" or "none" where "alpha" means sort alphabetically; "count" means sort by count; "none" means no sort.
* order="ascending" or "descending" where "ascending" is 0-9 a-z; "descending" is z-a 9-0.
* scope="post" or "page" or "all" where "post" means only posts are analyzed; "page" means only pages are analyzed; "all" means all content is analyzed.

Link Counter has been tested in Wordpress 2.8.5 through 3.3.1, MSIE 7, 8, and 9, Chrome, Firefox, and Safari.

== Installation ==

1.	Upload the link-counter directory to the wp-content/plugins directory of your Wordpress install. 
2.	Activate the plugin through the Plugins menu in Wordpress. 
3.	To add the link counter to a page via a template, use the code:
	<?php mklc(); ?>
	Add parameters to this function call by placing them in an array -- for example:
	<?php  mklc(array('dupes' => 'yes',
		'output' => 'link',
		'active' => 'no',
		'target' => 'all',
		'sort' => 'count',
		'order' => 'descending',
		'scope' => 'all')); ?>
4.	To add the link counter to page using a shortcode, use the syntax:
	[mklc]
	Add parameters to this short code using the syntax: parameter="value" -- for example:
	[mklc sort="alpha" scope="page"]

== Changelog ==

= 1.1 =

* Corrected a bug with the link sort.
* Corrected a bug with the link output. 
* Updated the link capture regular expressions to handle some obscure cases

== Upgrade Notice ==

= 1.1 =
This upgrade corrects two bugs related to link sort and output and adds an "active" option. Use it to ensure that link output is handled correctly and to optionally limit output to "active" links (this is now the default).