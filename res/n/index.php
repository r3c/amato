<table cellspacing=0 cellpadding=3 width='100%' border=2><tr align=center>
<?php
$dossier = opendir("./");

$tr = 0; $preg = "";
while ($entree = readdir($dossier)) {
	if ($entree != "." && $entree != "..")
	{
		list($nom,$ext) = explode(".",$entree);
		if ($ext == "gif") {
		
		//echo "<img src='$nom.$ext'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
		echo "<td><a href=\\\"javascript:;\\\" onClick=\\\"opsm('$nom');\\\"><img SRC=\\\"\$gfx/s2/$nom.gif\\\" border=0><br>#$nom#</a></td>\n";
		$preg .= "|$nom";
		$tr++;
		
		if ($tr == 7) { echo "</tr><tr align=center valign='top'>\n"; $tr = 0; }
		}
	}
}

closedir($dossier);
?>

</tr></table>
<br><br>


<?php

echo $preg;
?>
