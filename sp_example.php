<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" 
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<!--
<?php session_destroy(); ?> 
<?php session_start(); ?>
-->
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
9. record response time for each button press
10. manifest into 4 conditions
-->

<?php

$_SESSION['subjid'] = $_GET['id'];
$_SESSION['trialid'] = $_GET['studyid'];

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

<?php
$seqid = 2;
$sql = "SELECT * FROM test where id = $seqid;";
$result = $conn->query($sql);
if($result === false) {
  trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
} else {
  $result->data_seek(0);
  $php_array = $result->fetch_array(MYSQLI_NUM);
  $js_array = json_encode($php_array);
  echo "var javascript_array = ". $js_array . ";\n";
}

//echo "var seqid = ". $seqid . ";\n";
?>

function functionProceed()
{
  if ( x == last_item || choosePressed == 1) alert('This trial has already ended.');
  else {
    //start clock for first item
    if( x == '[Press Next for the first item]') {
      time_start = functionGetNewTime();
    }
    time_button_pressed = functionGetNewTime();
    if (time_stimulus_shown != 0)
      button_press_times.push(time_button_pressed - time_stimulus_shown);
    x = thisList.shift();
    document.getElementById("message").innerHTML = x;
    time_stimulus_shown = functionGetNewTime();
    if( x == last_item) {
      choosePressed = 1;
      functionRecord(thisListOriginal.indexOf(x),seqid);
      alert('Position: ' + thisListOriginal.indexOf(x) + ', Value: ' + x);
    }
  }
}

var text_welcome = "Welcome to the experiment portal for sequential decision making. Please press Proceed to continue.";

var text_instruction = "In this experiment, by clicking the Next button below, you are going to see a series of 10 (/ N) numbers one at a time. Your goal is to find the highest possible number among these 10 (/ N) numbers. When you are looking at one of these numbers and think that it is probably the highest number, you can choose it by clicking the Choose button and then the trial will end altogether, however, this also means you will not have chance to see the remaining numbers in the series; or when you think the current number is not the highest, you can click the Next button to reveal the next number in the series. You cannot go back again to any number you have passed by clicking the Next button. When you have advanced to the 10th (/ Nth) item, you have to choose it regardless if it is the highest among the 10 (/ N) or not.<br />Please press Proceed to look at the example.";

var text_example = "<table>
<tr><td>&#8595;</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
<tr><td>1</td><td>2</td><td>3</td><td>4</td><td>5</td><td>6</td><td>7</td><td>8</td><td>9</td><td>10</td></tr>
</table>";

</script>

</head>



<body>

<?php
echo '<p>Subject ID: '. $_SESSION['subjid'] . '</p>';
echo '<p>Study / Trial ID: '. $_SESSION['trialid'] . '</p>';
?>

<center>
<P>
<table width=480><tr><td align=center id="expt_text">
</td></tr></table>
</p>
<p>
<div id="message"></div>
</p>
<p>
<button onclick="functionProceed()" style="width: 200px; height: 60px" >Proceed</button>
</p>
<script type="text/javascript">
document.getElementById("expt_text").innerHTML = text_example;
</script>
</center>

</body>

</HTML>
