<?php
/*
Plugin Name: Link Counter
Plugin URI: http://www.henrywoodbury.com/link-counter/
Description: Counts links in posts; organizes list by domain if desired
Version: 1.1
Author: Henry Woodbury
Author URI: http://www.henrywoodbury.com/

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

*/

// Return lower case string
function mklc_strtolower($str = '') {
	if( function_exists( mb_strtolower )) {
		return mb_strtolower($str);
	}
	else {
		return strtolower($str);
	}
}

// Add http:// to links
function mklc_addHTTP($link = '') {
	if (substr($link, 0, 7) != 'http://' && substr($link, 0, 8) != 'https://') return 'http://' . $link;
	else return $link;
}

// Sort link arrays
function mklc_sortLinks($k, $sort, $order) {
	if ($sort == 'count') 
		if ($order == 'ascending') asort($k);
		else arsort($k);
	elseif ($sort == 'alpha')
		if ($order == 'descending') krsort($k);
		else ksort($k);
	return $k;
}


// Count links. Defaults are:
// dupes 	- 'yes' = All matches including duplicates, 'no' = Ignore duplicates in individual posts
// output - 'all' = All domains and links, 'domain' = domains only, 'link' = links only
// target 	- 'all' = All URLs, 
//				- 'other' = Exclude blog URL 
//				- (NEED TO DO: a single domain, or domain with * and ? wildcards)
// active	- 'yes' = Only active links (those attached to an href), 'no' = all URLs 
// sort 		- 'count' = Sourt by count, 'alpha' = Sort alphabetically, 'none' = no sort (will follow order of first occurence);
// order 	- 'ascending' or 'descending'
// scope 	- 'all' = posts and pages, 'post' = posts only, 'page' = pages only
// NEED TO DO: Date Range
function mklc_cc($atts) {
	extract(shortcode_atts(array(
		'dupes' => 'no',
		'output' => 'all',
		'target' => 'other',
		'active' => 'yes',
		'sort' => 'alpha',
		'order' => 'ascending',
		'scope' => 'post'
	), $atts));

	global $wpdb; 

// 	Grab all posts to present. (NEED TO DO: Date Range)
	$now = gmdate("Y-m-d H:i:s",time()); 

//  Define some regular expressions for matching links and domains.
//  See John Gruber's 'Liberal, Accurate Regex Pattern for Matching URLs' 
//	  http://daringfireball.net/2009/11/liberal_regex_for_matching_urls
// Capture all web links
	$patternURL = "\b((?:https?://|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:'\".,<>?«»“”‘’]))";
// Capture "active" links (those associated to a valid href
	$patternAURL = "\bhref=(?:\"?|'?)((?:https?://|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:'\".,<>?«»“”‘’]))";
// Pattern to extract subdomain from link	
	$patternSUB = "\b(([\w-]+://?|www\d{0,3}[.])([^\s()<>/]+))";
// Pattern to remove www from subdomain
	$patternWWW = "\bwww\d{0,3}[.]";
	
//	Retrieve URL of blog 
	$url = mklc_strtolower(get_bloginfo('url'));
	preg_match("@$patternSUB@", $url, $match);	
	$blogURL = $match[3];
	
	switch ($scope) {
		case 'post':
			$postScope = "AND post_type = 'post'";
			break;
		case 'page':
			$postScope = "AND post_type = 'page'";
			break;
		default:
			$postScope = "";
			break;
	}

// 	Retrieve post content ORDER BY post_date DESC"
	$postcontents = $wpdb->get_results("SELECT post_content FROM $wpdb->posts WHERE post_status = 'publish' AND post_password = '' AND post_date < '$now' $postScope");

//	Define arrays to store link, domain, post ID
	$linkList = array();
	$activeLinkList = array();
	$domainList = array();
	$idList = array();
	$linksByDomain = array();
	$domainListLink = array();
	$domainListWWW = array();

// 	Build the list of links by post - remove duplicates within the same post
	if ($postcontents) {
		foreach ($postcontents as $post) {
// 	Save post id to cross-reference content and other post data later
			$id = $post->ID;
//	Grab content to analyze
			$taggedContent = $post->post_content;
			$taggedContent = mklc_strtolower($taggedContent);
//	Match links
			if ($active == 'yes') {
// Match active links
				preg_match_all("@$patternAURL@", $taggedContent, $matches, PREG_PATTERN_ORDER);
			} else {
// Match all links
				preg_match_all("@$patternURL@", $taggedContent, $matches, PREG_PATTERN_ORDER);
			}
// Remove duplicates in same post
			if ($dupes == 'no') {
				$links = array_unique($matches[1]);
			} else {
				$links = $matches[1];
			}
//	Capture subdomain of each link and add to $linkData array
			foreach ($links as $link) {
				preg_match("@$patternSUB@", $link, $match);
//	Don't capture self
				if ($target == 'other' && ($match[3] == $blogURL)) break;
//	Add link to list of links
				$linkList[] = mklc_addHTTP($link);
//	Add post id to list of ids
				$idList[] = $id;
// Remove 'www' from domain name, but retain it for links	
				$domain = preg_replace("@$patternWWW@", '', $match[3]);
//	Add domain to list of domains
				$domainList[] = $domain;
//	Flag 'www' if used
				$domainListWWW[$domain] = '';
				if ($domain != $match[3]) $domainListWWW[$domain] = 'www.';
//	Add domain to array of domain links
				$domainListLink[$domain] =  mklc_addHTTP($domainListWWW[$domain] . $domain);
// Add link to array of links for each domain
				$linksByDomain[$domain][] = mklc_addHTTP($link);
			}
    	}
	}
	
// Count Domains
	$d = array_count_values($domainList);
// Count Links
	$a = array_count_values($linkList);

// 	Sort the Domains
	$d = mklc_sortLinks($d, $sort, $order);
// 	Sort the Links
	$a = mklc_sortLinks($a, $sort, $order);

// Clean up linksByDomain
	foreach ($linksByDomain as $domain => $links) {
		$b = array_count_values($links);
// 	Sort the Links
		$b = mklc_sortLinks($b, $sort, $order);
		$linksByDomain[$domain] = $b;
	}

// 	Print the data.
	$mklc = '<div class="mklc">';

	if ($output != 'link') {
		$mklc .= '<ul class="mklc-list-domains">';
		foreach ($d as $domain => $count) {
			$mklc .= '<li>' . $count . ' - <a href="' . $domainListLink[$domain] . '">' . $domain . '</a></li>'; 
			if ($output != 'domain') {
				$mklc .= '<ul class="mklc-list-links">';
				foreach ($linksByDomain[$domain] as $link => $count) {
					$mklc .= "<li>" . $count . ' - <a href="' . $link . '">' . $link . '</a></li>';
				}
				$mklc .= '</ul>';
			}
		}
		$mklc .= '</ul>';
	} else {
		$mklc .= '<ul class="mklc-list-links">';
		foreach ($a as $link => $count) {
			$mklc .= '<li>' . $count . ' - <a href="' . $link . '">' . $link . '</a></li>'; 
		}
		$mklc .= '</ul>';
	}

	$mklc .= '</ul>';
	$mklc .= '</div>';
	return $mklc;
}

/* Call the form from a post */
add_shortcode('mklc', 'mklc_cc');

/* Or call the form directly */
function mklc($atts) {
	echo mklc_cc($atts);
}

?>