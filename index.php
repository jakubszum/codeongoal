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

class Point {
  constructor(x, y) {
    this.x = x;
    this.y = y;
  }
};

function calc_end_pos(begin_pos, len, angle) {
  var end_x = begin_pos.x + len * Math.cos(angle);
  var end_y = begin_pos.y + len * Math.sin(angle);
  return new Point(end_x, end_y);
}

function draw_line(ctx, begin_pos, end_pos, dash) {
  ctx.beginPath();
  ctx.setLineDash(dash);
  ctx.moveTo(begin_pos.x, begin_pos.y);
  ctx.lineTo(end_pos.x, end_pos.y);
  ctx.stroke();
  ctx.setLineDash([]);
}

function draw_player(ctx, pos, angle, color, has_ball) {
  ctx.save();
  var r = 10;
  ctx.translate(pos.x, pos.y);
  ctx.rotate(angle);
  //feets
  ctx.beginPath();
  ctx.arc(0 + 3*r/4, 0 - 2*r/3, r/2, 0, 2 * Math.PI);
  ctx.arc(0 + 3*r/4, 0 + 2*r/3, r/2, 0, 2 * Math.PI);
  ctx.fillStyle = "#000000";
  ctx.fill();
  if (has_ball) {
    // ball
    ctx.beginPath();
    ctx.arc(0 + 3*r/2, 0 - r, r/2, 0, 2 * Math.PI);
    ctx.fillStyle = "#ffffff";
    ctx.fill();
    ctx.stroke();
  }
  // body
  ctx.beginPath();
  ctx.ellipse(0, 0, r, r + r/2, 0, 0, 2 * Math.PI)
  ctx.fillStyle = color;
  ctx.fill();
  ctx.stroke();
  // head
  ctx.beginPath();
  ctx.arc(0, 0, r/2, 0, 2 * Math.PI);
  ctx.fillStyle = "#000000";
  ctx.fill();
  ctx.restore();
}

function draw_ball(ctx, pos) {
  ctx.beginPath();
  var r = 5;
  ctx.arc(pos.x, pos.y, r, 0, 2 * Math.PI);
  ctx.fillStyle = "#ffffff";
  ctx.fill();
  ctx.stroke();
}

function draw_message(ctx, message, pos, color, font) {
  ctx.fillStyle = color;
  ctx.font = font;
  ctx.fillText(message, pos.x, pos.y);
}

class Animation {
  constructor(ctx) {
    this.ctx = ctx;
    this.promise = new Promise((resolve, reject) => { resolve(); });
  }

  push(func, delay) {
    this.promise = this.promise.then(() => {
      return new Promise((resolve, reject) => {
        setTimeout(() => {
          func(this.ctx);
          resolve();
        }, delay);
      });
    });
  }

  draw_line(begin_pos, end_pos, dash) {
    this.push((ctx) => { draw_line(ctx, begin_pos, end_pos, dash) }, 300);
  }

  draw_player(pos, angle, color, has_ball) {
    this.push((ctx) => { draw_player(ctx, pos, angle, color, has_ball) }, 0);
  }

  draw_ball(pos) {
    this.push((ctx) => { draw_ball(ctx, pos) }, 0);
  }

  draw_message(message, pos, color, font) {
    this.push((ctx) => { draw_message(ctx, message, pos, color, font) }, 200);
  }
};

var animation = null;

class Ball {
  constructor(ctx, pos) {
    this.ctx = ctx;
    this.pos = pos;
  }

  draw() {
    let pos = this.pos;
    animation.draw_ball(pos);
  }
};

class Goal {
  constructor(ctx, pos, w, h) {
    this.ctx = ctx;
    this.pos = pos;
    this.w = w;
    this.h = h;
  }

  draw() {
    this.ctx.beginPath();
    this.ctx.strokeStyle = '#ffff00';
    for (let x = this.pos.x; x <= (this.pos.x + this.w); x += 10) {
      this.ctx.moveTo(x, this.pos.y);
      this.ctx.lineTo(x, this.pos.y + this.h);
    }
    for (let y = this.pos.y; y <= (this.pos.y + this.h); y += 10) {
      this.ctx.moveTo(this.pos.x, y);
      this.ctx.lineTo(this.pos.x + this.w, y);
    }
    this.ctx.stroke();
    this.ctx.strokeStyle = '#000000';
  }

  is_ball_in(ball_pos) {
    return ball_pos.x > this.pos.x && ball_pos.x < (this.pos.x + this.w) &&
           ball_pos.y > this.pos.y && ball_pos.y < (this.pos.y + this.h)
  }
};

class Player {
  constructor(ctx, pos, angle, color, ball, has_ball) {
    this.ctx = ctx;
    this.pos = pos;
    this.angle = angle;
    this.color = color;
    this.ball = ball;
    this.has_ball = has_ball;
    if (this.has_ball) {
      this.ball.pos = this.pos;
    }
  }

  draw() {
    let pos = this.pos;
    let angle = this.angle;
    let has_ball = this.has_ball;
    let color = this.color;
    animation.draw_player(pos, angle, color, has_ball);
  }

  go(len) {
    let pos = this.pos;
    let end_pos = calc_end_pos(pos, len, this.angle);
    animation.draw_line(pos, end_pos, [5, 10]);
    this.pos = end_pos;
    // this.draw();
  }

  rotate(angle_degrees) {
    this.angle += (angle_degrees * (Math.PI / 180));
    // this.draw();
  }

  shoot(power) {
    if (this.has_ball) {
      let pos = this.pos;
      let end_pos = calc_end_pos(pos, power * 100, this.angle);
      animation.draw_line(pos, end_pos, [3, 3]);
      ball.pos = end_pos;
      ball.draw();
      this.has_ball = false;
    }
  }
};

var player_start_pos = new Point(100, 50);
var player_start_angle = 0;

var player = null;
var ball = null;
var goal = null;

var editor = null;

function run() {
  var canvas = document.getElementById("myCanvas");
  var ctx = canvas.getContext("2d");
  animation = new Animation(ctx);
  ball = new Ball(ctx, new Point(0, 0));
  player = new Player(ctx, player_start_pos, player_start_angle, "red", ball, true);
  goal = new Goal(ctx, new Point(750, 245), 40, 80);
  var imageObj = new Image();
  imageObj.src = "boisko.jpg";
  new Promise((resolve, reject) => {
    imageObj.onload = resolve;
  }).then(() => {
    ctx.drawImage(imageObj, 0, 0);
    ctx.lineWidth = 1;
    ctx.fillStyle = "#FFFFFF";
    ctx.lineStyle = "#ffff00";
    ctx.font = "10pt sans-serif";
    ctx.fillText("goal.ziv.pl", 6, 16);
    goal.draw();
    let player2 = new Player(ctx, player_start_pos, player_start_angle, "silver", ball, false);
    player2.draw();
    var textArea = document.getElementById("myText");
    if (!editor) {
      editor = CodeMirror.fromTextArea(textArea, { lineNumbers: true, mode: "javascript" });
      editor.setSize("100%", "400px");
    }
    eval(editor.getDoc().getValue());
    player.draw();
    is_goal = goal.is_ball_in(ball.pos);
    if (is_goal) {
      animation.draw_message("GOAL!!!", new Point(300, 300), "yellow", "48pt sans-serif");
    }
    var isGoal = document.getElementById("isGoal");
    isGoal.textContent = (is_goal ? "Goal!" : "No goal");
    var button_done = document.getElementById("buttonDone");
    button_done.disabled = !is_goal;
  });
};


function go(len) {
  if (len > 200) {
    len = 200;
  }
  if (len < 0) {
    len = 0;
  }
  player.go(len);
}

function rotate(angle_degrees) {
  if (angle_degrees > 360) {
    angle_degrees = 360;
  }
  if (angle_degrees < -360) {
    angle_degrees = -360;
  }
  player.rotate(angle_degrees);
}

function shoot(power) {
  if (power > 5) {
    power = 5;
  }
  if (power < 0) {
    power = 0;
  }
  player.shoot(power);
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
rotate(45);
go(100);
rotate(45);
go(150);
rotate(-90);
shoot(5);
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