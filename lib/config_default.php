<?php
/**
 * Default configuration
 *
 * @package 	TUPA
 * @author			Kasper Skaarhoj <kasperYYYY@typo3.com>
 * @author 			Urs Weiss <urs@tupa-dns.org>
 */

/***************************************************************
*  Copyright notice
*
*  (c) 2005 Urs Weiss (urs@tupa-dns.org)
*  All rights reserved
*
*  This file is part of TUPA.
*
*  TUPA is free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*  A copy is found in the textfile GPL.txt and important notices to the license
*  from the author is found in LICENSE.txt distributed with these scripts.
*
*  TUPA is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/


if (!defined ('PATH_config')) 	die ('The configuration path was not properly defined!');

// Don't change something in this file! Use "config/config_site.inc.php" instead.

$topLevelDomains = 'aaa|aarp|abb|abbott|abogado|ac|academy|accenture|accountant|accountants|aco|active|actor|ad|ads|adult|ae|aeg|aero|af|afl|ag|agency|ai|aig|airforce|airtel|al|alibaba|alipay|allfinanz|alsace|am|amica|amsterdam|analytics|android|ao|apartments|app|apple|aq|aquarelle|ar|aramco|archi|army|arpa|arte|as|asia|associates|at|attorney|au|auction|audi|audio|author|auto|autos|aw|ax|axa|az|azure|ba|baidu|band|bank|bar|barcelona|barclaycard|barclays|bargains|bauhaus|bayern|bb|bbc|bbva|bcn|bd|be|beats|beer|bentley|berlin|best|bet|bf|bg|bh|bharti|bi|bible|bid|bike|bing|bingo|bio|biz|bj|black|blackfriday|bloomberg|blue|bm|bms|bmw|bn|bnl|bnpparibas|bo|boats|boehringer|bom|bond|boo|book|boots|bosch|bostik|bot|boutique|br|bradesco|bridgestone|broadway|broker|brother|brussels|bs|bt|budapest|bugatti|build|builders|business|buy|buzz|bv|bw|by|bz|bzh|ca|cab|cafe|cal|call|camera|camp|cancerresearch|canon|capetown|capital|car|caravan|cards|care|career|careers|cars|cartier|casa|cash|casino|cat|catering|cba|cbn|cc|cd|ceb|center|ceo|cern|cf|cfa|cfd|cg|ch|chanel|channel|chat|cheap|chloe|christmas|chrome|church|ci|cipriani|circle|cisco|citic|city|cityeats|ck|cl|claims|cleaning|click|clinic|clinique|clothing|cloud|club|clubmed|cm|cn|co|coach|codes|coffee|college|cologne|com|commbank|community|company|compare|computer|comsec|condos|construction|consulting|contact|contractors|cooking|cool|coop|corsica|country|coupons|courses|cr|credit|creditcard|creditunion|cricket|crown|crs|cruises|csc|cu|cuisinella|cv|cw|cx|cy|cymru|cyou|cz|dabur|dad|dance|date|dating|datsun|day|dclk|de|dealer|deals|degree|delivery|dell|delta|democrat|dental|dentist|desi|design|dev|diamonds|diet|digital|direct|directory|discount|dj|dk|dm|dnp|do|docs|dog|doha|domains|doosan|download|drive|dubai|durban|dvag|dz|earth|eat|ec|edeka|edu|education|ee|eg|email|emerck|energy|engineer|engineering|enterprises|epson|equipment|er|erni|es|esq|estate|et|eu|eurovision|eus|events|everbank|exchange|expert|exposed|express|fage|fail|fairwinds|faith|family|fan|fans|farm|fashion|fast|feedback|ferrero|fi|film|final|finance|financial|firestone|firmdale|fish|fishing|fit|fitness|fj|fk|flights|florist|flowers|flsmidth|fly|fm|fo|foo|football|ford|forex|forsale|forum|foundation|fox|fr|fresenius|frl|frogans|fund|furniture|futbol|fyi|ga|gal|gallery|game|garden|gb|gbiz|gd|gdn|ge|gea|gent|genting|gf|gg|ggee|gh|gi|gift|gifts|gives|giving|gl|glass|gle|global|globo|gm|gmail|gmo|gmx|gn|gold|goldpoint|golf|goo|goog|google|gop|got|gov|gp|gq|gr|grainger|graphics|gratis|green|gripe|group|gs|gt|gu|gucci|guge|guide|guitars|guru|gw|gy|hamburg|hangout|haus|healthcare|help|here|hermes|hiphop|hitachi|hiv|hk|hm|hn|hockey|holdings|holiday|homedepot|homes|honda|horse|host|hosting|hoteles|hotmail|house|how|hr|hsbc|ht|hu|hyundai|ibm|icbc|ice|icu|id|ie|ifm|iinet|il|im|immo|immobilien|in|industries|infiniti|info|ing|ink|institute|insurance|insure|int|international|investments|io|ipiranga|iq|ir|irish|is|iselect|ist|istanbul|it|itau|iwc|jaguar|java|jcb|je|jetzt|jewelry|jlc|jll|jm|jmp|jo|jobs|joburg|jot|joy|jp|jprs|juegos|kaufen|kddi|ke|kfh|kg|kh|ki|kia|kim|kinder|kitchen|kiwi|km|kn|koeln|komatsu|kp|kpn|kr|krd|kred|kw|ky|kyoto|kz|la|lacaixa|lamborghini|lamer|lancaster|land|landrover|lasalle|lat|latrobe|law|lawyer|lb|lc|lds|lease|leclerc|legal|lexus|lgbt|li|liaison|lidl|life|lifeinsurance|lifestyle|lighting|like|limited|limo|lincoln|linde|link|live|living|lixil|lk|loan|loans|lol|london|lotte|lotto|love|lr|ls|lt|ltd|ltda|lu|lupin|luxe|luxury|lv|ly|ma|madrid|maif|maison|makeup|man|management|mango|market|marketing|markets|marriott|mba|mc|md|me|med|media|meet|melbourne|meme|memorial|men|menu|meo|mg|mh|miami|microsoft|mil|mini|mk|ml|mm|mma|mn|mo|mobi|mobily|moda|moe|moi|mom|monash|money|montblanc|mormon|mortgage|moscow|motorcycles|mov|movie|movistar|mp|mq|mr|ms|mt|mtn|mtpc|mtr|mu|museum|mutuelle|mv|mw|mx|my|mz|na|nadex|nagoya|name|navy|nc|ne|nec|net|netbank|network|neustar|new|news|nexus|nf|ng|ngo|nhk|ni|nico|ninja|nissan|nl|no|nokia|norton|nowruz|np|nr|nra|nrw|ntt|nu|nyc|nz|obi|office|okinawa|om|omega|one|ong|onl|online|ooo|oracle|orange|org|organic|origins|osaka|otsuka|ovh|pa|page|pamperedchef|panerai|paris|pars|partners|parts|party|pe|pet|pf|pg|ph|pharmacy|philips|photo|photography|photos|physio|piaget|pics|pictet|pictures|pid|pin|ping|pink|pizza|pk|pl|place|play|playstation|plumbing|plus|pm|pn|pohl|poker|porn|post|pr|praxi|press|pro|prod|productions|prof|promo|properties|property|protection|ps|pt|pub|pw|py|qa|qpon|quebec|racing|re|read|realtor|realty|recipes|red|redstone|redumbrella|rehab|reise|reisen|reit|ren|rent|rentals|repair|report|republican|rest|restaurant|review|reviews|rexroth|rich|ricoh|rio|rip|ro|rocher|rocks|rodeo|room|rs|rsvp|ru|ruhr|run|rw|rwe|ryukyu|sa|saarland|safe|safety|sakura|sale|salon|samsung|sandvik|sandvikcoromant|sanofi|sap|sapo|sarl|sas|saxo|sb|sbs|sc|sca|scb|schaeffler|schmidt|scholarships|school|schule|schwarz|science|scor|scot|sd|se|seat|security|seek|select|sener|services|seven|sew|sex|sexy|sfr|sg|sh|sharp|shell|shia|shiksha|shoes|show|shriram|si|singles|site|sj|sk|ski|skin|sky|skype|sl|sm|smile|sn|sncf|so|soccer|social|softbank|software|sohu|solar|solutions|sony|soy|space|spiegel|spreadbetting|sr|srl|st|stada|star|starhub|statefarm|statoil|stc|stcgroup|stockholm|storage|studio|study|style|su|sucks|supplies|supply|support|surf|surgery|suzuki|sv|swatch|swiss|sx|sy|sydney|symantec|systems|sz|tab|taipei|taobao|tatamotors|tatar|tattoo|tax|taxi|tc|tci|td|team|tech|technology|tel|telefonica|temasek|tennis|tf|tg|th|thd|theater|theatre|tickets|tienda|tiffany|tips|tires|tirol|tj|tk|tl|tm|tmall|tn|to|today|tokyo|tools|top|toray|toshiba|tours|town|toyota|toys|tr|trade|trading|training|travel|travelers|travelersinsurance|trust|trv|tt|tube|tui|tushu|tv|tw|tz|ua|ubs|ug|uk|university|uno|uol|us|uy|uz|va|vacations|vana|vc|ve|vegas|ventures|verisign|versicherung|vet|vg|vi|viajes|video|villas|vin|vip|virgin|vision|vista|vistaprint|viva|vlaanderen|vn|vodka|volkswagen|vote|voting|voto|voyage|vu|wales|walter|wang|wanggou|watch|watches|weather|webcam|weber|website|wed|wedding|weir|wf|whoswho|wien|wiki|williamhill|win|windows|wine|wme|work|works|world|ws|wtc|wtf|xbox|xerox|xin|xn--11b4c3d|xn--1qqw23a|xn--30rr7y|xn--3bst00m|xn--3ds443g|xn--3e0b707e|xn--3pxu8k|xn--42c2d9a|xn--45brj9c|xn--45q11c|xn--4gbrim|xn--55qw42g|xn--55qx5d|xn--6frz82g|xn--6qq986b3xl|xn--80adxhks|xn--80ao21a|xn--80asehdb|xn--80aswg|xn--90a3ac|xn--90ais|xn--9dbq2a|xn--9et52u|xn--b4w605ferd|xn--c1avg|xn--c2br7g|xn--cg4bki|xn--clchc0ea0b2g2a9gcd|xn--czr694b|xn--czrs0t|xn--czru2d|xn--d1acj3b|xn--d1alf|xn--eckvdtc9d|xn--efvy88h|xn--estv75g|xn--fhbei|xn--fiq228c5hs|xn--fiq64b|xn--fiqs8s|xn--fiqz9s|xn--fjq720a|xn--flw351e|xn--fpcrj9c3d|xn--fzc2c9e2c|xn--g2xx48c|xn--gecrj9c|xn--h2brj9c|xn--hxt814e|xn--i1b6b1a6a2e|xn--imr513n|xn--io0a7i|xn--j1aef|xn--j1amh|xn--j6w193g|xn--jlq61u9w7b|xn--kcrx77d1x4a|xn--kprw13d|xn--kpry57d|xn--kpu716f|xn--kput3i|xn--l1acc|xn--lgbbat1ad8j|xn--mgb9awbf|xn--mgba3a3ejt|xn--mgba3a4f16a|xn--mgbaam7a8h|xn--mgbab2bd|xn--mgbayh7gpa|xn--mgbb9fbpob|xn--mgbbh1a71e|xn--mgbc0a9azcg|xn--mgberp4a5d4ar|xn--mgbpl2fh|xn--mgbt3dhd|xn--mgbtx2b|xn--mgbx4cd0ab|xn--mk1bu44c|xn--mxtq1m|xn--ngbc5azd|xn--ngbe9e0a|xn--node|xn--nqv7f|xn--nqv7fs00ema|xn--nyqy26a|xn--o3cw4h|xn--ogbpf8fl|xn--p1acf|xn--p1ai|xn--pbt977c|xn--pgbs0dh|xn--pssy2u|xn--q9jyb4c|xn--qcka1pmc|xn--qxam|xn--rhqv96g|xn--s9brj9c|xn--ses554g|xn--t60b56a|xn--tckwe|xn--unup4y|xn--vermgensberater-ctb|xn--vermgensberatung-pwb|xn--vhquv|xn--vuq861b|xn--wgbh1c|xn--wgbl6a|xn--xhq521b|xn--xkc2al3hye2a|xn--xkc2dl3a5ee0h|xn--y9a3aq|xn--yfro4i67o|xn--ygbi2ammx|xn--zfr164b|xperia|xxx|xyz|yachts|yamaxun|yandex|ye|yodobashi|yoga|yokohama|youtube|yt|za|zara|zero|zip|zm|zone|zuerich|zw';

$TUPA_CONF_VARS = array(
	'SYS' => array(			// System related settings.
		'sitename' => 'The Ultimate PowerDNS Admin',			// Name of the base-site.
		'maintenanceEnabled' => 0,					// BOOLEAN |*| |*| If maintenance is enabled, only user "admin" can login into TUPA.
		'debugMode' => '',							// BOOLEAN |*| |*| Enabled debug mode enables PHP errors (down to notices) to be shown and prints parse-time in message field.
		'loginHashTimeout' => 300,					// NUMBER |*| number|0|30|3600 |*| Time in seconds the temp-hash is valid after loading the login page. The hash is used to crypt the password.
		'sessionTTL' => 1800,					// NUMBER |*| number|0|300|86400 |*| Time in seconds the session is valid. This also sets the php value (session.gc_maxlifetime) to this value to ensure it is not smaller than this value.
		'sessionTimeoutWarning' => 60,				// NUMBER |*| number|0|0|1800 |*| Time in seconds before the session times out to open a warning message (set it to 0 to disable).
		'noPconnect' => '',						// BOOLEAN |*| |*| If set, a non-persistent connection is made to database. Non-persistent connections are slower (normally)!
		'languages' => 'en|de_ch|el',						// List of avaiable languages. Splitted by | (not comma!) Only include the languages you really need! (This wil change in future versions). The file in /lang must have the same name (ex. en.inc.php / de_ch.inc.php).
		'language' => 'en',							// Language of the user itself. This value can be changed from the user over the preferences page. It's not identical with langDefault because this one changes!
		'langDefault' => 'en',						// SELECTOR |*| select |*| This language is preselected when creating a new user.
		'langFallback' => 'en',							// DON'T CAHNGE!!! Language to fall back if a word does not exists in configured language (should always be 'en' because this is the language which is always up to date).
		'langMgrServerUrl' => 'http://www.tupa-dns.org/47.733.html',	// DON'T CAHNGE!!! URL to get actual list of languages from
		'styleField' => 'field',							// Used for input fields in forms. Is defined as constant (STYLE_FIELD) in init.php
		'styleButton' => 'button',						// Used for buttons in forms. Is defined as constant (STYLE_BUTTON) in init.php
		'authKeyMaintenance' => 5,					// NUMBER |*| number|0|1|100 |*| Authentication key meintenance. Value between 1 and 100 (%). This is the possibility in % the maintenance runs on page load. Values over 10 makes no sense.
		'logMaintenanceDays' => 0,					// NUMBER |*| number|0|0|3660 |*| Number of days to keep in log table. Set it to 0 to disable. The maintenance function takes the value witch matchs FIRST (logMaintenanceDays or logMaintenanceRecords). (Older logs are deleted permanetly!)
		'logMaintenanceRecords' => 0,				// NUMBER |*| number|0|0|1000000 |*| Number of records to keep in log table. Set it to 0 to disable, The maintenance function takes the value witch matchs FIRST (logMaintenanceDays or logMaintenanceRecords). (Older logs are deleted permanetly!) 
		'notificationEmail' => 'notification@example.com',		// STRING |*| email|1 |*| Senders eMail address where notifications come from. (not used yet)
		'notificationName' => 'TUPA DNS Notification',		// STRING |*| blank |*| Senders Name where notifications come from. (not used yet)
		'optimizeJsCode' => 1,					// BOOLEAN |*| |*| if set to true the javascript code is optimized by removing unneeded stuff like comments, tabulators and line breakes. (You should only disable it for development)
	),
	'DNS' => array(				// DNS Related settings
		'defaultTTL' => 86400,					// NUMBER |*| number|0|3600|604800 |*| The default TTL for new DNS records.
		'defaultPrio'	=> 20,						// NUMBER |*| number|0|1|65535 |*| The default priority for new DNS records.
		'defaultSoaPrimary' => 'your.primary-ns-name.com',	// STRING |*| custom||^[a-zA-Z0-9][a-zA-Z0-9\-\.]*\.[a-zA-Z]{2,6} |*| The default primary nameserver for new SOA records.
		'soaPrimary' => '',							// Primary nameserver for new SOA records (Set by group and/or user preferences).
		'allowSoaPrimaryChange' => 1,				// BOOLEAN |*| |*| Set this value to if you and your users should be able to edit the primary nameserver in the SOA records.
		'defaultSoaHostmaster' => 'hostmaster@email.com',	// STRING |*| email|1 |*| The default hostmaster eMail for new SOA records.
		'soaHostmaster' => '',						// Hostmaster eMail for new SOA records (Set by group and/or user preferences).
		'allowSoaHostmasterChange' => '',			// BOOLEAN |*| |*| Set this value to if you and your users should be able to edit the SOA hostmaster eMail address
		'allowDomainNameChange' => 1,				// BOOLEAN |*| |*| Set this value to if you and your users should be able to edit the domain name when editing a domain
		'enableFancyRecords' => '',						// BOOLEAN |*| |*| Set this value to if you use fancy records. ( http://downloads.powerdns.com/documentation/html/fancy-records.html )
		'additionalTLDs' => '',						// STRING |*| custom|bok||^[a-zA-Z0-9]+(\|[a-zA-Z0-9]+)*$ |*| If you have additional TLD's for internal use add a "|"-separated list (f.ex first|second|third )
		'defaultSoaRefresh' => 10800,				// NUMBER |*| number|0|3600|86400 |*| The default refresh time for new SOA records.
		'minSoaRefresh' => 3600,						// Minimum allowed refresh time
		'maxSoaRefresh' => 86400,						// Maximum allowed refresh time
		'defaultSoaRetry' => 3600,					// NUMBER |*| number|0|30|86400 |*| The default retry time for new SOA records.
		'minSoaRetry' => 30,							// Minimum allowed retry time
		'maxSoaRetry' => 86400,						// Maximum allowed retry time
		'defaultSoaExpire' => 604800,				// NUMBER |*| number|0|86400|2419200 |*| The default expire time for new SOA records.
		'minSoaExpire' => 86400,						// Minimum allowed expire time
		'maxSoaExpire' => 2419200,						// Maximum allowed expire time
		'defaultSoaTTL' => 86400,					// NUMBER |*| number|0|3600|604800 |*| The default TTL time for new SOA records.
		'minSoaTTL' => 3600,						// Minimum allowed TTL time
		'maxSoaTTL' => 604800,						// Maximum allowed TTL time
		'defaultDomainType' => 'NATIVE',				// STRING |*| blank |*| The default type for PowerDNS Domains. (Only "NATIVE" is officially supported by TUPA)
	),
	'SKINS' => array(			// Skins related settings.
		'skins' => 'default',							// List of avaiable skins splitted by "|". Names MUST be identical with directory name.
		'skin' => 'default',							// Skin of user. It's overritten by user preferences if set there.
		'skinDefault' => 'default',					// SELECTOR |*| select |*| This skin is preselected when creating a new user.
		'skinFallback' => 'default',						// DON'T CAHNGE!!! Skin to fall back if file is not found in configured skin. (Maybe if new functions are integrated may no all skins ar up to date)
		'skinMgrServerUrl' => 'http://www.tupa-dns.org/52.734.html',	// URL to get actual list of skins from
	),
	'PREFS' => array(			// User related settings.
		'linesPerSite' => 10,						// Maximal lines in show view.
		'defLinesPerSite' => 10,					// NUMBER |*| number|0|1|10000 |*| Default amount of lines to show in list view.
		'minLinesPerSite' => 2,					// NUMBER |*| number|0|1|10000 |*| Minimum lines possible.
		'maxLinesPerSite' => 200,					// NUMBER |*| number|0|1|10000 |*| Maximum lines possible.
		'naviShowPages' => 10,						// Number of pages to show in navigation (also if there are not so much pages)
		'defNaviShowPages' => 10,					// NUMBER |*| number|0|1|100 |*| Default number of pages to show in navigation.
		'minNaviShowPages' => 2,					// NUMBER |*| number|0|1|100 |*| Minimum pages possible.
		'maxNaviShowPages' => 20,					// NUMBER |*| number|0|1|100 |*| Maximum pages possible.
		'startPage' => '',							// Users configured start page
		'displayHelp' => 1,							// Enable/disable help (0=disabled / 1=layer / 2=popup)
		'defDisplayHelp' => 1,					// SELECTOR |*| select |*| The default help setting.
		'disableTabs' => 0,	// OBSOLETE !!					// Disable the splitting of content into multible tabs
	),
	'GROUPS' => array(		// Database fields shown on groups
		'showFields' => 'name',
	),
	'USERS' => array(		// Database fields shown on users
		'showFields' => 'username,name,firstname',
	),
	'TEMPLATES' => array(	// Database fields shown on templates
		'showFields' => 'name',
	),
	'DOMAINS' => array(		// Domain related settings.
		'showFields' => 'name',
		'maxTmplDomains' => 50,					// NUMBER |*| number|0|1|10000 |*| INFO: Maximal number of domains which can be created in one time with a template. I searched a way to calculate an appoximate value depending on your cpu speed:<br />Get the "bogomips"-value from /proc/cpuinfo (or calculate the value with the table at: http://en.wikipedia.org/wiki/Bogomips ) Clock speed is in MHz! So you should get around 6'000 with a 3GHz P4 (3000MHz * 2.00)<br /> Divid your bogomips value with 24 and you have a good value with 50% savety (on my P4: 6000/24=250)
	),
	'LOGGING' => array(		// Logging related settings.
		'showItemAmount' => '10, 25, 50, 100, 250, 500, 1000',	// STRING |*| custom||^([0-9]\s?)+([,]\s?([0-9]\s?)+)*$ |*| Comma separated list of possible amounts of log entries to show in select box.
		'defItemAmount' => 25,					// SELECTOR |*| select |*| Default selected amount of messages on a page.
		'refresh' => '5, 10, 15, 30, 45, 60',				// STRING |*| custom||^([0-9]\s?)+([,]\s?([0-9]\s?)+)*$ |*| Comma separated list of possible refresh times (in seconds) to show in select box.
		'itemAmount' => 25,							// Amount of messages on a page. Can be changed by user preferences.
	),
	'SYSINFO' => array(		// System information related settings.
		'shortSysinfoRefresh' => 30,					// NUMBER |*| number|0|5|3600 |*| Update interval of administrators short system informations in seconds (top right).
		'loadUpperThreshold' => 2					// NUMBER |*| number|1|0|100 |*| Upper load Threshold. If the load is higher than this value it is shown in red and bold (or other style, depends on selected skin).
	),
	'HELP' => array(		// Help options
		'draggableXPos' => 235,						// Base x position for "draggable layer" help in px (Should only be changed in template config)
		'draggableYPos' => 620						// Base y position for "draggable layer" help in px (Should only be changed in template config)
	),
	'CRON' => array(		// Cronjob related settings.
		'insertRrdDemoData' => '',					// BOOLEAN |*| |*| If set, TUPA generates random RRD values itself. Only needed for demo installation.
		'pdnsConfigName' => ''					// STRING |*| |*| Set the config name if you need one (if you have f.ex. an init script called "pdns-something" and a config file called "pdns-something.conf" you have to set this to "something"). Leave it blank if you don't know what im talking about.
	),
	'RRD' => array(	// RRD graphics generation (Should only be changed in template config)
		'CONFIG' => array(
			'WIDTH' => 500,				// Width of the canvas (not of the generated image)
			'HEIGHT' => 100,				// Height of the canvas (not of the generated image)
			'TABWIDTH' => 25,				// With in pixels of a tabulator
			'COLOR' => array(	// Colors
				'BACK' => '#ffffff',			// Background color of the image
				'CANVAS' => '#ffffff',			// Canvas background color
				'SHADEA' => '#ffffff',			// Top and left border color
				'SHADEB' => '#ffffff'			// Bottom and right border color
			),
			'FONT' => array(	// Font and sizes
				'FILE' => 'lib/rrd_font.ttf',		// Used ttf font
				'LEGEND' => 7,			// Legend text size
				'AXIS' => 6,				// Axis text size
				'UNIT' => 7,				// Unit sizes
				'TITLE' => 7				// Title size
			)
		),
		'QUERIES' => array(	// PDNS queries chart
			0 => array(	// udp-queries
				'NAME' => 'udp-queries',		// RRD's vname (A-Z, a-z, 0-9, -, _ and maximal 255 chars)
				'DATA' => 'AVERAGE',		// Data to draw (normaly AVERAGE, maybe MIN or MAX)
				'STYLE' => 'AREA',			// Style of the drawn data (AREA or LINE)
				'COLOR' => '#00ff00',		// Color of the area/line
				'LEGEND' => array(
					0 => array(
						'DATA' => 'MIN',
						'VALUE_FORMAT' => '%5.1lf %Sqps',	// Value format
					),
					1 => array(
						'DATA' => 'AVERAGE',
						'VALUE_FORMAT' => '%5.1lf %Sqps',	// Value format
					),
					2 => array(
						'DATA' => 'MAX',
						'VALUE_FORMAT' => '%5.1lf %Sqps',	// Value format
					)
				)
			),
			1 => array(	// tcp-queries
				'NAME' => 'tcp-queries',		// RRD's vname (A-Z, a-z, 0-9, -, _ and maximal 255 chars)
				'DATA' => 'AVERAGE',		// Data to draw (normaly AVERAGE, maybe MIN or MAX)
				'STYLE' => 'LINE',			// Style of the drawn data (AREA or LINE)
				'COLOR' => '#0000ff',		// Color of the area/line
				'LEGEND' => array(
					0 => array(
						'DATA' => 'MIN',
						'VALUE_FORMAT' => '%5.1lf %Sqps',	// Value format
					),
					1 => array(
						'DATA' => 'AVERAGE',
						'VALUE_FORMAT' => '%5.1lf %Sqps',	// Value format
					),
					2 => array(
						'DATA' => 'MAX',
						'VALUE_FORMAT' => '%5.1lf %Sqps',	// Value format
					)
				)
			)
		),
		'LATENCY' => array(	// PDNS latency chart
			0 => array(	// udp-queries
				'NAME' => 'latency',			// RRD's vname (A-Z, a-z, 0-9, -, _ and maximal 255 chars)
				'DATA' => 'AVERAGE',		// Data to draw (normaly AVERAGE, maybe MIN or MAX)
				'STYLE' => 'LINE',			// Style of the drawn data (AREA or LINE)
				'COLOR' => '#0000ff',		// Color of the area/line
				'LEGEND' => array(
					0 => array(
						'DATA' => 'MIN',
						'VALUE_FORMAT' => '%4.1lf %Ssec',	// Value format
					),
					1 => array(
						'DATA' => 'AVERAGE',
						'VALUE_FORMAT' => '%4.lf %Ssec',	// Value format
					),
					2 => array(
						'DATA' => 'MAX',
						'VALUE_FORMAT' => '%4.1lf %Ssec',	// Value format
					)
				)
			),
		)
	),
	'MISC' => array(	// Mic stuff
		'copyrightNotice' => '/***************************************************************
*  Copyright notice
*
*  (c) 2005-2007 Urs Weiss (urs@tupa-dns.org)
*  All rights reserved
*
*  This file is part of TUPA.
*
*  TUPA is free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*  A copy is found in the textfile GPL.txt and important notices to the license
*  from the author is found in LICENSE.txt distributed with these scripts.
*
*  TUPA is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/',

		'phpDocLangHeader' => '/**
 * Language file (###LANGUAGE###)
 *
 * @package 	TUPA
<!-- ###AUTHOR_P### begin --> * @author 	###AUTHOR### <###EMAIL###><!-- ###AUTHOR_P### end -->
 */',

	'defaultIndexLevel2' => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<title>TUPA - The Ultimate PowerDNS Admin</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta http-equiv="refresh" content="0; url=../../" />
	</head>
	<body>
	</body>
</html>'
	),
);

// TUPA version
$TUPA_VERSION = '0.1-rc1';
define('TUPA_version', $TUPA_VERSION);
define('TUPA_branch', '0.1rc1');

// Database-variables are cleared!
$tupa_db = '';					// The database name
$tupa_db_username = '';			// The database username
$tupa_db_password = '';			// The database password
$tupa_db_host = '';				// The database host
$tupa_db_port = '';				// The database port
$installer_password = '';			// Installer password

// overrite with users site configuration
if (@is_file(PATH_config.'config_site.inc.php')) {
	require(PATH_config.'config_site.inc.php');
} else {
	die('"'. PATH_config .'config_site.inc.php" was not found!<br />You have to copy the file"config/config_site-dist.inc.php" to "config/config_site.inc.php" and change to permission of the file to make it writable by the webserver user when you install TUPA the first time. Start the installer over your browser after you did that (installer/ subdirectory).');
}

// Set $TUPA_CONF_VARS['REGEX']
// Because there is a new config option to add additional TLD's we have to create them after inclusion of site config
if ($TUPA_CONF_VARS['DNS']['additionalTLDs'] != '') {
	$topLevelDomains .= '|'. $TUPA_CONF_VARS['DNS']['additionalTLDs'];
}
$TUPA_CONF_VARS['REGEX'] = array(	// Some used regular expressions. DON'T CHANGE!!
	'templateDomain' =>'^%DOMAIN%$|(^[a-zA-Z0-9][a-zA-Z0-9\-\.]*\.(%DOMAIN%|'. $topLevelDomains .'))$',
	'domain' =>'(^[a-zA-Z0-9][a-zA-Z0-9\-\.]*\.('. $topLevelDomains .')$)',
	//'textareaMultiDomains' =>'^([a-zA-Z0-9][a-zA-Z0-9\-\.]+\.('. $topLevelDomains .')(\n)?)*$',
	'textareaMultiDomains' =>'^([a-zA-Z0-9][a-zA-Z0-9\-\.]+\.('. $topLevelDomains .')(\n|\r\n)?){1,%maxTmplDomains%}$',
	'host' => '^[a-zA-Z0-9\*_]([a-zA-Z0-9\-\._]*)$',
	'IPv4' => '^(25[0-5]|2[0-4]\d|1\d\d|\d?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|\d?\d)){3}$',
	'IPv6' => '^((([0-9A-Fa-f]{1,4}:){7}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}:[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){5}:([0-9A-Fa-f]{1,4}:)?[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){4}:([0-9A-Fa-f]{1,4}:){0,2}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){3}:([0-9A-Fa-f]{1,4}:){0,3}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){2}:([0-9A-Fa-f]{1,4}:){0,4}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(([0-9A-Fa-f]{1,4}:){0,5}:((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(::([0-9A-Fa-f]{1,4}:){0,5}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|([0-9A-Fa-f]{1,4}::([0-9A-Fa-f]{1,4}:){0,5}[0-9A-Fa-f]{1,4})|(::([0-9A-Fa-f]{1,4}:){0,6}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){1,7}:))$',
	'PTR' => '^([0-9]{0,3}((\.)?([0-9]{1,3})){0,3})$|^((([0-9A-Fa-f]\.){31}|([0-9A-Fa-f]\.){27}|([0-9A-Fa-f]\.){23}|([0-9A-Fa-f]\.){19}|([0-9A-Fa-f]\.){15}|([0-9A-Fa-f]\.){11}|([0-9A-Fa-f]\.){7}|([0-9A-Fa-f]\.){3})[0-9A-Fa-f])$',
	'HINFO' => '^([A-Za-z0-9\-\/])+ ([A-Za-z0-9\-\/])+$',
	'RP' => '^(([A-Za-z0-9\._-]+[@][A-Za-z0-9\._-]+\.('. $topLevelDomains .'))|\.) (([a-zA-Z0-9][a-zA-Z0-9\-\.]*\.('. $topLevelDomains .'))|\.)$',
	'SRV_CONTENT' => '^[0-9]{0,5} [0-9]{0,5} (([a-zA-Z0-9][a-zA-Z0-9\-\.]*\.('. $topLevelDomains .'))|\.)$',
	'SRV_NAME' => '^_[A-Za-z-]+\._[A-Za-z-]+$',
	'LOC' => '^(90|[0-8]\d|\d)(\s([0-5]\d|\d)){0,2}\s[NS]\s(180|1[0-7]\d|\d?\d)(\s([0-5]\d|\d)){0,2}\s[EW]$',
	//'numeric' => '^\\d+$',
	'time24' => '^(((0?[0-9])|(1[0-9])|(2[0-4]))\:([0-5][0-9]))$',
	'url' => '^(http|https)\:\/\/[a-zA-Z0-9][a-zA-Z0-9\-\.]*\.('. $topLevelDomains .')$',
);
unset($topLevelDomains);

// Defining the database setup as constants
define('TUPA_db', $tupa_db);
define('TUPA_db_username', $tupa_db_username);
define('TUPA_db_password', $tupa_db_password);
if ($tupa_db_host == '') $tupa_db_host = 'localhost';
define('TUPA_db_host', $tupa_db_host);
if ($tupa_db_port == '') $tupa_db_port = 3306;
define('TUPA_db_port', $tupa_db_port);
define('TUPA_installer_password', $installer_password);


// Unsetting the configured values. Use of these are depreciated.
unset($tupa_db);
unset($tupa_db_username);
unset($tupa_db_password);
unset($tupa_db_host);
unset($tupa_db_port);
unset($installer_password);

?>
