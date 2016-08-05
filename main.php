<?php
/**
* Telegram Bot example for ViaggiareinPuglia.it Lic. IoDL2.0
* @author Francesco Piero Paolicelli @piersoft
*/
//include("settings_t.php");
include("Telegram.php");

class mainloop{
const MAX_LENGTH = 4096;
function start($telegram,$update)
{

	date_default_timezone_set('Europe/Rome');
	$today = date("Y-m-d H:i:s");

		$inline_query = $update["inline_query"];
	$text = $update["message"] ["text"];
	$chat_id = $update["message"] ["chat"]["id"];
	$user_id=$update["message"]["from"]["id"];
	$location=$update["message"]["location"];
	$reply_to_msg=$update["message"]["reply_to_message"];

	$this->shell($inline_query,$telegram,$text,$chat_id,$user_id,$location,$reply_to_msg);
	$db = NULL;

}

 function shell($inline_query,$telegram,$text,$chat_id,$user_id,$location,$reply_to_msg)
{
	date_default_timezone_set('Europe/Rome');
	$today = date("Y-m-d H:i:s");
//	if (strpos($text,'@viaggiareinpugliabot') !== false) $text=str_replace("@viaggiareinpugliabot ","",$text);

	if ($text == "/start" || $text == "Info") {
		$img = curl_file_create('puglia.png','image/png');
		$contentp = array('chat_id' => $chat_id, 'photo' => $img);
		$telegram->sendPhoto($contentp);

		$reply = "Benvenuto. Per ricercare un luogo di interesse turistico, culturale censito da ViaggiareinPuglia.it, digita il nome del Comune oppure clicca sulla graffetta (📎) e poi 'posizione' . Puoi anche ricercare per parola chiave nel titolo anteponendo il carattere ?. Verrà interrogato il DataBase openData utilizzabile con licenza IoDL2.0 presente su http://www.dataset.puglia.it/dataset/luoghi-di-interesse-turistico-culturale-naturalistico . In qualsiasi momento scrivendo /start ti ripeterò questo messaggio di benvenuto.\nQuesto bot, non ufficiale e non collegato con il marchio regionale ViaggiareinPuglia.it, è stato realizzato da @piersoft e potete migliorare il codice sorgente con licenza MIT che trovate su https://github.com/piersoft/viaggiareinpugliabot. La propria posizione viene ricercata grazie al geocoder di openStreetMap con Lic. odbl.";
		$reply .="\nWelcome. To search for a place of tourist, cultural surveyed by ViaggiareinPuglia.it, type the name of the municipality or click on the paper clip (📎) and then 'position'. You can also search by keyword in the title prefixing the character ?. Will be questioned DataBase OpenData IoDL2.0 used with this license on http://www.dataset.puglia.it/dataset/luoghi-di-interesse-turistico-culturale-naturalistico. At any time by writing /start will repeat this message of welcome. \nThis bots, unofficial and unconnected with the regional brand ViaggiareinPuglia.it, has been realized by @piersoft and you can improve the source code under the MIT license that found on https://github.com/piersoft/viaggiareinpugliabot. Its position is searched through the geocoder OpenStreetMap with Lic. ODbL.";
		$content = array('chat_id' => $chat_id, 'text' => $reply,'disable_web_page_preview'=>true);
		$telegram->sendMessage($content);
		$log=$today. ";new chat started;" .$chat_id. "\n";
		$this->create_keyboard_temp($telegram,$chat_id);

		exit;
		}
		elseif ($text == "Città/City") {
			$reply = "Digita direttamente il nome del Comune. / Type city or Town";
			$content = array('chat_id' => $chat_id, 'text' => $reply,'disable_web_page_preview'=>true);
			$telegram->sendMessage($content);
			$log=$today. ";new chat started;" .$chat_id. "\n";
			exit;
			}
			elseif ($text == "Ricerca/Search") {
				$reply = "Digita la parola da cercare anteponendo il carattere ?, ad esempio: ?Chiesa Matrice";
				$reply .="\nType in the search word by prefixing the character?, For example: ?Chiesa Matrice";
				$content = array('chat_id' => $chat_id, 'text' => $reply,'disable_web_page_preview'=>true);
				$telegram->sendMessage($content);
				$log=$today. ";new chat started;" .$chat_id. "\n";
				exit;
			}elseif (strpos($inline_query["location"],'.') !== false){
					$this->location_manager_inline($inline_query,$telegram,$user_id,$chat_id,$location);
					exit;
				}elseif ($text == "/location" || $text == "Posizione") {

					$option = array(array($telegram->buildKeyboardButton("Invia la tua posizione / send your location", false, true)) //this work
		                        );
		    // Create a permanent custom keyboard
		    $keyb = $telegram->buildKeyBoard($option, $onetime=false);
		    $content = array('chat_id' => $chat_id, 'reply_markup' => $keyb, 'text' => "Attiva la localizzazione sul tuo smartphone / Turn on your GPS");
		    $telegram->sendMessage($content);
		    exit;
				}
			elseif($location!=null)
		{

			$this->location_manager($telegram,$user_id,$chat_id,$location);
			exit;
		}

		else{
			function extractString($string, $start, $end) {
					$string = " ".$string;
					$ini = strpos($string, $start);
					if ($ini == 0) return "";
					$ini += strlen($start);
					$len = strpos($string, $end, $ini) - $ini;
					return substr($string, $ini, $len);
			}
			if (strpos($text,' ') !== false){
			  $text=extractString($text,"/"," ");
			}
			if (strpos($text,'1') !== false || strpos($text,'2') !== false ||strpos($text,'3') !== false ||strpos($text,'4') !== false ||strpos($text,'5') !== false ||strpos($text,'6') !== false ||strpos($text,'7') !== false ||strpos($text,'8') !== false ||strpos($text,'9') !== false ) {
				$text="/".$text;
			}

			$string=0;
			$optionf=array([]);
			if(strpos($text,'?') !== false){
				$text=str_replace("?","",$text);
				$location="Sto cercando i luoghi aventi nel titolo: ".$text;
				$content = array('chat_id' => $chat_id, 'text' => $location,'disable_web_page_preview'=>true);
				$telegram->sendMessage($content);
				$string=1;
	//			sleep (1);
			}elseif(strpos($text,'/') === false){
				$location="Sto cercando i luoghi di interesse per località comprendente: ".$text;
				$location .="\nI'm looking for the places of interest in locations including:".$text;
				$content = array('chat_id' => $chat_id, 'text' => $location,'disable_web_page_preview'=>true);
				$telegram->sendMessage($content);
				$string=0;
		//		sleep (1);
	}elseif(strpos($text,'/') !== false){
		$text=str_replace("/","",$text);
		$string=2;

	}

			$urlgd="db/luoghi.csv";

			  $inizio=0;
			  $homepage ="";
			$csv = array_map('str_getcsv',file($urlgd));
	  	$count = 0;
				foreach($csv as $data=>$csv1){
					$count = $count+1;
				}
			if ($count ==0 || $count ==1)
			{
						$location="Nessun luogo trovato / No founds";
						$content = array('chat_id' => $chat_id, 'text' => $location,'disable_web_page_preview'=>true);
						$telegram->sendMessage($content);
			}
			function decode_entities($textt)
			{

							$textt=htmlentities($textt, ENT_COMPAT,'ISO-8859-1', true);
						$textt= preg_replace('/&#(\d+);/me',"chr(\\1)",$textt); #decimal notation
							$textt= preg_replace('/&#x([a-f0-9]+);/mei',"chr(0x\\1)",$textt);  #hex notation
						$textt= html_entity_decode($textt,ENT_COMPAT,"UTF-8"); #NOTE: UTF-8 does not work!

							return $textt;
			}

			$result=0;
			$ciclo=0;
//if ($count > 40) $count=40;
  for ($i=$inizio;$i<$count;$i++){

if ($string==1) {
	$filter= strtoupper($csv[$i][0]);
}elseif ($string==0){
	$filter=strtoupper($csv[$i][3]);
}elseif ($string==2){
	$content = array('chat_id' => $chat_id, 'text' => $text,'disable_web_page_preview'=>true);
	$telegram->sendMessage($content);
	$i=intval($text);
	$result=1;
	$homepage .="\nID: /".$i."\n";
	$homepage .="Nome: / Name: ".decode_entities($csv[$i][0])."\n";
	$homepage .="Risorsa: / Resource: ".decode_entities($csv[$i][1])."\n";

	if($csv[$i][4] !=NULL) $homepage .="Indirizzo: / Address: ".decode_entities($csv[$i][4]);
	if($csv[$i][5] !=NULL)	$homepage .=", ".decode_entities($csv[$i][5]);
	$homepage .="\n";
	if($csv[$i][3] !=NULL)$homepage .="Comune: / City: ".decode_entities($csv[$i][3])."\n";
	if(strpos($csv[$i][10],'http') !== false)$homepage .="Web: ".decode_entities($csv[$i][9])."\n";
	if($csv[$i][10] !=NULL)	$homepage .="Email: ".decode_entities($csv[$i][10])."\n";
//	if($csv[$i][22] !=NULL)	$homepage .="Descrizione: ".substr(decode_entities($csv[$i][22]), 0, 400)."..[....]\n";
	if($csv[$i][11] !=NULL)	$homepage .="Tel: ".decode_entities($csv[$i][11])."\n";
	if($csv[$i][14] !=NULL)	$homepage .="Servizi: / Service: ".$csv[$i][14]."\n";
	if($csv[$i][15] !=NULL)	$homepage .="Attrezzature: / Various: ".decode_entities($csv[$i][15])."\n";
	if($csv[$i][16] !=NULL)	$homepage .="Foto1: / Photo1: ".decode_entities($csv[$i][16])."\n";
	if($csv[$i][17] !=NULL) $homepage .="(realizzata da: / By: ".decode_entities($csv[$i][17]).")\n";
	if($csv[$i][18] !=NULL)	$homepage .="Foto2: / Photo2: ".decode_entities($csv[$i][18])."\n";
	if($csv[$i][19] !=NULL) $homepage .="(realizzata da: / By: ".decode_entities($csv[$i][19]).")\n";


	$homepage .="\n____________\n";
	$chunks = str_split($homepage, self::MAX_LENGTH);
	foreach($chunks as $chunk) {
	$content = array('chat_id' => $chat_id, 'text' => $chunk,'disable_web_page_preview'=>false);
	$telegram->sendMessage($content);
	if($csv[$i][7] !=NULL){
				$homepagemappa = "http://www.openstreetmap.org/?mlat=".$csv[$i][7]."&mlon=".$csv[$i][8]."#map=19/".$csv[$i][7]."/".$csv[$i][8];

		$option = array( array( $telegram->buildInlineKeyboardButton("MAPPA", $url=$homepagemappa)));
$keyb = $telegram->buildInlineKeyBoard($option);
$content = array('chat_id' => $chat_id, 'reply_markup' => $keyb, 'text' => "<b>Vai alla</b>",'parse_mode'=>"HTML");
$telegram->sendMessage($content);
//		$homepage .="Mappa: / Map: \n";
	}
}
	$this->create_keyboard_temp($telegram,$chat_id);

	exit;
	}



if (strpos(decode_entities($filter),strtoupper($text)) !== false ){
				$ciclo++;
//	if ($ciclo >40) exit;
	array_push($optionf,["/".$i." ".decode_entities($csv[$i][0])]);
				$result=1;
				$homepage .="\nClicca l'ID per dettagli: /".$i."\n";
				$homepage .="Nome: / Name: ".decode_entities($csv[$i][0])."\n";
/*
				$homepage .="Risorsa: / Resource: ".decode_entities($csv[$i][1])."\n";
				if($csv[$i][4] !=NULL) $homepage .="Indirizzo: / Address: ".decode_entities($csv[$i][4]);
				if($csv[$i][5] !=NULL)	$homepage .=", ".decode_entities($csv[$i][5]);
				$homepage .="\n";
				if($csv[$i][3] !=NULL)$homepage .="Comune: / City: ".decode_entities($csv[$i][3])."\n";
				if($csv[$i][9] !=NULL)$homepage .="Web: ".decode_entities($csv[$i][9])."\n";
				if($csv[$i][10] !=NULL)	$homepage .="Email: ".decode_entities($csv[$i][10])."\n";
			//	if($csv[$i][22] !=NULL)	$homepage .="Descrizione: ".substr(decode_entities($csv[$i][22]), 0, 400)."..[....]\n";
				if($csv[$i][11] !=NULL)	$homepage .="Tel: ".decode_entities($csv[$i][11])."\n";
				if($csv[$i][14] !=NULL)	$homepage .="Servizi: / Service: ".decode_entities($csv[$i][14])."\n";
				if($csv[$i][15] !=NULL)	$homepage .="Attrezzature: / Various: ".decode_entities($csv[$i][15])."\n";
				if($csv[$i][16] !=NULL)	$homepage .="Foto1: / Photo1".decode_entities($csv[$i][16])."\n";
				if($csv[$i][17] !=NULL) $homepage .="(realizzata da: / By: ".decode_entities($csv[$i][17]).")\n";
				if($csv[$i][18] !=NULL)	$homepage .="Foto2: / Photo2: ".decode_entities($csv[$i][18])."\n";
				if($csv[$i][19] !=NULL) $homepage .="(realizzata da: / By: ".decode_entities($csv[$i][19]).")\n";
				if($csv[$i][7] !=NULL){
					$homepage .="Mappa: / Map: \n";
					$homepage .= "http://www.openstreetmap.org/?mlat=".$csv[$i][7]."&mlon=".$csv[$i][8]."#map=19/".$csv[$i][7]."/".$csv[$i][8];
				}
*/
				$homepage .="____________\n";
				}

				if ($ciclo >300) {
					$location="Troppi risultati per essere visualizzati (più di 300). Restringi la ricerca";
					$location .="\nToo many results to be displayed (more than 300). Narrow Search";
					$content = array('chat_id' => $chat_id, 'text' => $location,'disable_web_page_preview'=>true);
					$telegram->sendMessage($content);

					 exit;
				}
				}

		$chunks = str_split($homepage, self::MAX_LENGTH);
		foreach($chunks as $chunk) {
		$content = array('chat_id' => $chat_id, 'text' => $chunk,'disable_web_page_preview'=>true);
		$telegram->sendMessage($content);

		}

				$keyb = $telegram->buildKeyBoard($optionf, $onetime=false);
				$content = array('chat_id' => $chat_id, 'reply_markup' => $keyb, 'text' => $ciclo." luoghi/places");
				$telegram->sendMessage($content);
				$content = array('chat_id' => $chat_id, 'reply_markup' => $keyb, 'text' => "[Clicca su ID per dettagli / Click on ID for details]");
				$telegram->sendMessage($content);

	}
//	$this->create_keyboard_temp($telegram,$chat_id);

	}

	function create_keyboard_temp($telegram, $chat_id)
	 {
			 $option = array(["Città/City","Ricerca/Search"],["Posizione","Info"]);
			 $keyb = $telegram->buildKeyBoard($option, $onetime=false);
			 $content = array('chat_id' => $chat_id, 'reply_markup' => $keyb, 'text' => "[Digita l'ID, un Comune, una Ricerca oppure invia la tua posizione tramite la graffetta (📎) o clicca /location]\n[Enter ID or a City, a search or send your location via the clip (📎) or click /location]");
			 $telegram->sendMessage($content);
	 }



function location_manager($telegram,$user_id,$chat_id,$location)
	{
	$optionf=array([]);
			$lon=$location["longitude"];
			$lat=$location["latitude"];
			$r=1;
			$response=$telegram->getData();
			$response=str_replace(" ","%20",$response);

				$reply="http://nominatim.openstreetmap.org/reverse?email=piersoft2@gmail.com&format=json&lat=".$lat."&lon=".$lon."&zoom=18&addressdetails=1";
				$json_string = file_get_contents($reply);
				$parsed_json = json_decode($json_string);
				//var_dump($parsed_json);
				$comune="";
				$temp_c1 =$parsed_json->{'display_name'};

				if ($parsed_json->{'address'}->{'town'}) {
					$temp_c1 .="\nCittà: ".$parsed_json->{'address'}->{'town'};
					$comune .=$parsed_json->{'address'}->{'town'};
				}else 	$comune .=$parsed_json->{'address'}->{'city'};

				if ($parsed_json->{'address'}->{'village'}) $comune .=$parsed_json->{'address'}->{'village'};
				$location="Sto cercando le località contenenti \"".$comune."\" tramite le coordinate che hai inviato: ".$lat.",".$lon;
				$location .="Searching for: ".$comune;
				$content = array('chat_id' => $chat_id, 'text' => $location,'disable_web_page_preview'=>true);
				$telegram->sendMessage($content);

			  $alert="";
			//	echo $comune;
			$urlgd="db/luoghi.csv";

				$inizio=0;
				$homepage ="";
			$csv = array_map('str_getcsv',file($urlgd));
			$count = 0;
				foreach($csv as $data=>$csv1){
					$count = $count+1;
				}
			if ($count ==0 || $count ==1)
			{
						$location="Nessun luogo trovato / No founds";
						$content = array('chat_id' => $chat_id, 'text' => $location,'disable_web_page_preview'=>true);
						$telegram->sendMessage($content);
			}
			function decode_entities($text)
			{

							$text=htmlentities($text, ENT_COMPAT,'ISO-8859-1', true);
						$text= preg_replace('/&#(\d+);/me',"chr(\\1)",$text); #decimal notation
							$text= preg_replace('/&#x([a-f0-9]+);/mei',"chr(0x\\1)",$text);  #hex notation
						$text= html_entity_decode($text,ENT_COMPAT,"UTF-8"); #NOTE: UTF-8 does not work!

							return $text;
			}

			$result=0;

			$ciclo=0;
//if ($count >40) $count=40;
	for ($i=$inizio;$i<$count;$i++){

		$lat10=floatval($csv[$i][7]);
		$long10=floatval($csv[$i][8]);
		$theta = floatval($lon)-floatval($long10);
		$dist =floatval( sin(deg2rad($lat)) * sin(deg2rad($lat10)) +  cos(deg2rad($lat)) * cos(deg2rad($lat10)) * cos(deg2rad($theta)));
		$dist = floatval(acos($dist));
		$dist = floatval(rad2deg($dist));
		$miles = floatval($dist * 60 * 1.1515 * 1.609344);
	//echo $miles;

		if ($miles >=1){
			$data1 =number_format($miles, 2, '.', '');
			$data =number_format($miles, 2, '.', '')." Km";
		} else {
			$data =number_format(($miles*1000), 0, '.', '')." mt";
			$data1 =number_format(($miles*1000), 0, '.', '');
		}
		$csv[$i][100]= array("distance" => "value");

		$csv[$i][100]= $data;



		$filter=strtoupper($csv[$i][3]);

if (strpos(decode_entities($filter),strtoupper($comune)) !== false ){
	$ciclo++;
	array_push($optionf,["/".$i." ".decode_entities($csv[$i][0])]);
				$result=1;
				$homepage .="\nClicca sull'ID per dettagli: /".$i."\n";
				$homepage .="Nome: / Name:  ".decode_entities($csv[$i][0])."\n";
/*
				$homepage .="Risorsa: ".decode_entities($csv[$i][1])."\n";
				if($csv[$i][4] !=NULL) $homepage .="Indirizzo: / Address: ".decode_entities($csv[$i][4]);
				if($csv[$i][5] !=NULL)	$homepage .=", ".decode_entities($csv[$i][5]);
				$homepage .="\n";
				if($csv[$i][3] !=NULL)$homepage .="Comune: / Comune:  ".decode_entities($csv[$i][3])."\n";
				if($csv[$i][9] !=NULL)$homepage .="Web: ".decode_entities($csv[$i][9])."\n";
				if($csv[$i][10] !=NULL)	$homepage .="Email: ".decode_entities($csv[$i][10])."\n";
			//	if($csv[$i][22] !=NULL)	$homepage .="Descrizione: ".substr(decode_entities($csv[$i][22]), 0, 400)."..[....]\n";
				if($csv[$i][11] !=NULL)	$homepage .="Tel: ".decode_entities($csv[$i][11])."\n";
				if($csv[$i][14] !=NULL)	$homepage .="Servizi: / Services: ".decode_entities($csv[$i][14])."\n";
				if($csv[$i][15] !=NULL)	$homepage .="Attrezzature: / Various: ".decode_entities($csv[$i][15])."\n";
				if($csv[$i][16] !=NULL)	$homepage .="Foto1: / Photo1: ".decode_entities($csv[$i][16])."\n";
				if($csv[$i][17] !=NULL) $homepage .="(realizzata da: / BY: ".decode_entities($csv[$i][17]).")\n";
				if($csv[$i][18] !=NULL)	$homepage .="Foto2: / Photo2: ".decode_entities($csv[$i][18])."\n";
				if($csv[$i][19] !=NULL) $homepage .="(realizzata da: / BY: ".decode_entities($csv[$i][19]).")\n";
				if($csv[$i][7] !=NULL){
					$homepage .="Dista: ".$csv[$i][100]."\n";
					$homepage .="Mappa:\n";
					$homepage .= "http://www.openstreetmap.org/?mlat=".$csv[$i][7]."&mlon=".$csv[$i][8]."#map=19/".$csv[$i][7]."/".$csv[$i][8];
				}
*/
				$homepage .="____________\n";
				}
					if ($ciclo >300) {
						$location ="Troppi risultati per essere visualizzati (più di 300). Restringi la ricerca";
						$location .="\nToo many results to be displayed (more than 300). Narrow Search";
						$content = array('chat_id' => $chat_id, 'text' => $location,'disable_web_page_preview'=>true);
						$telegram->sendMessage($content);

						 exit;
					}
				}

				$chunks = str_split($homepage, self::MAX_LENGTH);
				foreach($chunks as $chunk) {
				$content = array('chat_id' => $chat_id, 'text' => $chunk,'disable_web_page_preview'=>true);
				$telegram->sendMessage($content);

			}
			$keyb = $telegram->buildKeyBoard($optionf, $onetime=false);
			$content = array('chat_id' => $chat_id, 'reply_markup' => $keyb, 'text' => "[Clicca su ID per dettagli / Click on ID for details]");
			$telegram->sendMessage($content);
	//		$this->create_keyboard_temp($telegram,$chat_id);

		//exit;
	}
	function location_manager_inline($inline_query,$telegram,$user_id,$chat_id,$location)
		{

		$optionf=array([]);
		$trovate=0;
		$res=[];
		$id="";
		$i=0;
		$idx=[];
		$distanza=[];
		$id3="";
		$id1="";
		$inline="";
	$id=$inline_query['id'];
	$lat=$inline_query["location"]['latitude'];
	$lon=$inline_query["location"]['longitude'];
				$r=1;
			//	$response=$telegram->getData();
				$response=str_replace(" ","%20",$response);

					$reply="http://nominatim.openstreetmap.org/reverse?email=piersoft2@gmail.com&format=json&lat=".$lat."&lon=".$lon."&zoom=18&addressdetails=1";
					$json_string = file_get_contents($reply);
					$parsed_json = json_decode($json_string);
					//var_dump($parsed_json);
					$comune="";
					$temp_c1 =$parsed_json->{'display_name'};

					if ($parsed_json->{'address'}->{'town'}) {
						$temp_c1 .="\nCittà: ".$parsed_json->{'address'}->{'town'};
						$comune =$parsed_json->{'address'}->{'town'};
					}else 	$comune =$parsed_json->{'address'}->{'city'};

					if ($parsed_json->{'address'}->{'village'}) $comune =$parsed_json->{'address'}->{'village'};

				  $alert="";
				//	echo $comune;
					$urlgd="db/luoghi.csv";
					$inizio=0;
					$homepage ="";
					$csv = array_map('str_getcsv',file($urlgd));
					$count = 0;
					foreach($csv as $data=>$csv1){
						$count = $count+1;
					}
			//		$id3 = $telegram->InlineQueryResultArticle($id."/0", "Nessun luogo censito\nNo place in ViaggiareinPuglia's database", array('message_text'=>"Nessun luogo censito\nNo place in ViaggiareinPuglia's database",'disable_web_page_preview'=>true),"http://www.piersoft.it/viaggiareinpugliabot/puglia.png");

			//		$res= array($id3);
			//		$content=array('inline_query_id'=>$inline_query['id'],'results' =>json_encode($res));
			//		$telegram->answerInlineQuery($content);

				if ($count ==0)
				{

					$id3 = $telegram->InlineQueryResultArticle($id."/0", "Nessun luogo censito\nNo place in ViaggiareinPuglia's database", array('message_text'=>"Nessun luogo censito\nNo place in ViaggiareinPuglia's database",'disable_web_page_preview'=>true),"http://www.piersoft.it/viaggiareinpugliabot/puglia.png");

					$res= array($id3);
					$content=array('inline_query_id'=>$inline_query['id'],'results' =>json_encode($res));
					$telegram->answerInlineQuery($content);
					$this->create_keyboard($telegram,$chat_id);
					exit;
				}
				function decode_entities($text)
				{

								$text=htmlentities($text, ENT_COMPAT,'ISO-8859-1', true);
							$text= preg_replace('/&#(\d+);/me',"chr(\\1)",$text); #decimal notation
								$text= preg_replace('/&#x([a-f0-9]+);/mei',"chr(0x\\1)",$text);  #hex notation
							$text= html_entity_decode($text,ENT_COMPAT,"UTF-8"); #NOTE: UTF-8 does not work!

								return $text;
				}


				$result=0;

				$ciclo=0;
	//if ($count >40) $count=40;
$parola="";
		for ($i=$inizio;$i<$count;$i++)


	{
		$filter=strtoupper($csv[$i][3]);

		if (strpos(decode_entities($filter),strtoupper($comune)) !== false ){
			$ciclo++;
			$parola="trovato";

		// $id3 = $telegram->InlineQueryResultArticle($id."/0", $comune." ".$filter, array('message_text'=>"Nessun luogo censito\nNo place in ViaggiareinPuglia's database",'disable_web_page_preview'=>true),"http://www.piersoft.it/viaggiareinpugliabot/puglia.png");
		//  $rest= array($id3);
		//  $content=array('inline_query_id'=>$inline_query['id'],'results' =>json_encode($rest));
		//  $telegram->answerInlineQuery($content);

if ($ciclo<50){


		$idx[$i] = $telegram->InlineQueryResultArticle($id."/".$i, $csv[$i][0], array('message_text'=>"/".$i,'disable_web_page_preview'=>true),"http://www.piersoft.it/viaggiareinpugliabot/puglia.png");
		array_push($res,$idx[$i]);
}
		}



	}

	 $id3 = $telegram->InlineQueryResultArticle($id."/0", $ciclo." ".$comune." ".$parola, array('message_text'=>"Nessun luogo censito\nNo place in ViaggiareinPuglia's database",'disable_web_page_preview'=>true),"http://www.piersoft.it/viaggiareinpugliabot/puglia.png");
	  $rest= array($id3);
	  $content=array('inline_query_id'=>$inline_query['id'],'results' =>json_encode($res));
	  $telegram->answerInlineQuery($content);

		//	if ($ciclo !==0){
	//$content = array('chat_id' => 69668132, 'text' => "count non zero",'disable_web_page_preview'=>true);
	//$telegram->sendMessage($content);
	//$content=array('inline_query_id'=>$inline_query['id'],'results' =>json_encode($res));
	//$telegram->answerInlineQuery($content);
			//					}


				}



}

?>
