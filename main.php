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

	$text = $update["message"] ["text"];
	$chat_id = $update["message"] ["chat"]["id"];
	$user_id=$update["message"]["from"]["id"];
	$location=$update["message"]["location"];
	$reply_to_msg=$update["message"]["reply_to_message"];

	$this->shell($telegram,$text,$chat_id,$user_id,$location,$reply_to_msg);
	$db = NULL;

}

 function shell($telegram,$text,$chat_id,$user_id,$location,$reply_to_msg)
{
	date_default_timezone_set('Europe/Rome');
	$today = date("Y-m-d H:i:s");
	if (strpos($text,'@viaggiareinpugliabot') !== false) $text=str_replace("@viaggiareinpugliabot ","",$text);

	if ($text == "/start" || $text == "Informazioni") {
		$reply = "Benvenuto. Per ricercare un luogo di interesse turistico, culturale censito da ViaggiareinPuglia.it, digita il nome del Comune oppure clicca sulla graffetta (📎) e poi 'posizione' . Puoi anche ricercare per parola chiava nel titolo anteponendo il carattere ?. Verrà interrogato il DataBase openData utilizzabile con licenza IoDL2.0 presente su http://www.dataset.puglia.it/dataset/luoghi-di-interesse-turistico-culturale-naturalistico . In qualsiasi momento scrivendo /start ti ripeterò questo messaggio di benvenuto.\nQuesto bot, non ufficiale e non collegato con il marchio regionale ViaggiareinPuglia.it, è stato realizzato da @piersoft e potete migliorare il codice sorgente con licenza MIT che trovate su https://github.com/piersoft/viaggiareinpugliabot. La propria posizione viene ricercata grazie al geocoder di openStreetMap con Lic. odbl.";
		$content = array('chat_id' => $chat_id, 'text' => $reply,'disable_web_page_preview'=>true);
		$telegram->sendMessage($content);
		$log=$today. ";new chat started;" .$chat_id. "\n";
		$this->create_keyboard_temp($telegram,$chat_id);

		exit;
		}
		elseif ($text == "Città") {
			$reply = "Digita direttamente il nome del Comune.";
			$content = array('chat_id' => $chat_id, 'text' => $reply,'disable_web_page_preview'=>true);
			$telegram->sendMessage($content);
			$log=$today. ";new chat started;" .$chat_id. "\n";
			exit;
			}
			elseif ($text == "Ricerca") {
				$reply = "Scrivi la parola da cercare anteponendo il carattere ?, ad esempio: ?Chiesa Matrice";
				$content = array('chat_id' => $chat_id, 'text' => $reply,'disable_web_page_preview'=>true);
				$telegram->sendMessage($content);
				$log=$today. ";new chat started;" .$chat_id. "\n";
				exit;
			}
			elseif($location!=null)
		{

			$this->location_manager($telegram,$user_id,$chat_id,$location);
			exit;
		}

		elseif(strpos($text,'/') === false){
			$string=0;
			$img = curl_file_create('puglia.png','image/png');
			$contentp = array('chat_id' => $chat_id, 'photo' => $img);
			$telegram->sendPhoto($contentp);

			if(strpos($text,'?') !== false){
				$text=str_replace("?","",$text);
				$location="Sto cercando i luoghi aventi nel titolo: ".$text;
				$content = array('chat_id' => $chat_id, 'text' => $location,'disable_web_page_preview'=>true);
				$telegram->sendMessage($content);
				$string=1;
				sleep (1);
			}else{
				$location="Sto cercando i luoghi di interesse per località comprendente: ".$text;
				$content = array('chat_id' => $chat_id, 'text' => $location,'disable_web_page_preview'=>true);
				$telegram->sendMessage($content);
				$string=0;
				sleep (1);
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
						$location="Nessun luogo trovato";
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
}else{
	$filter=strtoupper($csv[$i][3]);
}


if (strpos(decode_entities($filter),strtoupper($text)) !== false ){
				$ciclo++;
//	if ($ciclo >40) exit;

				$result=1;
				$homepage .="\n";
				$homepage .="Nome: ".decode_entities($csv[$i][0])."\n";
				$homepage .="Risorsa: ".decode_entities($csv[$i][1])."\n";
				if($csv[$i][4] !=NULL) $homepage .="Indirizzo: ".decode_entities($csv[$i][4]);
				if($csv[$i][5] !=NULL)	$homepage .=", ".decode_entities($csv[$i][5]);
				$homepage .="\n";
				if($csv[$i][3] !=NULL)$homepage .="Comune: ".decode_entities($csv[$i][3])."\n";
				if($csv[$i][9] !=NULL)$homepage .="Web: ".decode_entities($csv[$i][9])."\n";
				if($csv[$i][10] !=NULL)	$homepage .="Email: ".decode_entities($csv[$i][10])."\n";
			//	if($csv[$i][22] !=NULL)	$homepage .="Descrizione: ".substr(decode_entities($csv[$i][22]), 0, 400)."..[....]\n";
				if($csv[$i][11] !=NULL)	$homepage .="Tel: ".decode_entities($csv[$i][11])."\n";
				if($csv[$i][14] !=NULL)	$homepage .="Servizi: ".decode_entities($csv[$i][14])."\n";
				if($csv[$i][15] !=NULL)	$homepage .="Attrezzature: ".decode_entities($csv[$i][15])."\n";
				if($csv[$i][16] !=NULL)	$homepage .="Foto1: ".decode_entities($csv[$i][16])."\n";
				if($csv[$i][17] !=NULL) $homepage .="(realizzata da: ".decode_entities($csv[$i][17]).")\n";
				if($csv[$i][18] !=NULL)	$homepage .="Foto2: ".decode_entities($csv[$i][18])."\n";
				if($csv[$i][19] !=NULL) $homepage .="(realizzata da: ".decode_entities($csv[$i][19]).")\n";
				if($csv[$i][7] !=NULL){
					$homepage .="Mappa:\n";
					$homepage .= "http://www.openstreetmap.org/?mlat=".$csv[$i][7]."&mlon=".$csv[$i][8]."#map=19/".$csv[$i][7]."/".$csv[$i][8];
				}

				$homepage .="\n____________\n";
				}
				if ($ciclo >40) {
					$location="Troppi risultati per essere visualizzati. Restringi la ricerca";
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



	}
	$this->create_keyboard_temp($telegram,$chat_id);

	}

	function create_keyboard_temp($telegram, $chat_id)
	 {
			 $option = array(["Città","Ricerca"],["Informazioni"]);
			 $keyb = $telegram->buildKeyBoard($option, $onetime=false);
			 $content = array('chat_id' => $chat_id, 'reply_markup' => $keyb, 'text' => "[Digita un Comune, una Ricerca oppure invia la tua posizione tramite la graffetta (📎)]");
			 $telegram->sendMessage($content);
	 }



function location_manager($telegram,$user_id,$chat_id,$location)
	{

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
						$location="Nessun luogo trovato";
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

				$result=1;
				$homepage .="\n";
				$homepage .="Nome: ".decode_entities($csv[$i][0])."\n";
				$homepage .="Risorsa: ".decode_entities($csv[$i][1])."\n";
				if($csv[$i][4] !=NULL) $homepage .="Indirizzo: ".decode_entities($csv[$i][4]);
				if($csv[$i][5] !=NULL)	$homepage .=", ".decode_entities($csv[$i][5]);
				$homepage .="\n";
				if($csv[$i][3] !=NULL)$homepage .="Comune: ".decode_entities($csv[$i][3])."\n";
				if($csv[$i][9] !=NULL)$homepage .="Web: ".decode_entities($csv[$i][9])."\n";
				if($csv[$i][10] !=NULL)	$homepage .="Email: ".decode_entities($csv[$i][10])."\n";
			//	if($csv[$i][22] !=NULL)	$homepage .="Descrizione: ".substr(decode_entities($csv[$i][22]), 0, 400)."..[....]\n";
				if($csv[$i][11] !=NULL)	$homepage .="Tel: ".decode_entities($csv[$i][11])."\n";
				if($csv[$i][14] !=NULL)	$homepage .="Servizi: ".decode_entities($csv[$i][14])."\n";
				if($csv[$i][15] !=NULL)	$homepage .="Attrezzature: ".decode_entities($csv[$i][15])."\n";
				if($csv[$i][16] !=NULL)	$homepage .="Foto1: ".decode_entities($csv[$i][16])."\n";
				if($csv[$i][17] !=NULL) $homepage .="(realizzata da: ".decode_entities($csv[$i][17]).")\n";
				if($csv[$i][18] !=NULL)	$homepage .="Foto2: ".decode_entities($csv[$i][18])."\n";
				if($csv[$i][19] !=NULL) $homepage .="(realizzata da: ".decode_entities($csv[$i][19]).")\n";
				if($csv[$i][7] !=NULL){
					$homepage .="Dista: ".$csv[$i][100]."\n";
					$homepage .="Mappa:\n";
					$homepage .= "http://www.openstreetmap.org/?mlat=".$csv[$i][7]."&mlon=".$csv[$i][8]."#map=19/".$csv[$i][7]."/".$csv[$i][8];
				}

				$homepage .="\n____________\n";
				}
					if ($ciclo >40) {
						$location="Troppi risultati per essere visualizzati. Restringi la ricerca";
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
			$this->create_keyboard_temp($telegram,$chat_id);

		exit;
	}


}

?>
