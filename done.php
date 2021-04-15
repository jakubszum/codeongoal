<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
    <title>Code on goal</title>
    <link rel="stylesheet" type="text/css" href="style.css" media="screen">
</head>
<body>
<div align="center"><table width="800" border="0"><tr><td>
<div align="center"><h1>Code on goal</h1></div>
<?php
$level = $_POST['level'];
$timestamp = $_POST['timestamp'];
$now = time();
$lasted= $now - $timestamp;
// send e-mail
$to      = 'jakub.szumacher@gmail.com';
$subject = 'CodeOnGoal';
$message = 'Level: ' . $level . ' time: ' . sprintf('%d:%02d', $lasted / 60, $lasted % 60);
$headers = 'From: master_szumi@o2.pl' . "\r\n" .
    'Reply-To: master_szumi@o2.pl' . "\r\n" .
    'X-Mailer: PHP/' . phpversion();
mail($to, $subject, $message, $headers);
?>

<script type="text/javascript" src="https://code.jquery.com/jquery-2.2.4.min.js"></script>
<SCRIPT LANGUAGE="JAVASCRIPT">  
$(document).ready(function () {
	// Initiate gifLoop for set interval
	var refresh;
	// Duration count in seconds
	const duration = 1000 * 10;
	// Giphy API defaults
	const giphy = {
		baseURL: "https://api.giphy.com/v1/gifs/",
		apiKey: "0UTRbFtkMxAplrohufYco5IY74U8hOes",
		tag: "cr7",
		type: "random",
		rating: "pg-13"
	};
	// Target gif-wrap container
	const $gif_wrap = $("#gif-wrap");
	// Giphy API URL
	let giphyURL = encodeURI(
		giphy.baseURL +
			giphy.type +
			"?api_key=" +
			giphy.apiKey +
			"&tag=" +
			giphy.tag +
			"&rating=" +
			giphy.rating
	);

	// Call Giphy API and render data
	var newGif = () => $.getJSON(giphyURL, (json) => renderGif(json.data));

	// Display Gif in gif wrap container
	var renderGif = (_giphy) => {
		console.log(_giphy);
		// Set gif as bg image
		$gif_wrap.css({
			"background-image": 'url("' + _giphy.image_original_url + '")'
		});

		// Start duration countdown
		// refreshRate();
	};

	// Call for new gif after duration
	// var refreshRate = () => {
	// 	// Reset set intervals
	// 	clearInterval(refresh);
	// 	refresh = setInterval(function() {
	// 		// Call Giphy API for new gif
	// 		newGif();
	// 	}, duration);
	// };

	// Call Giphy API for new gif
	newGif();
});
</SCRIPT>

<div align="center">
<div id="gif-wrap"></div>
<br/><br/>
<a href="index.php">Back</a><br/>
<br/><br/>
</div>
</td></tr></table></div>
</body>
</html>