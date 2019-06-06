<?php session_start(); ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" 
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<META http-equiv="content-type" CONTENT="text/html;charset=utf-8" />
<HTML>



<head>

<title>Secretary Problem</title>

<!-- The basic JavaScript secretary problem -->
<!--
Notes go here.
Frontpage with randomization mechanism.
-->

<script type="text/javascript">
<!--

<?php
//must add quotations to subjectid because $_SESSION['subjid'] can be a string variable
echo "var lang = " . $_SESSION['lang'] . ";\n";
echo "var sessionid = " . $_SESSION['sessionid'] . ";\n";
echo "var subjectid = '" . $_SESSION['subjid'] . "';\n";
echo "var total_score = " . $_SESSION['total_score'] . ";\n";
echo "var total_bonus = " . number_format($_SESSION['total_bonus'], 2, '.', '') . ";\n";

echo "var completion = " . $_SESSION['completion'] . ";\n\n";
?>

function init()
{
  if (lang == 1) {
    document.getElementById('title').innerHTML = "<b>Das Experiment ist beendet.</b>";
    document.getElementById('prompt').innerHTML = "Vielen Dank für Ihre Teilnahme!";
    document.getElementById('prompt').innerHTML += '<br/>Gesamtbonus: ' + total_bonus + '€';
  }
  else
    document.getElementById('prompt').innerHTML += '<br/>Total bonus: $' + total_bonus;

  if(subjectid=='30000')
    document.getElementById('prompt').innerHTML += '<br/><br/><br/><b>Survey Code: ' + sessionid + '</b>';
}

-->
</script>

</head>

<body>
<!-- Preload images -->
<div id="preloaded-images" style="width:1px;height:1px;left:-9999px;top:-9999px;position:absolute;visibility:hidden;overflow:hidden">
   <img src="fishbowl_cards_s.jpg" width="1" height="1" alt="Image 06" />
  <!---// www.clker.com/clipart-blank-fishbowl-2-1.html //--->
</div>

<p>
<table width=720 height=600 style="margin:0 auto;border:1px solid black;border-collapse:collapse">

<tr>
  <td id="title" align=left valign=top colspan=10 style="text-align:center;padding:24px">
<b>You have completed the experiment.</b>
</td>
</tr>

<tr>
<td align=center valign=middle >
<img src="fishbowl_cards_s.jpg" />
</td>
</tr>

<tr>
<td id="prompt" align=center valign=middle colspan=10 style="text-align:center;padding:24px">
Thank you!
</td>
</tr>

<tr>
<td align=left valign=middle colspan=10 style="width:360px;height:60px;padding:24px">

</td>
</tr>

</table>
</p>


<script type="text/javascript">
<!--
init();
-->
</script>


</body>

</HTML>