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
			<center><a href="snooppaste.php"><h1>Snoop Paste</h1><a></center>
		</header>
		<?php
			// Search pastebin for given text (content), loop throu given urls (url_stack) until max_urls is reached
			$output_file = "L:\\xampp\htdocs\snooppaste\pastes\output.txt";
			set_time_limit(0);
			$domain = 'https://pastebin.com/';
			$content = $_POST['TextInPastes'];
			$language = $_POST['LangPastes'];
			//$min_urls = $_POST['MinPastes'];
			$max_urls = isset($_POST['MaxPastes']) ? $_POST['MaxPastes'] : '1';
			$url_stack = array();
			$checked_urls = array();
			$rounds = 0;
			
			while($domain != "" && $rounds < $max_urls){
				$doc = new DOMDocument();
				// Get page source
				$doc->loadHTMLFile($domain);
				$found = false;
				
				// Search for the textarea tag and then search for the specified content within it
				foreach($doc->getElementsByTagName('textarea') as $tag){
					if(strpos($tag->nodeValue, $content)){
						print($tag->nodeValue);
						$found = true;
						break;
					}
				}
				// Add the domain to the checked urls hash
				$checked_urls[$domain] = $found;
				// Loop through each 'a' -tag on the page and add its href to the checked_urls stack
				// Links to crawl are stored in div tag with id="menu_2"
				$menu_2 = new DOMDocument();
				$menu_2->loadHTMLFile($domain);
				$menu_2->getElementsByTagName('menu_2');
				$links = $menu_2->getElementsByTagName('a');
				foreach($links as $link){
					$href = $link->getAttribute('href');
					// Ensure href contains correctly formatted url and contains this domain (pastebin)
					if(strpos($href, 'http://') !== false && strpos($href, $domain) === false){
						$href_array = explode("/", $href);
						// Keep the url stack to the max_urls to check
						// only push urls to the stack that have not been checked
						if(count($url_stack) < $max_urls && $checked_urls["http://" . $href_array[2]] === null){
							array_push($url_stack, "http://" . $href_array[2]);
						}
					}
				}
				
				// Remove duplicates from url stack
				$url_stack = array_unique($url_stack);
				$domain = $url_stack[0];
				// Remove assigned domain from url stack
				unset($url_stack[0]);
				// Reorder the url stack
				$url_stack = array_values($url_stack);
				$rounds++;
			}
			
			// Write found pastes to output file
			$found_pastes = "";
			foreach($checked_urls as $key => $value){
				if($value){
					$found_pastes .= $key . "\nNEWLINE BREAK\n";
				}
			}
			
			fwrite($output_file, $found_pastes);
		?>
		<!-- Options form entry -->
		<form action="snooppaste.php" method="post" id="user_form">
			<p>Options go here!</p>
			<!-- Minimum number of pastes to crawl: <input type="text" name="MinPastes" value="1" /> -->
			<b>(*)</b> Maximum number of pastes to crawl: <input type="text" name="MaxPastes" /><br />
			Language: <input type="text" name="LangPastes" /><br />
			Text to look for in pastes: <input type="text" name="TextInPastes" /><br />
			<input type="submit" name="submit" value="Begin Crawling" />
		</form>
		<br />
		<div id="main_container">
			<p>Main Container TOP</p>
			<br />
			<a href="pastes/output.txt">OUTPUT</a>
			<br />
			<p>Main Container BOTTOM</p>
		</div>
		<footer>
			<p>Footer is here</p>
		</footer>
	</body>
<html>