<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
    <title>Code on goal</title>
<style>
body {
    font: normal 13px Verdana, Arial, sans-serif;
}
</style>
<link rel="stylesheet" href="lib/codemirror.css">
<style>
.CodeMirror {
    border: 1px solid #ccc;
}
</style>
<script src="lib/codemirror.js"></script>
<script src="mode/javascript/javascript.js"></script>
<script>
function draw_line(begin_x, begin_y, len, angle, dash) {
  var c = document.getElementById("myCanvas");
  var ctx = c.getContext("2d");
  ctx.beginPath();
  ctx.setLineDash(dash);
  ctx.moveTo(begin_x, begin_y);
  var end_x = begin_x + len * Math.cos(angle);
  var end_y = begin_y + len * Math.sin(angle);
  ctx.lineTo(end_x, end_y);
  ctx.stroke();
  ctx.setLineDash([]);
  return [end_x, end_y];
}

function draw_player(begin_x, begin_y, angle) {
  var c = document.getElementById("myCanvas");
  var ctx = c.getContext("2d");
  ctx.beginPath();
  var r = 10;
  ctx.arc(begin_x, begin_y, r, 0, 2 * Math.PI);
  ctx.fillStyle = "#ff0000";
  ctx.fill();
  ctx.moveTo(begin_x, begin_y);
  var end_x = begin_x + r * Math.cos(angle);
  var end_y = begin_y + r * Math.sin(angle);
  ctx.lineTo(end_x, end_y);
  ctx.stroke();
}

function draw_ball(begin_x, begin_y) {
  var c = document.getElementById("myCanvas");
  var ctx = c.getContext("2d");
  ctx.beginPath();
  var r = 5;
  ctx.arc(begin_x, begin_y, r, 0, 2 * Math.PI);
  ctx.fillStyle = "#ffffff";
  ctx.fill();
  ctx.stroke();
}

var goal_dim = [750, 245, 40, 80];

function draw_goal() {
  var c = document.getElementById("myCanvas");
  var ctx = c.getContext("2d");
  ctx.beginPath();
  ctx.strokeStyle = '#ffff00';
  for (x = goal_dim[0]; x <= (goal_dim[0] + goal_dim[2]); x += 10) {
    ctx.moveTo(x, goal_dim[1]);
    ctx.lineTo(x, goal_dim[1] + goal_dim[3]);
  }
  for (y = goal_dim[1]; y <= (goal_dim[1] + goal_dim[3]); y += 10) {
    ctx.moveTo(goal_dim[0], y);
    ctx.lineTo(goal_dim[0] + goal_dim[2], y);
  }
  ctx.stroke();
  ctx.strokeStyle = '#000000';
}

var player_pos = [100, 100];
var player_angle = 0;
var player_has_ball = true;
var ball_pos = player_pos;

function is_goal() {
  return ball_pos[0] > goal_dim[0] && ball_pos[0] < (goal_dim[0] + goal_dim[2]) &&
         ball_pos[1] > goal_dim[1] && ball_pos[1] < (goal_dim[1] + goal_dim[3])
}

var editor = null;

function run() {
  player_pos = [100, 100];
  player_angle = 0;
  player_has_ball = true;
  ball_pos = player_pos;
  var canvas = document.getElementById("myCanvas");
  var ctx = canvas.getContext("2d");
  var imageObj = new Image();
  imageObj.src = "boisko.jpg";
  imageObj.onload = function(){
    ctx.drawImage(imageObj, 0, 0);
    ctx.lineWidth = 1;
    ctx.fillStyle = "#FFFFFF";
    ctx.lineStyle = "#ffff00";
    ctx.font = "10pt sans-serif";
    ctx.fillText("goal.ziv.pl", 6, 16);
    draw_goal();
    draw_player(player_pos[0], player_pos[1], player_angle);
    var textArea = document.getElementById("myText");
    if (!editor) {
      editor = CodeMirror.fromTextArea(textArea, { lineNumbers: true, mode: "javascript" });
      editor.setSize("100%", "400px");
    }
    eval(editor.getDoc().getValue());
    var isGoal = document.getElementById("isGoal");
    isGoal.textContent = (is_goal() ? "Goal!" : "No goal");
    var button_done = document.getElementById("buttonDone");
    button_done.disabled = !is_goal();
  };
};

function go(len) {
  if (len > 200) {
    len = 200;
  }
  if (len < 0) {
    len = 0;
  }
  if (!player_has_ball) {
    return;
  }
  player_pos = draw_line(player_pos[0], player_pos[1], len, player_angle, []);
  draw_player(player_pos[0], player_pos[1], player_angle);
}

function rotate(angle_degrees) {
  if (angle_degrees > 360) {
    angle_degrees = 360;
  }
  if (angle_degrees < -360) {
    angle_degrees = -360;
  }
  if (!player_has_ball) {
    return;
  }
  player_angle += (angle_degrees * (Math.PI / 180));
  draw_player(player_pos[0], player_pos[1], player_angle);
}

function shoot(power) {
  if (power > 5) {
    power = 5;
  }
  if (power < 0) {
    power = 0;
  }
  if (player_has_ball) {
    ball_pos = draw_line(player_pos[0], player_pos[1], power * 100, player_angle, [5, 10]);
    draw_ball(ball_pos[0], ball_pos[1]);
    player_has_ball = false;
  }
}
</script>
</head>
<body onload="run();">
<div align="center"><h1>Code on goal</h1></div>
<div align="center"><table width="1200" border="0"><tr><td width="800">
<div align="center"><canvas id="myCanvas" width="799" height="571"/></div>
</td><td>
<div align="center">
<p>Code:</p>
<form>
<p align="left"><textarea name="Text1" cols="46" rows="30" id="myText">
go(100);
rotate(90);
go(200);
rotate(-60);
go(200);
rotate(-70);
shoot(3);
</textarea></p>
<table width="80%" border="0"><tr><td>
<p align="center" style="font-size: 20px; color: black;" id="isGoal"/></td><td>
<p align="center"><input style="font-size: 22px; color: green;" type="button" value="Run" onClick="run()"></p>
</td></tr></table>
</form>
</div>
</br>
<form action="logo_done.php" method="post" >
<p align="center"><input style="font-size: 16px; color: green;" type="submit" name="submit" value="Done!" id="buttonDone"></p>
</form>
</td></tr></table></div>

<div align="center"><table width="1200" border="0"><tr><td>
<p>Available commands:</p>
<ul style="line-height: 1.8em">
<li><b>go(steps: 0..200)</b> - moves the player forward
<li><b>rotate(degrees: -360..360)</b> - rotates the player
<li><b>shoot(power: 0..5)</b> - shoot on goal
</ul>
</td></tr></table></div>
</body>
</html>