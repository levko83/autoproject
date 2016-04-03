<?php

$map  = array(
	/* mail links */
	array('url'=> '/','action' => 'index','controller' => 'index',),
	array('url'=> '/error404','action' => 'error404','controller' => 'index',),
	array('url'=> '/page/:code','action' => 'index','controller' => 'page',),
	array('url'=> '/sitemap','action' => 'sitemap','controller' => 'index',),
	
	/* custom */
	array('url'=> '/faq/:code','action' => 'index','controller' => 'faq',),
	array('url'=> '/faq/:code/page/:page','action' => 'index','controller' => 'faq',),
	
	
	/* tecdoc */
	array('url'=> '/auto/:MFA_ID/:MOD_ID/:TYP_ID/:STR_ID','action' => 'index','controller' => 'search',),
	array('url'=> '/auto/:MFA_ID/:MOD_ID/:TYP_ID','action' => 'index','controller' => 'search',),
	array('url'=> '/auto/:MFA_ID/:MOD_ID','action' => 'index','controller' => 'search',),
	array('url'=> '/auto/:MFA_ID','action' => 'index','controller' => 'search',),
	array('url'=> '/search/number','action' => 'artlookup','controller' => 'search',),
	
);

Register::add('map', $map);