<?php
require_once "include/simplehtmldom/simple_html_dom.php";
$base_url = "http://elezionistorico.interno.it";

// questo url serve per test su elezioni con candidati + liste
$start_url = $base_url."/index.php?tpel=C&dtel=13/04/2008";

// questo url serve per test su elezioni con uninominale+proporzionale
// $start_url = $base_url."/index.php?tpel=C&dtel=21/04/1996";

$page_num = 0;
$max_page_num = 10;
$max_depth = $_REQUEST['m']>0? $_REQUEST['m']: 5;

ob_start();


?><html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="pragma" content="no-cache">
<title>Test lettura elezioni</title>
<style>
body {
	font-family: Verdana, Tahoma, Helvetica;
	font-size: 9pt;
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
<h1>Esempio di scraper per lettura dati elettorali<br>Parametri di elaborazione</h1>
<h3>Pagina root: <?= $start_url ?><br>
Massimo numero di pagine: <?= $max_page_num ?><br>
Massima profondit&agrave;: <?= $max_depth ?> - questo parametro pu&ograve; essere modificato aggiungendo "?m=numero&gt;0" all'indirizzo di questo script
</h3><hr>
<?
// parametri
$data = array(
	'root' => array(
		'tipo'=>'C',
		'data' => '13/04/2008'
	),
	'unipro' => null,
	'arg' => null,
	// 'children' => null,
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


// start elaborazione
set_time_limit(0);
$start_time = time();
getUrls($start_url, $data);
endExec();

function getUrls($url, $data) {
	global $base_url, $page_num, $max_page_num, $max_depth, $livelli;
	$page_num++;
	// raggiunto max numero di pagine?
	if($page_num > $max_page_num) {
		endExec("Raggiunto max numero di pagine");
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
		
		// get dati di riepilogo, se presenti, altrimenti è una pagina "di passaggio"
		$riepilogo = $page->find(".dati_riepilogo",0);
		if($riepilogo) {
			$data['elettori'] = norm_voto($riepilogo->find("[headers=helettori]", 0)->plaintext);
			$data['votanti'] = norm_voto($riepilogo->find("[headers=hvotanti]", 0)->plaintext);
			$data['bianche'] = norm_voto($riepilogo->find("[headers=hskbianche]", 0)->plaintext);
			$data['nonvalide'] = norm_voto($riepilogo->find("[headers=hsknonvaliderER]", 0)->plaintext);
			
			$risultati = $page->find(".dati",0)->find("tr");
			$candidato=null;
			$data['candidati'] = array();
			$data['liste'] = array();
			$data['apparentamenti'] = array();
			for ($i=1, $max_i=count($risultati)-1; $i<$max_i; $i++) {
				$r = $risultati[$i];
				// riga candidato
				if($r->find("[headers*=hcandidato]")) {
					$candidato = $r->find("[headers=hcandidato]",0)->plaintext;
					$eletto = $r->find(".eletto_index",0);
					($voti_candidato = $r->find("[headers*=hvoti]",0)) ?
						$data['candidati'][$candidato] = array('voti'=>norm_voto($voti_candidato->plaintext), 'eletto'=>(! empty($eletto))):
						$data['candidati'][$candidato] = null;
				}
				// riga lista tipo 1
				elseif ($lista = $r->find(".candidato",0)->plaintext) {
					$data['liste'][$lista] = array(
						'voti' => norm_voto($r->find("[headers*=hvoti]",0)->plaintext), 
						'seggi' => $r->find("[headers*=hseggi]",0)->plaintext
					);
					if($img = $r->find('img',0)) $data['liste'][$lista]['img'] = array_pop(explode("?", $img->src));
					if($candidato) $data['apparentamenti'][$lista] = $candidato;
				}
				// riga lista tipo 2
				elseif ($span = $r->find("span[class=simbolo_lista]",0)) {
					$rtds = $r->find("td");
					foreach($rtds as $rtd) {
						if($rtd_content = trim($rtd->innertext)) {
						// possono esserci varie righe di questo tipo in una sola <td>
						// <span class='simbolo_lista'><img src="imgContrassegno.php?tpel=C&amp;dtel=21/04/1996&amp;tpa=I&amp;tpe=L&amp;ne=101&amp;tpseg=L&amp;ncl=1&amp;ccp=1472" title="L'ULIVO" alt="L'ULIVO"/></span>&nbsp;L'ULIVO&nbsp;&nbsp;
							preg_match_all('#<span class=.*?simbolo_lista.+?src="([^"]+?)".+?\&nbsp\;(.+?)\&nbsp#is', $rtd_content, $liste);
							for($l=0, $max_l=count($liste[0]); $l<$max_l;$l++) {
								$lista = preg_replace('#,$#', '', trim($liste[2][$l]));
								$data['liste'][$lista] = array(
									'img' => array_pop(explode("?", $liste[1][$l]))
								);
								if($r->find("[headers*=hvoti]",0)) $data['liste'][$lista]['voti'] = norm_voto($r->find("[headers*=hvoti]",0)->plaintext);
								if($r->find("[headers*=hseggi]",0)) $data['liste'][$lista]['seggi'] = $r->find("[headers*=hseggi]",0)->plaintext;
								if($candidato) $data['apparentamenti'][$lista] = $candidato;
							}
						}
					}
				}
			}
		}

		// se utilizzato il sistema uninominale/proporzionale la pagina di default è "proporzionale" 
		// in questo caso legge la pagina "uninominale", che è allo stesso livello di profondità -> non genera ricorsione
		$unipro = $page->find(".unipro",0);
		if( $unipro ) {
			$data['unipro'] = $unipro->find(".activelink",0)->plaintext;
			if($data['unipro'] == 'Proporzionale') getUrls($base_url.htmlspecialchars_decode($unipro->find("a[title=Uninominale]",0)->href), $data);
		}
		
		// se la pagina ha una sottosezione in più rispetto alla pagina parent -> prevede ulteriore dettaglio
		if($data['depth'] > $prec_depth) {
			// gli url delle pagine depth+1 sono in <div class="sezione">
			if($sezione_urls = $page->find('div.sezione', 0)->find('a')) {
				// $data['children'] = count($sezione_urls);
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
				endExec("ERRORE lettura sottosezioni di $url");
			}
		}
		else {
			// $data['children'] = 0;
			preprint_r($data, "$page_num) $title");
		}
		// no memory leaks!
		$page->clear();
		unset($page);
	}
	// pagina non trovata
	else {
		endExec("ERRORE lettura pagina $url");
	}
	return;
}

function norm_voto($v) {
	return preg_replace('#\.#', '', $v);
}

function preprint_r ($arg, $title=null) {
	if($title) print "<h3><u>$title</u></h3>";
	print "<pre>";
	$arg['liste'] ? print_r($arg) : print "Pagina senza dati";
	print "</pre><hr>";
	ob_flush();
	flush();
}

function endExec($msg=null) {
	global $start_time, $page_num;
	$et = time() - $start_time;
	print ($msg ? "<h3>$msg<br>" : "<h3>"). "Elaborazione terminata<br>";
	print "Tempo richiesto $et secondi<br>";
	print "Tempo richiesto per pagina ".round($et/$page_num, 3)." secondi</h3>";
	ob_flush();

	exit;
}
?>
