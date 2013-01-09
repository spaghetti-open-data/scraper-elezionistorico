require_once "include/simplehtmldom/simple_html_dom.php";
set_time_limit(0);
// parametri
$data = array(
	'root' => array(
		'tipo'=>'C',
		'data' => '13/04/2008'
	),
	'arg' => null,
	'content' => null,
	'children' => null,
	'depth' => -1,
	'liv' => array()
);
$livelli=array(
	// italia, italia escluse regioni
	"I" => array(
		'area', 
		'circoscrizione',
		'regione',
		'provincia',
		'comune'
	),
	// valle d'aosta
	"H" => array(
		'area', 
		'circoscrizione',
		'provincia',
		'comune'
	),
	// valle d'aosta + trentino
	"G" => array(
		'area', 
		'regione',
		'collegio',
		'comune'
	),
	// estero
	"E" => array(
		'area', 
		'circoscrizione',
		'ripartizione',
		'nazione',
		'consolato'
	)
);
$base_url = "http://elezionistorico.interno.it";
$start_url = $base_url."/index.php?tpel=C&dtel=13/04/2008";
// contatore pagine
$page_num = 0;
// max numero di pagine da leggere
$max_page_num = 25;
// max profondit�
$max_depth = $_REQUEST['m']>0? $_REQUEST['m']: 5;
?><html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="pragma" content="no-cache">
<title>Test lettura elezioni</title>
<style>
body {
	font-family: Verdana, Tahoma, Helvetica;
	font-size: 8pt;
}
h1 {
	font-size: 12pt;
}
h2 {
	font-size: 11pt;
}
h3 {
	font-size: 10pt;
}
</style>
</head> 
<body>
<h1>Parametri di elaborazione</h1>
<h3>Pagina root: <?= $start_url ?><br>
Massimo numero di pagine: <?= $max_page_num ?><br>
Massima profondit&agrave;: <?= $max_depth ?> - questo parametro pu&ograve; essere modificato mettendo "?m= numero&gt;0" dopo l'indirizzo di questo script
</h3><hr>
<?

// start elaborazione
$start_time = time();
getUrls($start_url, $data);
printElapsedTime();

function getUrls($url, $data) {
	global $base_url, $page_num, $max_page_num, $max_depth, $livelli;
	$page_num++;
	// raggiunto max numero di pagine?
	if($page_num > $max_page_num) {
		print "<h3>Raggiunto max numero di pagine</h3>";
		printElapsedTime();
	}
	
	// get pagina
	$page = file_get_html($url);
	
	// se caricato contenuto
	if($page) {
		// set depth
		$prec_depth = $data['depth'];
		$data['depth'] = count($page->find("div.sezione_panel"))-1;
		
		// elabora dati della pagina
		$title = $page->find("div.titolo_pagina", 0)->plaintext;

		// split argomenti passati alla pagina
		$data['arg'] = array();
		$arg = explode("&", array_pop(explode("?", $url)));
		foreach($arg as $a) {
			list($k, $v) = explode("=", $a);
			$data['arg'][$k] = $v;
		}

		// attribuzione di un valore a tutti i livelli geografici previsti dalla specifica area
		foreach($livelli[$data['arg']['tpa']] as $label) {
			$data['liv'][$label] = (preg_match("#$label (.+?)($| \|)#i", $title, $m) ? $m[1]: null);
		}

		// qui bisogna inserire l'elaborazione dei dati della pagina
		$data['content'] = $page->find(".dati_riepilogo") ? "[ Dati da elaborare ]": "[ Nessun dato da elaborare ]";

		// se la pagina ha una sottosezione in pi� rispetto alla pagina parent -> prevede ulteriore dettaglio
		if($data['depth'] > $prec_depth) {
			// gli url delle pagine depth+1 sono in <div class="sezione">
			if($sezione_urls = $page->find('div.sezione', 0)->find('a')) {
				$data['children'] = count($sezione_urls);
				// stampa i dati prima della recursion
				preprint_r($data, "$page_num) $title");

				// recursion
				if ($data['depth'] < $max_depth) {
					foreach($sezione_urls as $sezione_url) {
						getUrls($base_url.htmlspecialchars_decode($sezione_url->href), $data);
					}
				}
			}
			// almeno un url deve esserci
			else {
				exit("<h1>ERRORE lettura sottosezioni di $url</h1>");
			}
		}
		else {
			$data['children'] = 0;
			preprint_r($data, "$page_num) $title");
		}
		// no memory leaks!
		$page->clear();
		unset($page);
	}
	// pagina non trovata
	else {
		exit("<h1>ERRORE lettura pagina $url</h1>");
	}
	return;
}

function preprint_r ($arg, $title=null) {
	if($title) print "<h3><u>$title</u></h3>";
	print "<pre>";
	print_r($arg);
	print "</pre><hr>";
}

function printElapsedTime() {
	global $start_time, $page_num;
	$et = time() - $start_time;
	print "<h3>Elaborazione terminata<br>";
	print "Tempo richiesto $et secondi<br>";
	print "Tempo richiesto per pagina ".round($et/$page_num, 3)." secondi</h3>";
	exit;
}
