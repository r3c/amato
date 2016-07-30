<?php

defined ('CHARSET') or die ('must be included through "bench.php" file');

ini_set('error_reporting', E_ALL & ~E_NOTICE & ~E_DEPRECATED);

function formatRegexp($message,$nick='Zephyr',$limit=100)
{	
	global $skinP,$cook_login,$connexion,$_DROITS_cat,$gfx, $debut;

	$message = " $message ";
	
	$message=preg_replace("/(\[media\])(.*?)(\[\/media\])/i", "<embed src=\"\\2\" autostart=\"false\" width=\"200\" height=\"120\">", $message);
	$message=preg_replace("/(!slap )([^\r\n]+)/ism", "$1$2<br /><font color='#990099'>• $nick slaps \\2 around a bit with a large trout !</font><br />", $message);
	
	$message = preg_replace("/(\r\n|\n)/"," <br /> ",$message);
	$message=preg_replace("/([^a-zA-Z0-9])[.]\/([0-9]+)/", "$1<a href='javascript:;' onclick='getPost(event,".intval($GLOBALS[s]).",$2);'>./$2</a>", $message);
		

	if (empty($cook_login))
		$message=preg_replace("/([[:alnum:]]+[[:alnum:]\._-]*)@([[:alnum:]_-]+[[:alnum:]\._-]+)/", "$1##antispam##$2",$message);

//	if (!eregi("\[nosmile\]",$message) ) $message = smileys($message, $limit);


	if (preg_match("/(\[.*?\])/ism",$message))
	{
		$message=preg_replace_callback("/(\[pre\])(.*?)(\[\/pre\])/i", "pre", $message);
		
		$message=preg_replace("/(\[modo\])(.*?)(\[\/modo\])/i", "<div class='modo'>\\2</div>", $message);
	
		$message=preg_replace("/(\[google\])(.[^\]\[]*?)(\[\/google\])/i","<b><font color='#003366'>G</font><font color='#cc3333'>o</font><font color='#ffcc00'>o</font><font color='#003366'>g</font><font color='#66cc00'>l</font><font color='#cc3300'>e</font> :</b> <a href='http://www.google.fr/search?hl=fr&ie=UTF-8&oe=UTF-8&q=\\2&meta=lr%3Dlang_fr' target='_blank'>\\2</a>",$message);
	
		$message=preg_replace("/(\[smiley)(=([1-5]))*(\])(.*?)(\[\/smiley\])/is", "<table border='0' cellpadding='0' cellspacing='0' style='margin: 3px;'><tr><td align='center'><img src='$GLOBALS[gfx]/pancarte/h.gif' alt='' style='vertical-align: bottom;'/></td></tr><tr><td class='panneau' align='center'>\\5</td></tr><tr><td align=center><img src='$GLOBALS[gfx]/pancarte/b\\3.gif' alt='' /></td></tr></table>", $message);

		$message=preg_replace("/(\[tiwiki\])(.*?)(\[\/tiwiki\])/i","<a href='http://www.tiwiki.org/\\2' target='_blank'>\\2 <img src='$gfx/external.png' alt=\"TiWiki:\\2\" /></a>",$message);
		
		$message = preg_replace ("/(\[)([0-9]|1[0-5])(\])/i", "<span class='color\\2'>", $message);
		$message = preg_replace ("/(\[\/)([0-9]|1[0-5])(\])/i", "</span>", $message);

		$message=preg_replace("/(\[url\])(http|https|ftp|irc)(.[^\"\'\<\>\]\[]*?)(\[\/url\])/i", "<a href='$2$3' target='_blank'>$2$3</a>", $message);
		$message=preg_replace("/(\[url\])(www)(.[^\"\'\<\>\]\[]*?)(\[\/url\])/i", "<a href='http://$2$3' target='_blank'>$2$3</a>", $message);
		
		$message=preg_replace("/(\[url=(http|https|ftp|irc|#)(.[^\"\'\<\>\]\[]*?)\])(.*?)(\[\/url\])/i", "<a target='_blank' href=\"\\2\\3\">\\4</a>", $message);
		
		$message=preg_replace("/(\[urli=(http|https|ftp|irc|#)(.[^\"\'\<\>\]\[]*?)\])(.*?)(\[\/urli\])/i", "<a href=\"$2$3\">$4</a>", $message);
		
		$message=preg_replace("/(\[color=)(#)?([a-zA-Z0-9]{6})(\])(.*?)(\[\/color\])/i", "<span style='color:\\2\\3'>\\5</span>", $message);
		
//		if ($_DROITS_cat != 0) $message= preg_replace_callback("/(\[hide\])(.*?)(\[\/hide\])/is", "hide", $message);
	
		$message=preg_replace("/(\[yncMd:159\])(.*?)(\[\/yncMd:159\])/i", " <div style='border: 1px solid #003366; border-right: 0; border-top: 0; border-bottom: 0;  margin-left: 10px; padding-left: 5px'>\\2</div> ", $message);

		$message=preg_replace("/(\[email\])(.*?)(\[\/email\])/i", " <a href='mailto:\\2'>\\2</a>", $message);
		$message=preg_replace("/\[serif\](.*?)\[\/serif\]/i", " <span style='font-family: Garamond; font-size: 11pt'>\\1</span>", $message);

		$message = preg_replace("/(\[)(\/?)(quote\])/ism","$1$2cite]",$message);
	
		$ci = 0;
		while (ereg("(\[cite\])(.*)(\[\/cite\])",$message) && $ci < 8) 
		{
			$message=preg_replace("/(\[cite\])(.*?)(\[\/cite\])/ism", "<blockquote class='cite' > \\2 </blockquote>", $message);
			$ci++;
		}

		$ci = 0;
		while (ereg("\[box=(.*)\[\/box\]",$message) && $ci < 3) 
		{
			$message=preg_replace("/\[box=([^\]]+)\](.*?)\[\/box\]/ism", "<div class='box'><div class='t' onclick='box(this)'><img src='$skinP/application_put.png'/> \\1</div><div class='c'> \\2</div></div>", $message);
			$ci++;
		}

		$message=preg_replace("/(\[spoiler\])(.*?)(\[\/spoiler\])/i", "<span class='spoiler' onmouseover=\"this.className = 'spoiler2';\" onmouseout=\"this.className = 'spoiler';\" >\\2</span>", $message);
		
		$message=preg_replace("/(\[nosmile\])/i", "", $message);
		$message=preg_replace("/(\[noedit\])/i", "", $message);
		
		$message=preg_replace_callback("/(\[font=([0-9]+)\])(.*?)(\[\/font\])/i", "font", $message);

		$message=preg_replace_callback("/(\[table\])(.*?)(\[\/table\])/is", "table", $message);
		$message=preg_replace_callback("/(\[itable(=[0-9]{0,3})?\])(.*?)(\[\/itable\])/is", "tablei", $message);
	
		$message=preg_replace("/(\[)(\/)?(b|i|u|s|sup|sub|li|ul|code|hr|em)(\])(.*?)/i", "<$2$3>", $message);
		
		$message=preg_replace("/(\[center\])(.*?)(\[\/center\])/is", "</span><center><span class=centre>\\2</span></center><span class=centre>", $message);
		
		$message=preg_replace("/(\[)(left|right)(\])(.*?)(\[\/)(left|right)(\])/i", "<div style='text-align: \\2'>\\4</div>", $message);
		
		$message=preg_replace("/(\[sp\])(.*?)(\[\/sp\])/","<pre style='padding: 0; margin:0'>$2</pre>", $message);
		
		$message=preg_replace("/(\[img\])(http:\/\/|\/)(.[^':]*?)(\[\/img\])/i", " <img src='$2$3' alt='img'/> ", $message,$limit);
		$message=preg_replace_callback("/(\[img=)([0-9]+)\](http:\/\/)(.[^':]*?)(\[\/img\])/i", "img", $message);
		
		$message=preg_replace("/(\[flash\])(www|http)(.*?)(\[\/flash\])/i", "<object classid=\"clsid:D27CDB6E-AE6D-11cf-96B8-444553540000\" codebase=\"http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=5,0,0,0\" width=\"550\" height=\"400\"><param name=movie value=\"\\2\\3\"><param name=quality value=high><embed src=\"\\2\\3\"  width=\"550\" height=\"400\" quality=high pluginspage=\"http://www.macromedia.com/shockwave/download/index.cgi?P1_Prod_Version=ShockwaveFlash\" type=\"application/x-shockwave-flash\"></embed></object>", $message);
	
		$message=preg_replace("/(\[flash=)([0-9]+)(,)([0-9]+)(\])(www|http)(.*?)(\[\/flash\])/i", "<object classid=\"clsid: D27CDB6E-AE6D-11cf-96B8-444553540000\" codebase=\"http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=5,0,0,0\" width=\"\\2\" height=\"\\4\"><param name=movie value=\"\\6\\7\"><param name=quality value=high><embed src=\"\\6\\7\"  width=\"\\2\" height=\"\\4\" quality=high pluginspage=\"http://www.macromedia.com/shockwave/download/index.cgi?P1_Prod_Version=ShockwaveFlash\" type=\"application/x-shockwave-flash\"></embed></object>", $message);

		$message=preg_replace("/(\[png=)([0-9]+)(,)([0-9]+)(\])(http:\/\/)(.[^':]*?)(\[\/png\])/i", "<span style=\"width:\\2px;height:\\4px; filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src='\\6\\7');\"><img style=\"filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src='\\6\\7');filter:progid:DXImageTransform.Microsoft.Alpha(opacity=0);\" src='\\6\\7' width='\\2' height='\\4' border='0' /></span>", $message);

		################################################## Sondage
		if (ereg("(\[sondage=)([0-9]*)(\])",$message,$sT))
		{
			$s = $sT[2];
			include("sond.php");
			
			$message=preg_replace("/(\[sondage=(.*?)\])/i", $sondINC, $message,1);
		}
	
		################################################## Sources
		$message=preg_replace("/(\[source=)([0-9]+)(\])/i", "<br /><a href=\"javascript:popup('source.php?s=\\2','800','600');\"><img src='$GLOBALS[gfx]/source.gif' alt='' align=middle> Source $source[type]</a>", $message);

//		$message=preg_replace_callback("/(\[sourcei=)([0-9]+)(\])/i", "source", $message);
	}
	
	//no ubb code needed	
	$message=preg_replace("/( |>)(www\.)(.[^\"\'\<\>\]\[]*?)( |<|\r)/ism", "\\1 <a target=\"_blank\" href=\"http://www.\\3\">www.\\3</a> \\4", $message);
	$message=preg_replace("/( |>)(http:\/\/)(.[^\"\'\<\>\]\[]*?)( |<|\r)/ism", "\\1 <a target=\"_blank\" href=\"http://\\3\">http://\\3</a> \\4", $message);
	
	//unicode
	$message = preg_replace("/&amp;#([a-z0-9]+);/s", "&#\\1;", $message );
	return $message;
}

function sp($s)
{
	$img = "sp/img/$s[1].img";
	if(file_exists($img))
	{
		return "<img src='/$img' alt='$s[1]' />";
	}
	else return $s[0];
}

function font($s)
{
	$p = intval($s[2]);
	
	if ($p < 50) $p=50;
	if ($p > 300) $p=300;
	
	return "<span style='font-size: $p%;line-height:100%'>$s[3]</span>";
}


function img($s)
{
	$p = intval($s[2]);
	
	if ($p < 20) $p=20;
	if ($p > 200) $p=200;

	$p = round($p/100,2);
	return "<a href='$s[3]$s[4]' target='_blank'><img src='$s[3]$s[4]' onload='this.width*=$p;this.onload=null' alt='img'/></a>";
}

function table($s)
{
	$p = str_replace("[|]", "</td><td>", $s[2]);
	$p = str_replace("[-]","</td></tr><tr valign='top'><td>",$p);

	return str_replace("<td> <br /> ", "<td>", "<table cellspacing='0' cellpadding='2' class='ymltab'><tr valign='top'><td>$p</td></tr></table>");
}

function tablei($s)
{
	$p = intval($s[2]);

	if ($p != 0) $width = " width= '$p%' ";
	
	$p = str_replace("[|]", "</td><td>", $s[3]);
	$p = str_replace("[-]","</td></tr><tr valign='top'><td>",$p);

	return "<table cellspacing='0' cellpadding='2' border='0'$width><tr valign='top'><td>$p</td></tr></table>";
}



function pre($s)
{
	$s[2] = preg_replace("/<br \/> /i","\n","<pre><div class='pre'>$s[2]</div></pre>");
	$s[2] = str_replace("[","&"."#091;",$s[2]);
	$s[2] = str_replace("]","&"."#093;",$s[2]);
	$s[2] = str_replace(")","&"."#041;",$s[2]);
	
	return $s[2];
}

?>
