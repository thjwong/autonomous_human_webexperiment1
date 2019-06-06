<?php session_start(); ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<META http-equiv="content-type" CONTENT="text/html;charset=utf-8" />
<HTML>



<head>

<title>Secretary Problem</title>

<!-- The basic JavaScript secretary problem -->
<!--
-1. a press-able button
-2. display of a number read from a list
-3. 'no/next' button action to go forward in list
-4. 'yes' button action to stop
-5. display of position in list
-6. a press-able button
-7. read in a list
-8. store response
-9. record response time for each button press
-10. on-screen prompt of items shown
-11. search cost prompt with calculated sum
-12. having to observe items till the end
-13. manifest into 4 conditions
-->

<?php
$DBServer = '127.0.0.1';
$DBUser = 'spadmin';
$DBPass = '#spdb1optnt!';
$DBName = 'sp';

$conn = new mysqli($DBServer, $DBUser, $DBPass, $DBName);

//check connection
if ($conn->connect_error) {
  trigger_error('Database connection failed: '  . $conn->connect_error, E_USER_ERROR);
}
?>

<script type="text/javascript">
//commented out because participants are afraid to proceed
/*
var myEvent = window.attachEvent || window.addEventListener;
var chkevent = window.attachEvent ? 'onbeforeunload' : 'beforeunload'; /// make IE7, IE8 compitable

myEvent(chkevent, function(e) { // For >=IE7, Chrome, Firefox
    var confirmationMessage = 'Your work has not been saved, are you sure to leave the page?';  // a space
    (e || window.event).returnValue = confirmationMessage;
    return confirmationMessage;
  });
*/

function setSession(variable, value) {
  xmlhttp = new XMLHttpRequest();
  xmlhttp.open("GET", "sp_setSession.php?variable=" + variable + "&value=" + value, true);
  xmlhttp.send();
}

function functionGetNewTime()
{
  var now = new Date();
  var new_time = now.getTime();
  return new_time;
}

/**
 * Randomize array element order in-place.
 * Using Fisher-Yates shuffle algorithm.
 */
function shuffleArray(arr) {
    for (var i = arr.length - 1; i > 0; i--) {
        var j = Math.floor(Math.random() * (i + 1));
        var temp = arr[i];
        arr[i] = arr[j];
        arr[j] = temp;
    }
    return arr;
}

function getRandomSubarray(arr, size) {
    var shuffled = arr.slice(0), i = arr.length, temp, index;
    while (i--) {
        index = Math.floor((i + 1) * Math.random());
        temp = shuffled[index];
        shuffled[index] = shuffled[i];
        shuffled[i] = temp;
    }
    return shuffled.slice(0, size);
}

function hasDuplicates(array) {
    var valuesSoFar = [];
    for (var i = 0; i < array.length; ++i) {
        var value = array[i];
        if (valuesSoFar.indexOf(value) !== -1) {
            return true;
        }
        valuesSoFar.push(value);
    }
    return false;
}


/*retrieve data from mysql and inject into JavaScript as an array by PHP.*/
<?php
//generate a random array of length $seqtrials from a range of 1 to $seqtablerow
//for picking random sequence out of the sequence table(s);
$seqtablerow = 40;
$seqtrials = 3;
$randomvalues_array = range(1, $seqtablerow);
shuffle($randomvalues_array);
$randomvalues_array = array_slice($randomvalues_array, 0, $seqtrials);

if (version_compare(PHP_VERSION, '5.3.3', '>=')) {
  $sq_array = json_encode($randomvalues_array,JSON_NUMERIC_CHECK);
}
else {
  $sq_array = json_encode($randomvalues_array);
  str_replace('"','',$sq_array);
}
echo "var seq_array = " . $sq_array . ";\n";

$seqid = array_shift($randomvalues_array);

echo "var seqid = " . $seqid . ";\n";


function getDist($dist,$conn) {
  switch ($dist) {
  case '0':
    $table = 'tableuni';
    break;
  case '1';
    $table = 'tablepos';
    break;
  case '2';
    $table = 'tableneg';
    break;
  case '3';
    $table = 'tablesym';
    break;
  default:
    echo "Distribution not specified!";
  }

  //trying to select all fields, but just exclude field 'id'
  //(obsolete, 'id' does not exist now)
  $sql_getfields_stmt = $conn->prepare("SHOW fields FROM $table WHERE field not in ('id')");
  $fields_without_id = NULL;

  $sql_getfields_stmt->execute();
  $fields_result = $sql_getfields_stmt->get_result();
  //$fields_result = $conn->query($sql_getfields);

  while($fields = $fields_result->fetch_array(MYSQLI_NUM)) {
    foreach ($fields as $key=>$value){
      if($key == 0){ //get only the first item, and then concatenate it to string variable
	if($fields_without_id == NULL)
	  $fields_without_id .= $value;
	else {
	  $fields_without_id = $fields_without_id . ", " . $value;
	}
      }
    }
  }

  //$sql = "SELECT $fields_without_id FROM $table where id = $seqid;";

  //this version doesn't make use of seq_array above,
  //it instead retrieves all (40 rows) of sequences (40x40 items),
  //and then use a javascript function to randomly sample N items
  //out of the 40x40 for each trial.
  $sql_stmt = $conn->prepare("SELECT $fields_without_id FROM $table");

  // prepare() can fail because of syntax errors, missing privileges, ....
  if ( false===$sql_stmt ) {
    die('prepare() failed: ' . htmlspecialchars($conn->error));
    return false;
  }

  $sql_stmt->execute();
  $result = $sql_stmt->get_result();
  //$result = $conn->query($sql);

  if(false === $result) {
    die('execute() failed: ' . htmlspecialchars($conn->error));
    return false;
  }
  else {
    $result->data_seek(0); //point to first entry
    //$php_array = $result->fetch_array(MYSQLI_NUM);
    $php_array = array();
    while($row = $result->fetch_array(MYSQLI_NUM)) {
      $php_array = array_merge($php_array,$row);
    }
    if (version_compare(PHP_VERSION, '5.3.3', '>=')) {
      $js_array = json_encode($php_array,JSON_NUMERIC_CHECK);
    }
    else {
      foreach ($php_array as $key => $var) {
	$php_array[$key] = (int)$var;
      }
      $js_array = json_encode($php_array);
      str_replace('"','',$js_array);
    }
    return $js_array;
  }
}

echo "var javascript_array = " . getDist($_SESSION['dist'],$conn) . ";\n\n";

?>

//var thisList = javascript_array;
//var thisListCopy = thisList.slice(0);
//var last_item = thisList[thisList.length - 1];
var thisList = new Array();
var thisListOriginal = new Array();
var max_of_array;
var x;

//target-experiment phase
var stage = 1;

<?php
//must add quotations to subjectid because $_SESSION['subjid'] can be a string variable
echo "var subjectid = '" . $_SESSION['subjid'] . "';\n\n";
echo "var sessionid = " . $_SESSION['sessionid'] . ";\n\n";

echo "var lang = " . $_SESSION['lang'] . ";\n\n";
echo "var stage_total = " . $_SESSION['stagetotal'] . ";\n\n";

echo "var dist = " . $_SESSION['dist'] . ";\n\n";
echo "var trial_total = " . $_SESSION['trialtotal'] . ";\n\n";
echo "var valuepayoff = " . $_SESSION['valuepayoff'] . ";\n\n";
echo "var searchcost = " . $_SESSION['searchcost'] . ";\n\n";
echo "var ntotal = " . $_SESSION['ntotal'] . ";\n\n";
echo "var onscreen = " . $_SESSION['onscreen'] . ";\n\n";
echo "var tillend = " . $_SESSION['tillend'] . ";\n\n";

if(isset($_SESSION['dist1'])) {
  echo "var javascript_array0 = new Array();\n\n";
  echo "javascript_array0 = javascript_array;\n\n";
  echo "var dist0 = " . $_SESSION['dist'] . ";\n\n"; //placeholder for checking
  echo "var dist1 = " . $_SESSION['dist1'] . ";\n\n";
  echo "var trial_total1 = " . $_SESSION['trialtotal1'] . ";\n\n";
  echo "var valuepayoff1 = " . $_SESSION['valuepayoff1'] . ";\n\n";
  echo "var searchcost1 = " . $_SESSION['searchcost1'] . ";\n\n";
  echo "var ntotal1 = " . $_SESSION['ntotal1'] . ";\n\n";
  echo "var onscreen1 = " . $_SESSION['onscreen1'] . ";\n\n";
  echo "var tillend1 = " . $_SESSION['tillend1'] . ";\n\n";
  echo "var javascript_array1 = " . getDist($_SESSION['dist1'],$conn) . ";\n\n";
}

if(isset($_SESSION['dist2'])) {
  echo "var dist2 = " . $_SESSION['dist2'] . ";\n\n";
  echo "var trial_total2 = " . $_SESSION['trialtotal2'] . ";\n\n";
  echo "var valuepayoff2 = " . $_SESSION['valuepayoff2'] . ";\n\n";
  echo "var searchcost2 = " . $_SESSION['searchcost2'] . ";\n\n";
  echo "var ntotal2 = " . $_SESSION['ntotal2'] . ";\n\n";
  echo "var onscreen2 = " . $_SESSION['onscreen2'] . ";\n\n";
  echo "var tillend2 = " . $_SESSION['tillend2'] . ";\n\n";
  echo "var javascript_array2 = " . getDist($_SESSION['dist2'],$conn) . ";\n\n";
}

if(isset($_SESSION['dist3'])) {
  echo "var dist3 = " . $_SESSION['dist3'] . ";\n\n";
  echo "var trial_total3 = " . $_SESSION['trialtotal3'] . ";\n\n";
  echo "var valuepayoff3 = " . $_SESSION['valuepayoff3'] . ";\n\n";
  echo "var searchcost3 = " . $_SESSION['searchcost3'] . ";\n\n";
  echo "var ntotal3 = " . $_SESSION['ntotal3'] . ";\n\n";
  echo "var onscreen3 = " . $_SESSION['onscreen3'] . ";\n\n";
  echo "var tillend3 = " . $_SESSION['tillend3'] . ";\n\n";
  echo "var javascript_array3 = " . getDist($_SESSION['dist3'],$conn) . ";\n\n";
}

if(isset($_SESSION['dist4'])) {
  echo "var dist4 = " . $_SESSION['dist4'] . ";\n\n";
  echo "var trial_total4 = " . $_SESSION['trialtotal4'] . ";\n\n";
  echo "var valuepayoff4 = " . $_SESSION['valuepayoff4'] . ";\n\n";
  echo "var searchcost4 = " . $_SESSION['searchcost4'] . ";\n\n";
  echo "var ntotal4 = " . $_SESSION['ntotal4'] . ";\n\n";
  echo "var onscreen4 = " . $_SESSION['onscreen4'] . ";\n\n";
  echo "var tillend4 = " . $_SESSION['tillend4'] . ";\n\n";
  echo "var javascript_array4 = " . getDist($_SESSION['dist4'],$conn) . ";\n\n";
}

?>

//var searchcost = -10;
//var onscreen = 1;
//var tillend = 0;
//var ntotal = 10;
//var valuepayoff = 0;

var choosePressed = 0;

var score_base = 0;
var score_total = 0;

var items_shown = new Array();
var item_chosen;
var items_explored = 0;

var timedelay = 0;//.9 second

var time_start;
var time_total;
var time_stimulus_shown = 0;
var time_button_pressed;
var button_press_times = new Array();

var chosen_item_position = null;

//var trial_total = seq_array.length;
//trial_total = 10;

var trial_count = 0;
var win_count = 0;
var trial_verdict;

var example_value = 2000;

function pay_factor(valuepayoff) {
  if(valuepayoff==0)
    return 20;
  else
    //2000 is estimated by possible max. pay:
    //max of array*ntotal*.66/200000 (say, .66 probability of winning)
    return 2000;
};

var trial_start = 0;


var prompt_txt1 = "<b>(Click the [Proceed] button to continue.)</b>";

var prompt_txt1_max = "<b>When you select the highest card in a round, you will gain 100 points. </b>";

var prompt_txt1_vb = "<b>The points you will get is equal to the exact value of the number on the card you select.</b>";

var prompt_txt1_basescore = "You have a <b>base score of " + score_base + "</b>. ";

var prompt_txt1_pretext = "In these following" + trial_total + " rounds, each round will have " + ntotal + " cards drawn from the <b>same previous fishbowl</b>, ";

var prompt_txt1_costzer = "and for every time you <b>click [Next card] to see a card, you will not gain or lose any points</b>. Your score will be translated into a bonus experimental payoff on top of your experimental payment by dividing by " + pay_factor(valuepayoff) + ". For example, " + example_value + " points will be translated to an extra " + example_value/pay_factor(valuepayoff)/100 + " dollar.";

var prompt_txt1_costpos = "and for every time you <b>click [Next card] to see a card, you will spend " + searchcost + " points</b>. Your score will be translated into a bonus experimental payoff on top of your experimental payment by dividing by " + pay_factor(valuepayoff) + ". For example, " + example_value + " points will be translated to an extra " + example_value/pay_factor(valuepayoff)/100 + " dollar.";

var prompt_txt1_costneg = "and for every time you <b>click [Next card] to see a card, you will get an extra " + Math.abs(searchcost) + " points</b>. Your score will be translated into a bonus experimental payoff on top of your experimental payment by dividing " + pay_factor(valuepayoff) + ". For example, " + example_value + " points will be translated to an extra " + example_value/pay_factor(valuepayoff)/100 + " dollar.";

var prompt_txt2 = "<br/><b>Please click [Choose card] or [Next card].</b><br/>There will be a slight delay each time after you click a button, and this is normal.";

var prompt_txt3 = "<b>(This is the last card of the round.)</b>";

var prompt_txt4 = "<b>(You have selected this card.)</b>";
var prompt_txt4plus = "<b>Please go through the remaining cards <br/>by clicking the [Next card] button.</b>";

var prompt_txt5 = "<br/><b>Click the [Proceed] button for the next round</b>";

//#008080
var prompt_txt6 = "<br/><b><span style='color:#FF0000;vertical-align:-189px;'>A new round is starting! READY? Click the [Proceed] button to see the first card.</span></b>";

var prompt_txt7 = "<br/><b>Click the [Proceed] button for a final time</b>";

var prompt_txt8 = "<b>You have completed 10 rounds.</b>";


var de_prompt_txt1 = "<b>(Klicken Sie auf „Weiter“, um fortzufahren.)</b>";

var de_prompt_txt1_max = "<b>Wenn Sie die höchste Karte dieser Runde auswählen, gewinnen Sie 100 Punkte. </b>";

var de_prompt_txt1_vb = "<b>Die Punkte, die Sie erhalten, entsprechen genau dem Wert der Karte, die Sie ausgewählt haben. </b>";

var de_prompt_txt1_basescore = "Zu Beginn werden Sie <b>" + score_base + " Punkte als Basis</b> erhalten. ";

var de_prompt_txt1_pretext = "Zusätzlich werden Sie die Punktzahl der gewählten Karte jeder Runde bekommen. Es wird befolgend" + trial_total + " Runden mit jeweils " + ntotal + " Karten geben aus die <b>vorherige gleiche Glas</b>. ";

var de_prompt_txt1_costzer = "Jedes Mal, wenn Sie <b>auf „Nächste Karte“ klicken, werden Sie weder Punkte gewinnen noch verlieren.</b>. Ihre Gesamtpunktzahl wird am Ende zur Berechnung Ihrer Bonuszahlung verwendet, die Sie zusätzlich zu Ihrer Basisvergütung erhalten. Dafür wird die Gesamtpunktzahl durch " + pay_factor(valuepayoff) + " geteilt.  Wenn Sie zum Beispiel " + example_value + " Punkte erreicht haben, bekommen Sie " + example_value/pay_factor(valuepayoff)/100 + " Euro Bonus.";

var de_prompt_txt1_costpos = "Jedes Mal, wenn Sie <b>auf „nächste Karte“ klicken, verlieren Sie " + searchcost + " Punkte.</b>. Ihre Gesamtpunktzahl wird am Ende zur Berechnung Ihrer Bonuszahlung verwendet, die Sie zusätzlich zu Ihrer Basisvergütung erhalten. Dafür wird die Gesamtpunktzahl durch " + pay_factor(valuepayoff) + " geteilt.  Wenn Sie zum Beispiel " + example_value + " Punkte erreicht haben, bekommen Sie " + example_value/pay_factor(valuepayoff)/100 + " Euro Bonus.";

var de_prompt_txt1_costneg = "Jedes Mal, wenn Sie <b>auf „nächste Karte“ klicken, erhalten Sie " + Math.abs(searchcost) + " Punkte extra.</b>. Ihre Gesamtpunktzahl wird am Ende zur Berechnung Ihrer Bonuszahlung verwendet, die Sie zusätzlich zu Ihrer Basisvergütung erhalten. Dafür wird die Gesamtpunktzahl durch " + pay_factor(valuepayoff) + " geteilt.  Wenn Sie zum Beispiel " + example_value + " Punkte erreicht haben, bekommen Sie " + example_value/pay_factor(valuepayoff)/100 + " Euro Bonus.";

var de_prompt_txt2 = "<br/><b>Bitte klicken Sie auf „Karte auswählen“ oder „Nächste Karte“.</b><br/>Es gibt eine kleine Verzögerung, nachdem Sie auf eines der Felder geklickt haben, das ist normal.";

var de_prompt_txt3 = "<b>(Dies ist die letzte Karte dieser Runde.)</b>";

var de_prompt_txt4 = "<b>(Sie haben diese Karte gewählt.)</b>";
var de_prompt_txt4plus = "<b>Bitte gehen Sie noch die verbleibenden Karten durch,<br/> indem Sie auf „Nächste Karte“ klicken.</b>";

var de_prompt_txt5 = "<br/><b>Klicken Sie auf „Weiter“, um zur nächsten Runde zu gelangen.</b>";

//#008080
var de_prompt_txt6 = "<br/><b><span style='color:#FF0000;vertical-align:-189px;'>Eine neue Runde beginnt! Klicken Sie auf „Weiter“, um die erste Karte zu sehen.</span></b>";

var de_prompt_txt7 = "<br/><b>Klicken Sie auf „Weiter“.</b>";

var de_prompt_txt8 = "<b>Sie haben 10 Runde komplettiert.</b>";


/*return JavaScript data to PHP and insert into mysql using ajax.*/
function functionRecord(position,chosen)
{
  time_total = (new Date()).getTime() - time_start;
  // ajax start
  var xhr;
  //for all browsers except IE
  if (window.XMLHttpRequest) xhr = new XMLHttpRequest();
  //for IE
  else xhr = new ActiveXObject("Microsoft.XMLHTTP");
  var varHolder0 = 'stage=';
  var varHolder1 = 'dist=';
  var varHolder2 = 'valuepayoff=';
  var varHolder3 = 'searchcost=';
  var varHolder4 = 'ntotal=';
  var varHolder5 = 'onscreen=';
  var varHolder6 = 'tillend=';
  var varHolder7 = 'trialtotal=';
  var varHolder8 = 'trial=';
  var varHolder9 = 'duration=';
  var varHolder10 = 'position=';
  var varHolder11 = 'chosen=';
  var varHolder12 = 'highest=';
  var varHolder13 = 'score=';
  var varHolder14 = 'payfactor=';
  var varHolder15 = 'bonus=';
  var varHolder16 = 'items=';
  var varHolder17 = 'rtimes=';
  var url = './sp_process.php?' + varHolder0 + stage + '&' + varHolder1 + dist + '&' + varHolder2 + valuepayoff + '&' + varHolder3 + searchcost + '&' + varHolder4 + ntotal + '&' + varHolder5 + onscreen + '&' + varHolder6 + tillend + '&' + varHolder7 + trial_total + '&' + varHolder8 + trial_count + '&' + varHolder9 + time_total + '&' + varHolder10 + position + '&' + varHolder11 + chosen + '&' + varHolder12 + max_of_array + '&' + varHolder13 + score_total + '&' + varHolder14 + pay_factor(valuepayoff) + '&' + varHolder15 + (Math.ceil(score_total/pay_factor(valuepayoff)))/100 + '&' + varHolder16 + items_shown + '&' + varHolder17 + button_press_times;
  xhr.open('GET', url, false);
  xhr.onreadystatechange = function () {
    if (xhr.readyState===4 && xhr.status===200) {
      var div = document.getElementById('update');
      div.innerHTML = xhr.responseText;
    }
  }
  xhr.send();
  // ajax stop
  return false;
}

function functionUpdateTimeDelay()
{
  //alert(button_press_times);
  button_press_times.sort(function(a, b){return a-b});
  //alert(button_press_times);

  var button_press_times_median;

  if (button_press_times.length % 2 == 0) { //even
    //alert('even!');
    //alert(button_press_times.length/2);
    button_press_times_median = (button_press_times[button_press_times.length/2-1]+button_press_times[button_press_times.length/2])/2;
    //alert(button_press_times_median);
  }
  else { //odd
    //alert('odd!');
    //alert(Math.round(button_press_times.length/2)-1);
    button_press_times_median = button_press_times[Math.round(button_press_times.length/2)-1];
    //alert(button_press_times_median);
  }
  //alert(timedelay);
  timedelay = Math.round((timedelay + button_press_times_median)/2);
  //alert(timedelay);
  //////Should we set an upper limit of the timedelay?
  if(timedelay > 2400)
    timedelay = 2400;

  return false;
}

function functionProceed()
{
    if (trial_count >= trial_total) {
	if(stage >= stage_total) {
	    document.getElementById('expt_prompt').innerHTML = "";
	    document.getElementById('expt_onscreenaid').innerHTML = "";
	    document.getElementById('expt_status').innerHTML = "";
	    document.getElementById("expt_text").style.background = ''; //keyword null doesn't always work.
	    document.getElementById('expt_text').innerHTML = prompt_txt8;

	    document.getElementById('buttonProceed').style.visibility = 'hidden';
	    //document.getElementById('buttonProceed').style.display = 'none';
	    document.getElementById('buttonChoose').style.visibility = 'hidden';
	    document.getElementById('buttonNext').style.visibility = 'hidden';

	    completion = 2;
	    //set session completion to avoid restart
	    setSession('completion',2);
	    //total score, payfactor, and total bonus set to Session
	    setSession('total_score',score_total);
	    setSession('payfactor',pay_factor(valuepayoff));
	    setSession('total_bonus',(Math.ceil(score_total/pay_factor(valuepayoff)))/100 );

	    if(lang == 0) {
		document.getElementById("score").innerHTML = score_total + "=" + "$" + (Math.ceil(score_total/pay_factor(valuepayoff)))/100;
		alert('Your rounds of game are finished,\nplease proceed to answer a few questions.\nIt is now fine to leave this current page despite warning.');
	    }
	    else {
		document.getElementById("score").innerHTML = score_total + "=" + (Math.ceil(score_total/pay_factor(valuepayoff)))/100 + "€";
		alert('Ihre Runde sind beendet,\nbitten Sie in die näschte Seite ein paar Fragen ausfüllen.\ndas ist jetzt OK zum nächste Seite weiter gehen trotz der Warnung.');
	    }
	    window.location.href="sp_post.php";
	}
	else {
	    if(lang == 0)
		document.getElementById("score").innerHTML = score_total + "=" + "$" + (Math.ceil(score_total/pay_factor(valuepayoff)))/100;
	    else
		document.getElementById("score").innerHTML = score_total + "=" + (Math.ceil(score_total/pay_factor(valuepayoff)))/100 + "€";
	    switch(stage)
	    {
	    case 1:
		dist = dist1;
		trial_total = trial_total1;
		valuepayoff = valuepayoff1;
		searchcost = searchcost1;
		ntotal = ntotal1;
		onscreen = onscreen1;
		tillend = tillend1;
		javascript_array = javascript_array1;
		break;
	    case 2:
		dist = dist2;
		trial_total = trial_total2;
		valuepayoff = valuepayoff2;
		searchcost = searchcost2;
		ntotal = ntotal2;
		onscreen = onscreen2;
		tillend = tillend2;
		javascript_array = javascript_array2;
		break;
	    case 3:
		dist = dist3;
		trial_total = trial_total3;
		valuepayoff = valuepayoff3;
		searchcost = searchcost3;
		ntotal = ntotal3;
		onscreen = onscreen3;
		tillend = tillend3;
		javascript_array = javascript_array3;
		break;
	    case 4:
		dist = dist4;
		trial_total = trial_total4;
		valuepayoff = valuepayoff4;
		searchcost = searchcost4;
		ntotal = ntotal4;
		onscreen = onscreen4;
		tillend = tillend4;
		javascript_array = javascript_array4;
		break;
	    default:
		break;
	    }
	    trial_count = 0;
	    stage = ++stage;
	    document.getElementById("expt_text").style.background = ''; //keyword null doesn't always work.
	    document.getElementById('expt_itemcount').innerHTML = "";
	    document.getElementById('expt_trialcount').innerHTML = "";
	    document.getElementById('expt_onscreenaid').innerHTML = "";
	    document.getElementById('expt_result').innerHTML = "";
	    init();
	    if (lang == 0)
		alert('Next ' + trial_total + ' rounds!');
	    else
		alert('Nächste ' + trial_total + ' Runde!');
	}
    }
  
    else {
	if(trial_start == 0) { // trial has yet to start, now preparation
	    choosePressed = 0;
	    chosen_item_position = null;
	    items_shown.length = 0; //empty an existing array
	    items_explored = 0;
	    document.getElementById('expt_itemcount').innerHTML = "";
	    button_press_times.length = 0;
	    document.getElementById('expt_onscreenaid').innerHTML = "";
	    document.getElementById('expt_result').innerHTML = "";
	    if (searchcost == 0)
		document.getElementById('expt_prompt').innerHTML = "";
	    if (searchcost > 0) { //+ve search cost
		if (lang == 0)
		    document.getElementById('expt_prompt').innerHTML = "<br/><b>You are spending " + searchcost + " points each time to view a card until you make a choice.</b>";
		else
		    document.getElementById('expt_prompt').innerHTML = "<br/><b>Sie verlieren jedes Mal " + searchcost + " Punkte, wenn Sie sich eine Karte ansehen, bis Sie eine Entscheidung getroffen haben.</b>";
	    }
	    if (searchcost < 0) { //-ve search cost
		if (lang == 0)
		    document.getElementById('expt_prompt').innerHTML = "<br/><b>You are gaining " + Math.abs(searchcost) + " points each time you check out a card until you make a choice.</b>";
		else
		    document.getElementById('expt_prompt').innerHTML = "<br/><b>Sie gewinnen jedes Mal " + Math.abs(searchcost) + " Punkte, wenn Sie sich eine Karte ansehen, bis Sie eine Entscheidung getroffen haben.</b>";
	    }
	    //Original design from http://cryosphinx.deviantart.com/art/7x12-Card-Back-190783572
	    document.getElementById("expt_text").style.background = "url('card_back.jpg') no-repeat center center";
	    document.getElementById("expt_text").innerHTML = prompt_txt6;
	    if (stage == 1 && trial_count == 0) { //do only for the very first trial
		score_total = score_base;
	    }
	    if (lang == 1)
		document.getElementById("score").innerHTML = score_total + "=" + (Math.ceil(score_total/pay_factor(valuepayoff)))/100 + "€";
	    else
		document.getElementById("score").innerHTML = score_total + "=" + "$" + (Math.ceil(score_total/pay_factor(valuepayoff)))/100;
	    trial_start = 1;
	}
	else { //trial has begun
	    trial_count++;
	    if (stage == 1 && trial_count == 1) { //do only for the very first trial
		score_total = score_base - searchcost;
		if (lang == 1)
		    document.getElementById("score").innerHTML = score_total + "=" + (Math.ceil(score_total/pay_factor(valuepayoff)))/100 + "€";
		else
		    document.getElementById("score").innerHTML = score_total + "=" + "$" + (Math.ceil(score_total/pay_factor(valuepayoff)))/100;
	    }
	    else {
		score_total = score_total - searchcost;
		if (lang == 1)
		    document.getElementById("score").innerHTML = score_total + "=" + (Math.ceil(score_total/pay_factor(valuepayoff)))/100 + "€";
		else
		    document.getElementById("score").innerHTML = score_total + "=" + "$" + (Math.ceil(score_total/pay_factor(valuepayoff)))/100;
	    }
	    document.getElementById('buttonProceed').style.visibility = 'hidden';
	    //document.getElementById('buttonProceed').style.display = 'none';
	    document.getElementById('buttonChoose').style.visibility = 'visible';
	    document.getElementById('buttonNext').style.visibility = 'visible';
	    document.getElementById('expt_trialcount').innerHTML = trial_count;
	    //thisList = shuffleArray(javascript_array);
	    //Accept unless it has no duplicate
	    do {
		thisList = getRandomSubarray(javascript_array, ntotal);
	    }
	    while(hasDuplicates(thisList));
	    thisListOriginal = thisList.slice(0);
	    max_of_array = Math.max.apply(Math, thisList);
	    x = thisList.shift();
	    document.getElementById("expt_text").style.background = "url('card_front.jpg') no-repeat center center";
	    document.getElementById("expt_text").innerHTML ="<div style='font-size:750%'>" + x + "</div>";
	    items_shown.push(x);
	    time_start = functionGetNewTime();
	    items_explored = items_shown.length;
	    document.getElementById('expt_itemcount').innerHTML = items_explored;
	    time_stimulus_shown = functionGetNewTime();
	    document.getElementById('expt_prompt').innerHTML += prompt_txt2;
	    trial_start = 0; //reset trial_start status for next preparation
	}
    }
}

function functionChoose()
{
  time_button_pressed = functionGetNewTime();
  button_press_times.push(time_button_pressed - time_stimulus_shown);
  choosePressed = 1;
  item_chosen = x;
  document.getElementById("expt_prompt").innerHTML = prompt_txt4;
  if (trial_count < trial_total && tillend == 0)
    document.getElementById("expt_prompt").innerHTML += prompt_txt5;
  if (trial_count >= trial_total && tillend == 0)
    document.getElementById("expt_prompt").innerHTML += prompt_txt7;

  if( x == max_of_array && valuepayoff == 0) {
    win_count++;
    if (lang == 0)
      trial_verdict = "<br/><div style='color:#0000FF'>You succeeded. +100 points.</div>";
    else
      trial_verdict = "<br/><div style='color:#0000FF'>Sie haben gewonnen. +100 Punkte.</div>";
    score_total += 100;
    //    document.getElementById("score").innerHTML = score_total;
  }
  else if (valuepayoff == 0) {
    if (lang == 0)
      trial_verdict = "<br/><div style='color:#9F000F'>You didn't succeed. 0 points.</div>";
    else
      trial_verdict = "<br/><div style='color:#9F000F'>Sie waren in dieser Runde nicht erfolgreich. 0 Punkte.</div>";
    score_total -= 0;
    //    document.getElementById("score").innerHTML = score_total;
  }
  else {
    if (lang == 0)
      trial_verdict = "<br/><div style='color:#006300'>You gained " + x + " points.</div>";
    else
      trial_verdict = "<br/><div style='color:#006300'>Sie erhalten " + x + " Punkte.</div>";
    score_total += x;
    //    document.getElementById("score").innerHTML = score_total;
  }

  chosen_item_position = thisListOriginal.indexOf(x);

  functionRecord(chosen_item_position,x);
  functionUpdateTimeDelay();
  //alert('Position: ' + chosen_item_position + ', Value: ' + x);
  //alert(items_shown);
  //alert(button_press_times);
  if (tillend == 0) {
    if (lang == 0) {
      if (x == max_of_array)
	document.getElementById("expt_result").innerHTML = "You have chosen this card with number: " + x + " at position " + (chosen_item_position+1) + ", and the number is the maximum for this round. " + trial_verdict + "Total " + score_total + " points = " + Math.round(score_total/pay_factor(valuepayoff)) + " cents.";
      else
	document.getElementById("expt_result").innerHTML = "You have chosen this card with number: " + x + " at position " + (chosen_item_position+1) + ", and the number is NOT the maximum for this round. " + trial_verdict + "Total " + score_total + " points = " + Math.round(score_total/pay_factor(valuepayoff)) + " cents.";

    }
    else {
      if (x == max_of_array)
	document.getElementById("expt_result").innerHTML = "Sie haben sich entschieden: " + x + " auf Position " + (chosen_item_position+1) + ", und die Nummer ist die höchste Nummer in dieser Runde. " + trial_verdict + "Insgesamt " + score_total + " Punkte = " + Math.round(score_total/pay_factor(valuepayoff)) + " Cent.";
      else
	document.getElementById("expt_result").innerHTML = "Sie haben sich entschieden: " + x + " auf Position " + (chosen_item_position+1) + ", und die Nummer ist NICHT die höchste Nummer in dieser Runde. " + trial_verdict + "Insgesamt " + score_total + " Punkte = " + Math.round(score_total/pay_factor(valuepayoff)) + " Cent.";
    }
    document.getElementById('buttonProceed').style.visibility = 'visible';
    //document.getElementById('buttonProceed').style.display = 'block';
    document.getElementById('buttonNext').style.visibility = 'hidden';
  }
  else { //i.e. (tillend == 1)
    if (lang == 0)
      document.getElementById("expt_result").innerHTML = "You have chosen: " + x + " at position " + (chosen_item_position+1);
    else
      document.getElementById("expt_result").innerHTML = "Sie haben sich entschieden: " + x + " auf Position " + (chosen_item_position+1);
    if (lang == 0)
      alert('Please go through the remaining cards by clicking [Next card].');
    else
      alert('Bitte gehen Sie noch die verbleibenden Karten durch,\n indem Sie auf „Nächste Karte“ klicken.');
    document.getElementById("expt_prompt").innerHTML = prompt_txt4plus;
  }
  document.getElementById('buttonChoose').style.visibility = 'hidden';
}

function functionNext()
{
  time_button_pressed = functionGetNewTime();
  if (time_stimulus_shown != 0)
    button_press_times.push(time_button_pressed - time_stimulus_shown);
  if (choosePressed == 0) {
    score_total -= searchcost;
    if (lang == 1)
      document.getElementById("score").innerHTML = score_total + "=" + (Math.ceil(score_total/pay_factor(valuepayoff)))/100 + "€";
    else
      document.getElementById("score").innerHTML = score_total + "=" + "$" + (Math.ceil(score_total/pay_factor(valuepayoff)))/100;
    ///Execute Delay before displaying the next number
    
    //document.getElementById('buttonChoose').disabled = true;
    //document.getElementById('buttonNext').disabled = true;
    setTimeout(function(){
	//code to be executed after delay of 'timedelay' seconds
	x = thisList.shift();
	document.getElementById("expt_text").innerHTML ="<div style='font-size:750%'>" + x + "</div>";
	time_stimulus_shown = functionGetNewTime();
	if (onscreen == 1) {
	  if(items_shown.length == 1) { //first item shown, showing the second item
	    if (lang == 0)
	      document.getElementById('expt_onscreenaid').innerHTML = "Shown:";
	    else 
	      document.getElementById('expt_onscreenaid').innerHTML = "Gezeigt:";
	  }
	  if (items_shown[items_shown.length - 1] == item_chosen) { //Shown item was the item chosen
	    if(items_shown.length == 1)
	      document.getElementById('expt_onscreenaid').innerHTML += " <b><span style='color:#FF00FF'>" + items_shown[items_shown.length - 1] + "</span></b>";
	    else
	      document.getElementById('expt_onscreenaid').innerHTML += ", <b><span style='color:#FF00FF'>" + items_shown[items_shown.length - 1] + "</span></b>";
	  }
	  else {
	    if(items_shown.length == 1)
	      document.getElementById('expt_onscreenaid').innerHTML += " " + items_shown[items_shown.length - 1];
	    else
	      document.getElementById('expt_onscreenaid').innerHTML += ", " + items_shown[items_shown.length - 1];
	  }
	}
	items_shown.push(x);
	items_explored = items_shown.length;
	document.getElementById('expt_itemcount').innerHTML = items_explored;
	document.getElementById('buttonChoose').disabled = false;
	document.getElementById('buttonNext').disabled = false;
	//When reaching the last item
	if( x == thisListOriginal[thisListOriginal.length-1]) {
	  document.getElementById("expt_prompt").innerHTML = prompt_txt3;
	  if (trial_count < trial_total)
	    document.getElementById("expt_prompt").innerHTML += prompt_txt5;
	  else
	    document.getElementById("expt_prompt").innerHTML += prompt_txt7;
	  if (choosePressed == 0) {

	    if( x == max_of_array && valuepayoff == 0) {
	      win_count++;
	      if (lang == 0)
		trial_verdict = "<br/><div style='color:#0000FF'>You succeeded. +100 points.</div>";
	      else
		trial_verdict = "<br/><div style='color:#0000FF'>Sie haben gewonnen. +100 Punkte.</div>";
	      score_total += 100;
	      if (lang == 1)
		document.getElementById("score").innerHTML = score_total + "=" + (Math.ceil(score_total/pay_factor(valuepayoff)))/100 + "€";
	      else
		document.getElementById("score").innerHTML = score_total + "=" + "$" + (Math.ceil(score_total/pay_factor(valuepayoff)))/100;
	    }
	    else if (valuepayoff == 0) {
	      if (lang == 0)
		trial_verdict = "<br/><div style='color:#9F000F'>You didn't succeed. 0 points.</div>";
	      else
		trial_verdict = "<br/><div style='color:#9F000F'>Sie waren in dieser Runde nicht erfolgreich. 0 Punkte.</div>";
	      score_total -= 0;
	      if (lang == 1)
		document.getElementById("score").innerHTML = score_total + "=" + (Math.ceil(score_total/pay_factor(valuepayoff)))/100 + "€";
	      else
		document.getElementById("score").innerHTML = score_total + "=" + "$" + (Math.ceil(score_total/pay_factor(valuepayoff)))/100;
	    }
	    else {
	      if (lang == 0)
		trial_verdict = "<br/><div style='color:#006300'>You gained " + x + " points.</div>";
	      else
		trial_verdict = "<br/><div style='color:#006300'>Sie erhalten " + x + " Punkte.</div>";
	      score_total += x;
	      if (lang == 1)
		document.getElementById("score").innerHTML = score_total + "=" + (Math.ceil(score_total/pay_factor(valuepayoff)))/100 + "€";
	      else
		document.getElementById("score").innerHTML = score_total + "=" + "$" + (Math.ceil(score_total/pay_factor(valuepayoff)))/100;
	    }
	    chosen_item_position = thisListOriginal.indexOf(x);
	    if (lang == 0) {
	      if (x == max_of_array)
		document.getElementById("expt_result").innerHTML = "The last card of this round has the number: " + x + " at position " + (chosen_item_position+1) + ", and the number is the maximum for this round. " + trial_verdict + "Total " + score_total + " points = " + Math.round(score_total/pay_factor(valuepayoff)) + " cents.";
	      else
		document.getElementById("expt_result").innerHTML = "The last card of this round has the number: " + x + " at position " + (chosen_item_position+1) + ", and the number is NOT the maximum for this round. " + trial_verdict + "Total " + score_total + " points = " + Math.round(score_total/pay_factor(valuepayoff)) + " cents.";
	    }
	    else {
	      if (x == max_of_array)
		document.getElementById("expt_result").innerHTML = "Die letzte Karte hat die Nummer: " + x + " auf Position " + (chosen_item_position+1) + ", und die Nummer ist die höchste Nummer in dieser Runde. " + trial_verdict + "Insgesamt " + score_total + " Punkte = " + Math.round(score_total/pay_factor(valuepayoff)) + " Cent.";
	      else
		document.getElementById("expt_result").innerHTML = "Die letzte Karte hat die Nummer: " + x + " auf Position " + (chosen_item_position+1) + ", und die Nummer ist NICHT die höchste Nummer in dieser Runde. " + trial_verdict + "Insgesamt " + score_total + " Punkte = " + Math.round(score_total/pay_factor(valuepayoff)) + " Cent.";
	    }
	    choosePressed = 1;
	    functionRecord(chosen_item_position,x);
	    functionUpdateTimeDelay();
	    //alert('Position: ' + chosen_item_position + ', Value: ' + x);
	  }
	  else { //i.e. tillend == 1 && choosePressed == 1
	    if (lang == 0) {
	      if (x == max_of_array)
		document.getElementById("expt_result").innerHTML += ", and the number is the maximum for this round. " + trial_verdict + "Total " + score_total + " points = " + Math.round(score_total/pay_factor(valuepayoff)) + " cents.";
	      else
		document.getElementById("expt_result").innerHTML += ", and the number is NOT the maximum for this round. " + trial_verdict + "Total " + score_total + " points = " + Math.round(score_total/pay_factor(valuepayoff)) + " cents.";
	    }
	    else {
	      if (x == max_of_array)
		document.getElementById("expt_result").innerHTML += ", und die Nummer ist die höchste Nummer in dieser Runde. " + trial_verdict + "Insgesamt " + score_total + " Punkte = " + Math.round(score_total/pay_factor(valuepayoff)) + " Cent.";
	      else
		document.getElementById("expt_result").innerHTML += ", und die Nummer ist NICHT die höchste Nummer in dieser Runde. " + trial_verdict + "Insgesamt " + score_total + " Punkte = " + Math.round(score_total/pay_factor(valuepayoff)) + " Cent.";
	    }
	  }
	  document.getElementById('buttonProceed').style.visibility = 'visible';
	  //document.getElementById('buttonProceed').style.display = 'block';
	  document.getElementById('buttonChoose').style.visibility = 'hidden';
	  document.getElementById('buttonNext').style.visibility = 'hidden';
	}
      },0); //timedelay is removed and replaced by 0 for button press before item chosen
  }
  //Add some annoying delays for button clicks after item has been chosen
  else if (choosePressed == 1 && x != thisListOriginal[thisListOriginal.length-1]) {
    //document.getElementById('buttonNext').style.visibility = 'hidden';
    document.getElementById('buttonNext').disabled = true;
    setTimeout(function(){
	//code to be executed after delay of 'timedelay' seconds
	x = thisList.shift();
	document.getElementById("expt_text").innerHTML ="<div style='font-size:750%'>" + x + "</div>";
	time_stimulus_shown = functionGetNewTime();
	if (onscreen == 1) {
	  if(items_shown.length == 1) { //first item shown, showing the second item
	    if (lang == 0)
	      document.getElementById('expt_onscreenaid').innerHTML = "Shown:";
	    else
	      document.getElementById('expt_onscreenaid').innerHTML = "Gezeigt:";
	  }
	  if (items_shown[items_shown.length - 1] == item_chosen) { //Shown item was the item chosen
	    if(items_shown.length == 1)
	      document.getElementById('expt_onscreenaid').innerHTML += " <b><span style='color:#FF00FF'>" + items_shown[items_shown.length - 1] + "</span></b>";
	    else
	      document.getElementById('expt_onscreenaid').innerHTML += ", <b><span style='color:#FF00FF'>" + items_shown[items_shown.length - 1] + "</span></b>";
	  }
	  else {
	    if(items_shown.length == 1)
	      document.getElementById('expt_onscreenaid').innerHTML += " " + items_shown[items_shown.length - 1];
	    else
	      document.getElementById('expt_onscreenaid').innerHTML += ", " + items_shown[items_shown.length - 1];
	  }
	}
	items_shown.push(x);
	document.getElementById('expt_itemcount').innerHTML = items_shown.length;
	//document.getElementById('buttonNext').style.visibility = 'visible';
	document.getElementById('buttonNext').disabled = false;
	//When reaching the last item
	if( x == thisListOriginal[thisListOriginal.length-1]) {
	  document.getElementById("expt_prompt").innerHTML = prompt_txt3;
	  if (trial_count < trial_total)
	    document.getElementById("expt_prompt").innerHTML += prompt_txt5;
	  else
	    document.getElementById("expt_prompt").innerHTML += prompt_txt7;
	  if (choosePressed == 0) {
	    //process will not reach here (*delete* within this if{})
	    if( x == max_of_array && valuepayoff == 0) {
	      win_count++;
	      if (lang == 0)
		trial_verdict = "<br/><div style='color:#0000FF'>You succeeded. +100 points.</div>";
	      else
		trial_verdict = "<br/><div style='color:#0000FF'>Sie haben gewonnen. +100 Punkte.</div>";
	      score_total += 100;
	      if (lang == 1)
		document.getElementById("score").innerHTML = score_total + "=" + (Math.ceil(score_total/pay_factor(valuepayoff)))/100 + "€";
	      else
		document.getElementById("score").innerHTML = score_total + "=" + "$" + (Math.ceil(score_total/pay_factor(valuepayoff)))/100;
	    }
	    else if (valuepayoff == 0) {
	      if (lang == 0)
		trial_verdict = "<br/><div style='color:#9F000F'>You didn't succeed. 0 points.</div>";
	      else
		trial_verdict = "<br/><div style='color:#9F000F'>Sie waren in dieser Runde nicht erfolgreich. 0 Punkte.</div>";
	      score_total -= 0;
	      if (lang == 1)
		document.getElementById("score").innerHTML = score_total + "=" + (Math.ceil(score_total/pay_factor(valuepayoff)))/100 + "€";
	      else
		document.getElementById("score").innerHTML = score_total + "=" + "$" + (Math.ceil(score_total/pay_factor(valuepayoff)))/100;
	    }
	    else {
	      if (lang == 0)
		trial_verdict = "<br/><div style='color:#006300'>You gained " + x + " points.</div>";
	      else
		trial_verdict = "<br/><div style='color:#006300'>Sie erhalten " + x + " Punkte.</div>";
	      score_total += x;
	      if (lang == 1)
		document.getElementById("score").innerHTML = score_total + "=" + (Math.ceil(score_total/pay_factor(valuepayoff)))/100 + "€";
	      else
		document.getElementById("score").innerHTML = score_total + "=" + "$" + (Math.ceil(score_total/pay_factor(valuepayoff)))/100;
	    }
	    items_explored = items_shown.length;
	    chosen_item_position = thisListOriginal.indexOf(x);
	    if (lang == 0) {
	      if (x == max_of_array)
		document.getElementById("expt_result").innerHTML = "The last card of this round has the number: " + x + " at position " + (chosen_item_position+1) + ", and the number is the maximum for this round. " + trial_verdict + "Total " + score_total + " points = " + Math.round(score_total/pay_factor(valuepayoff)) + " cents.";
	      else
		document.getElementById("expt_result").innerHTML = "The last card of this round has the number: " + x + " at position " + (chosen_item_position+1) + ", and the number is NOT the maximum for this round. " + trial_verdict + "Total " + score_total + " points = " + Math.round(score_total/pay_factor(valuepayoff)) + " cents.";
	    }
	    else {
	      if (x == max_of_array)
		document.getElementById("expt_result").innerHTML = "Die letzte Karte hat die Nummer: " + x + " auf Position " + (chosen_item_position+1) + ", und die Nummer ist die höchste Nummer in dieser Runde. " + trial_verdict + "Insgesamt " + score_total + " Punkte = " + Math.round(score_total/pay_factor(valuepayoff)) + " Cent.";
	      else
		document.getElementById("expt_result").innerHTML = "Die letzte Karte hat die Nummer: " + x + " auf Position " + (chosen_item_position+1) + ", und die Nummer ist NICHT die höchste Nummer in dieser Runde. " + trial_verdict + "Insgesamt " + score_total + " Punkte = " + Math.round(score_total/pay_factor(valuepayoff)) + " Cent.";
	    }
	    choosePressed = 1;
	    functionRecord(chosen_item_position,x);
	    functionUpdateTimeDelay();
	    //alert('Position: ' + chosen_item_position + ', Value: ' + x);
	  }
	  else { //i.e. tillend == 1 && choosePressed == 1
	    if (lang == 0) {
	      document.getElementById("score").innerHTML = score_total + "=" + "$" + (Math.ceil(score_total/pay_factor(valuepayoff)))/100;
	      if (x == max_of_array)
		document.getElementById("expt_result").innerHTML += ", and the number is the maximum for this round. " + trial_verdict + "Total " + score_total + " points = " + Math.round(score_total/pay_factor(valuepayoff)) + " cents.";
	      else
		document.getElementById("expt_result").innerHTML += ", and the number is NOT the maximum for this round. " + trial_verdict + "Total " + score_total + " points = " + Math.round(score_total/pay_factor(valuepayoff)) + " cents.";
	    }
	    else {
	      document.getElementById("score").innerHTML = score_total + "=" + (Math.ceil(score_total/pay_factor(valuepayoff)))/100 + "€";
	      if (x == max_of_array)
		document.getElementById("expt_result").innerHTML += ", und die Nummer ist die höchste Nummer in dieser Runde. " + trial_verdict + "Insgesamt " + score_total + " Punkte = " + Math.round(score_total/pay_factor(valuepayoff)) + " Cent.";
	      else
		document.getElementById("expt_result").innerHTML += ", und die Nummer ist NICHT die höchste Nummer in dieser Runde. " + trial_verdict + "Insgesamt " + score_total + " Punkte = " + Math.round(score_total/pay_factor(valuepayoff)) + " Cent.";
	    }
	  }

	  document.getElementById('buttonProceed').style.visibility = 'visible';
	  //document.getElementById('buttonProceed').style.display = 'block';
	  document.getElementById('buttonChoose').style.visibility = 'hidden';
	  document.getElementById('buttonNext').style.visibility = 'hidden';
	}
      },0); //timedelay is removed and replaced by 0 for button press AFTER item chosen
  }
}


function init() {

  if (lang == 1) {
    prompt_txt1 = de_prompt_txt1;
    prompt_txt1_max = de_prompt_txt1_max;
    prompt_txt1_vb = de_prompt_txt1_vb;
    prompt_txt1_basescore = de_prompt_txt1_basescore;
    prompt_txt1_costzer = de_prompt_txt1_costzer;
    prompt_txt1_costpos = de_prompt_txt1_costpos;
    prompt_txt1_costneg = de_prompt_txt1_costneg;
    prompt_txt2 = de_prompt_txt2;
    prompt_txt3 = de_prompt_txt3;
    prompt_txt4 = de_prompt_txt4;
    prompt_txt4plus = de_prompt_txt4plus;
    prompt_txt5 = de_prompt_txt5;
    prompt_txt6 = de_prompt_txt6;
    prompt_txt7 = de_prompt_txt7;
    prompt_txt8 = de_prompt_txt8;
    document.getElementById('buttonNext').innerHTML = 'Nächste Karte';
    document.getElementById('buttonChoose').innerHTML = 'Karte auswählen';
    document.getElementById('buttonProceed').innerHTML = 'Weiter';
    document.getElementById('term_card').innerHTML = 'Karte:';
    document.getElementById('term_round').innerHTML = 'Runde:';
    document.getElementById('term_of1').innerHTML = 'von';
    document.getElementById('term_of2').innerHTML = 'von';
    document.getElementById('term_score').innerHTML = 'Punktzahl';
 }

  if (valuepayoff == 0)
      document.getElementById("expt_text").innerHTML = prompt_txt1_max;
  else
      document.getElementById("expt_text").innerHTML = prompt_txt1_vb;

  if (stage == 1)
      document.getElementById("expt_text").innerHTML += prompt_txt1_basescore;

  // update ntotal and determine *same* or *different* fishbowl
  if (lang == 0) {
      prompt_txt1_pretext = "In these following" + trial_total + " rounds, each round will have " + ntotal + " cards drawn from ";
      if (stage == 1 || (stage == 2 && dist1 == dist0) || (stage == 3 && dist2 == dist1) || (stage == 4 && dist3 == dist2) || (stage == 5 && dist4 == dist3) ) //dist0==dist 
	  prompt_txt1_pretext += "the <b>SAME PREVIOUS fishbowl</b>, ";
      else
	  prompt_txt1_pretext += "a <b>DIFFERENT fishbowl</b>, ";
  }
  else { // i.e. lang==1
      de_prompt_txt1_pretext = "Zusätzlich werden Sie die Punktzahl der gewählten Karte jeder Runde bekommen. Es wird befolgend" + trial_total + " Runden mit jeweils " + ntotal + " Karten geben aus ";
      if (stage == 1 || (stage == 2 && dist1 == dist0) || (stage == 3 && dist2 == dist1) || (stage == 4 && dist3 == dist2) || (stage == 5 && dist4 == dist3) ) //dist0==dist
	  de_prompt_txt1_pretext += "die <b>VORHERIGE GLEICHE Glas</b>. ";
      else
	  de_prompt_txt1_pretext += "ein <b>VERSCHIEDEN Glas</b>. ";
      prompt_txt1_pretext = de_prompt_txt1_pretext;
  }

  document.getElementById("expt_text").innerHTML += prompt_txt1_pretext;

  if (searchcost > 0)
      document.getElementById("expt_text").innerHTML += prompt_txt1_costpos;
  else if (searchcost < 0)
      document.getElementById("expt_text").innerHTML += prompt_txt1_costneg;
  else
      document.getElementById("expt_text").innerHTML += prompt_txt1_costzer;

  document.getElementById("expt_prompt").innerHTML = prompt_txt1;
  //document.getElementById("score").innerHTML = score_total;
  //x = thisList.shift();
  //document.getElementById("expt_text").innerHTML = x;
  document.getElementById("expt_trialtotal").innerHTML = trial_total;
  document.getElementById("expt_itemtotal").innerHTML = ntotal;
  document.getElementById('buttonChoose').style.visibility = 'hidden';
  document.getElementById('buttonNext').style.visibility = 'hidden';
  //document.getElementById('buttonProceed').style.visibility = 'hidden';
}


</script>

</head>



<body>
<!-- Preload images -->
<div id="preloaded-images" style="width:1px;height:1px;left:-9999px;top:-9999px;position:absolute;visibility:hidden;overflow:hidden">
   <img src="card_back.jpg" width="1" height="1" alt="Image 01" />
   <img src="card_front.jpg" width="1" height="1" alt="Image 02" />
</div>

<DIV OnSelectStart='return false;' onselect='return false;' onmousedown='return false;' ondblclick='return false;' style="margin:0 auto;text-align:center">
<!--- The above prevents it from selecting the text (OnSelectStart for IE and Opera; onmousedown for Firefox etc.) --->

<?php
  //echo '<p>Subject ID: '. $_SESSION['subjid'] . '</p>';
  //echo '<p>Study / Trial ID: '. $_SESSION['studyid'] . '</p>';
?>

<p>
<table width=720 height=570 style="margin:0 auto;border:1px solid black;border-collapse:collapse">

<tr>
<td align=right valign=middle colspan=2 style="height:30px;border:1px solid black;border-collapse:collapse;text-align:left;font-size:9pt"><span id=term_card>card:</span>
<span id=expt_itemcount></span> <span id=term_of1>of</span> <span id=expt_itemtotal></span>
</td>

<td align=right valign=middle colspan=6 style="height: 30px;border-bottom:white solid; border-bottom-width:1px"></td>

<td align=right valign=middle colspan=2 style="height:30px;border:1px solid black;border-collapse:collapse;text-align:left;font-size:9pt"><span id=term_round>round:</span>
<span id=expt_trialcount></span> <span id=term_of2>of</span> <span id=expt_trialtotal></span>
</td>
</tr>

<tr>
<td align=center valign=middle colspan=10 style="width:360px;height:40px" id=expt_prompt></td>
</tr>

<tr>
<td align=center valign=middle colspan=10 style="width:360px;height:210px" id=expt_text></td>
</tr>

<tr align=center>
<td width=10%></td>
<td width=10%></td>
<td width=10%></td>
<td width=10%></td>
<td width=10%></td>
<td width=10%></td>
<td width=10%></td>
<td width=10%></td>
<td width=10%></td>
<td width=10%></td>
</tr>

<tr>
<td align=left valign=bottom colspan=10 style="width:360px;height:120px" id=expt_onscreenaid></td>
</tr>

<tr>
<td align=center valign=middle colspan=10 style="width: 360px" id=expt_status></td>
</tr>

<tr>
<td align=center valign=middle colspan=2 style="height:60px;border:1px solid black;border-collapse:collapse;text-align:center;vertical-align:top">
<span id=term_score>score:</span><br/>
<span style="vertical-align:-25px;" id=score></span>
</td>
<td align=center valign=middle colspan=8 style="width:360px;height:60px;font-size:75%;border-top:white solid" id=expt_result></td>
</tr>

</table>
</p>

<p>
<button onclick="functionChoose()" style="width: 200px; height: 60px" id='buttonChoose'>Choose card</button>
&nbsp;&nbsp;&nbsp;
<button onclick="functionNext()" style="width: 200px; height: 60px" id='buttonNext'>Next card</button>
</p>

<p>
<button onclick="functionProceed()" style="width: 200px; height: 60px" id='buttonProceed'>Proceed</button>
</p>



<?php
if(isset($_SESSION['completion']) && $_SESSION['completion'] >= 2)
  echo "<script type='text/javascript'>    if(lang == 0)      alert('This session is completed.');    else      alert('Diese Session ist beendet.');    document.getElementById('expt_text').innerHTML = prompt_txt8;    if(lang == 0)      document.getElementById('expt_text').innerHTML += '<br/>Thank you!</b>';    else      document.getElementById('expt_text').innerHTML += '<br/>Vielen Dank für Ihre Teilnahme!</b>';    if(subjectid==30000)      document.getElementById('expt_result').innerHTML = '<b>Survey Code: ' + sessionid + '</b>';    document.getElementById('buttonProceed').style.visibility = 'hidden';    document.getElementById('buttonChoose').style.visibility = 'hidden';    document.getElementById('buttonNext').style.visibility = 'hidden';    document.getElementById('expt_text').style.background = '';    completion = 1;</script>";
else
  echo "<script type='text/javascript'>init();</script>";
?>


</DIV>

</body>

</HTML>
