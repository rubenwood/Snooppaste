<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>Snoop Paste</title>
		<!-- [if It IE 9]
		<script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
		<![endif-->
		<link href="style.css" rel="stylesheet" type="text/css">
	</head>
	<body>
		<header>
			<center><a href="snooppaste2.php"><h1>Snoop Paste</h1><a></center>
		</header>
		<div id="output">
		<?php			
			set_time_limit(0);
			$max_urls = isset($_POST['MaxPastes']) ? $_POST['MaxPastes'] : '1';
			$search_text = isset($_POST['TextInPastes']) ? $_POST['TextInPastes'] : '';
			$domain = 'https://pastebin.com';
			
			// Download function, download raw_url and save file, used later
			function download($fname, $dom, $raw){
				$file = fopen('pastes/' . $fname, 'w+');
				$curl = curl_init($dom . $raw);
				curl_setopt_array($curl, array(
					CURLOPT_RETURNTRANSFER => 1, 
					CURLOPT_FILE => $file,
					CURLOPT_FOLLOWLOCATION => true,
					CURLOPT_SSL_VERIFYPEER => false
				));
				$result = curl_exec($curl);
				if(!curl_exec($curl)){
					echo('<p>Something went wrong</p>');
					die('Error: ' . curl_error($curl) . ' Code: ' . curl_errno($curl));
				}
				curl_close($curl);
				echo('<p>Download complete! File stored in: ' .  $fname . '<p>');
			}
			
			function download_all($urls){
				//This function will eventualy go through the urls in the stack and download them all

				for($i = 0; $i < sizeof($urls); $i++){
					$domain = 'https://pastebin.com';
					$paste_url = str_replace('/', '', $urls[$i]); 
					$name = $paste_url . '.txt';
					$raw_url = "/raw.php?i=" . $paste_url;
					download($name, $domain, $raw_url);
				}
			}

			// Scan /archive for paste urls, contained in <a> tags
			$doc = new DOMDocument();
			@$doc->loadHTMLFile($domain . '/archive');
			$links = $doc->getElementsByTagName('a');
			foreach($links as $link){
				$href = $link->getAttribute('href');
				$url_stack[] = $href;
			}
			
			// Paste urls start at position 17 in stack, url_stack goes up to 60, but there are only 8 links we are interested in
			//in the array that would be elements 17 to 24
			echo('<p><b>TEST1:</b> ' . $url_stack[17] . '</p>');
			//echo('<p><b>TEST1.1:</b> ' . $url_stack[24] . '</p>');
			$paste_url = str_replace('/', '', $url_stack[17]); 			// Remove '/'
			$pastesWeWant = [];
			for($i = 0; $i < 8; $i++){
				$pastesWeWant[$i] = $url_stack[17+$i];
			}

			// Get raw url, this will be the url we get the text from, using raw text is easier and cleaner than searching for <textfield>
			// We could optionally use /download.php?i=URL, this essentially accomplishes the same thing, however requesting download.php is slower
			// May also be useful; http://pastebin.com/api#13
			$name = $paste_url . '.txt';
			$raw_url = "/raw.php?i=" . $paste_url;
			echo('<p><b>TEST2:</b> ' . $raw_url . '</p>');
			
			// !!! Search this raw paste, see if text is in it, if no go next paste, if yes download this paste
			$curl = curl_init($domain . $raw_url);
			curl_setopt_array($curl, array(
				CURLOPT_RETURNTRANSFER => 1, 
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_SSL_VERIFYPEER => false
			));
			$result = curl_exec($curl);
			if(!curl_exec($curl)){
				echo('<p>Something went wrong</p>');
				die('Error: ' . curl_error($curl) . ' Code: ' . curl_errno($curl));
			}
			curl_close($curl);
			echo('<p><b>TEST3:</b> ' . $result . '</p>'); 			// Print result
			// If result contains the search terms then begin download, if not go to next url
			if($search_text != ''){
				if(strpos($result, $search_text) !== false){
					echo('<p>Found search string in result, beginning download</p>');
					download($name, $domain, $raw_url);
				}else{
					echo('<p>Did NOT find string in result</p>');
				}
			}else{
				// This is just for debugging
				echo('<p>Did not search anything, downloading anyway</p>');
				//download($name, $domain, $raw_url);
				download_all($pastesWeWant);
			}
		?>
		</div>
		<!-- Options form entry -->
		<form action="snooppaste2.php" method="post" id="user_form">
			<p>Options go here!</p>
			Maximum number of pastes to crawl: <input type="text" name="MaxPastes" /><br />
			Language: <input type="" name="LangPastes" /><br />
			Text to look for in pastes: <input type="text" name="TextInPastes" /><br />
			<input type="submit" name="submit" value="Search" />
		</form>
		<div id="main_container">
			<p>Main Container TOP</p>
			<br />
			<a href="pastes/">OUTPUT DIR</a>
			<br />
			<p>Main Container BOTTOM</p>
		</div>
		<footer>
			<p>Footer is here</p>
		</footer>
	</body>
<html>