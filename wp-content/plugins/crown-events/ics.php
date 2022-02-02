<?php
define('WP_USE_THEMES', false);
require_once(dirname(__FILE__).'/../../../wp-load.php');


$postName = isset($_GET['event']) ? $_GET['event'] : '';
$posts = get_posts(array('post_type' => 'event', 'posts_per_page' => 1, 'name' => $postName));
if(empty($posts)) die;
$event = $posts[0];

// $location = array();
// $venue = get_post_meta($event->ID, 'event_venue', true);
// $address = get_post_meta($event->ID, 'event_address', true);
// if(!empty($venue)) $location[] = $venue;
// if(!empty($address)) $location[] = str_replace("\r\n", "\\n", $address);

$startTimestamp = new DateTime(get_post_meta($event->ID, 'event_start_timestamp', true), new DateTimeZone(get_post_meta($event->ID, 'event_timezone', true)));
$endTimestamp = new DateTime(get_post_meta($event->ID, 'event_end_timestamp', true), new DateTimeZone(get_post_meta($event->ID, 'event_timezone', true)));
$startTimestamp->modify(-floatval($startTimestamp->getOffset()/60/60).' hours');
$endTimestamp->modify(-floatval($endTimestamp->getOffset()/60/60).' hours');

// $url = 'http://ics.agical.io/';
// $params = array(
// 	'subject' => $event->post_title,
// 	'description' => str_replace("\r\n", "\\n", wp_strip_all_tags($event->post_content)),
// 	'location' => implode("\\n", $location),
// 	'dtstart' => $startTimestamp->format('Y-m-d\TH:i:s\Z'),
// 	'dtend' => $endTimestamp->format('Y-m-d\TH:i:s\Z'),
// 	'echo' => 1
// );
// wp_redirect($url.'?'.http_build_query($params));
// die;

// print_r($params); die;

$dtFormat = 'Ymd\THis\Z';
$now = time();

$ics = array();
$ics[] = 'BEGIN:VCALENDAR';
$ics[] = 'VERSION:2.0';
$ics[] = 'PRODID:-//www.figureone.com//ics';
$ics[] = 'CALSCALE:GREGORIAN';
$ics[] = 'BEGIN:VEVENT';
$ics[] = 'DTSTAMP:'.date($dtFormat, $now);
$ics[] = 'UID:'.date($dtFormat, $now).'-'.rand().'@mbexec.net';
$ics[] = 'DTSTART:'.$startTimestamp->format($dtFormat);
$ics[] = 'DTEND:'.$endTimestamp->format($dtFormat);
$ics[] = 'ORGANIZER:';
$ics[] = 'SUMMARY:'.$event->post_title;
$ics[] = 'BEGIN:VALARM';
$ics[] = 'TRIGGER;VALUE=DURATION:-PT15M';
$ics[] = 'DESCRIPTION:Alarm: '.$event->post_title;
$ics[] = 'ACTION:DISPLAY';
$ics[] = 'END:VALARM';
// $ics[] = 'DESCRIPTION:'.preg_replace('/([\,;])/','\\\$1', html_entity_decode(str_replace("\r\n", '\n', wp_strip_all_tags($event->post_content))));
$ics[] = 'DESCRIPTION:'.preg_replace('/([\,;])/','\\\$1', html_entity_decode(get_permalink($event->ID)));
// $ics[] = 'LOCATION:'.preg_replace('/([\,;])/','\\\$1', html_entity_decode(implode('\n', $location)));
$ics[] = 'ATTACH:';
$ics[] = 'END:VEVENT';
$ics[] = 'END:VCALENDAR';

header('Content-type: text/calendar; charset=utf-8');
header('Content-Disposition: attachment; filename='.$postName.'.ics');
echo implode("\n", $ics);
die;