<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
    <title>Code on goal [TEST]</title>
    <link rel="stylesheet" type="text/css" href="style.css" media="screen">

<!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/> -->
<link rel="stylesheet" href="animate.min.css"/>

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

function degreeToRad(angle) {
  return angle * (Math.PI / 180);
}

function calc_end_pos(begin_pos, len, angle) {
  var end_x = Math.floor(begin_pos.x + len * Math.cos(degreeToRad(angle)));
  var end_y = Math.floor(begin_pos.y + len * Math.sin(degreeToRad(angle)));
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

function draw_ball(ctx, pos) {
  ctx.beginPath();
  var r = 5;
  ctx.arc(pos.x, pos.y, r, 0, 2 * Math.PI);
  ctx.fillStyle = "#ffffff";
  ctx.fill();
  ctx.stroke();
}

function erase_ball(ctx, pos) {
  var r = 5;
  ctx.clearRect(pos.x - (r * 1.2), pos.y - (r * 1.2), (r * 2.5), (r * 2.5));
}

var player_size_r = 10;

function draw_player(ctx, pos, angle, color, has_ball, alpha = 1.0) {
  ctx.save();
  let r = player_size_r;
  let ball_pos = null;
  ctx.translate(pos.x, pos.y);
  ctx.rotate(degreeToRad(angle));
  ctx.globalAlpha = alpha;
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
  ctx.stroke();
  ctx.restore();
}

function erase_player(ctx, pos, angle, color, has_ball) {
  ctx.save();
  ctx.translate(pos.x, pos.y);
  ctx.rotate(degreeToRad(angle));
  let r = player_size_r;
  ctx.clearRect(0 - (r * 1.2), 0 - (r * 1.8), (r * 3.5), (r * 3.6));
  ctx.restore();
}

function move_player(ctx, begin_pos, end_pos, angle, color, has_ball) {
  return new Promise((resolve, reject) => {
    let pos = begin_pos;
    let max_steps = (Math.abs(end_pos.x - pos.x) > Math.abs(end_pos.y - pos.y))
        ? Math.abs(end_pos.x - pos.x) : Math.abs(end_pos.y - pos.y);
    let step_x = (end_pos.x - pos.x) / max_steps;
    let step_y = (end_pos.y - pos.y) / max_steps;
    let id = setInterval(() => {
      moved = false;
      erase_player(ctx, pos, angle, color, has_ball);
      if (Math.floor(pos.x) != end_pos.x) {
        pos.x += step_x;
        moved = true;
      }
      if (Math.floor(pos.y) != end_pos.y) {
        pos.y += step_y;
        moved = true;
      }
      draw_player(ctx, pos, angle, color, has_ball);
      if (!moved) {
        clearInterval(id);
        resolve();
      }
    }, 3);
  });
}

function rotate_player(ctx, pos, begin_angle, end_angle, color, has_ball) {
  return new Promise((resolve, reject) => {
    let angle = begin_angle;
    let id = setInterval(() => {
      if (angle == end_angle) {
        clearInterval(id);
        resolve();
      } else {
        erase_player(ctx, pos, angle, color, has_ball);
        if (angle > end_angle) {
          --angle;
        } else if (angle < end_angle) {
          ++angle;
        }
        draw_player(ctx, pos, angle, color, has_ball);
      }
    }, 3);
  });
}

function shoot_player(ctx, begin_pos, end_pos, angle, color) {
  return new Promise((resolve, reject) => {
    erase_player(ctx, begin_pos, angle, color, true);
    draw_player(ctx, begin_pos, angle, color, false);
    let pos = calc_end_pos(begin_pos, 20, angle);
    if (begin_pos.x == end_pos.x && begin_pos.y == end_pos.y) {
      draw_ball(ctx, pos);
      resolve();
      return;
    }
    let max_steps = (Math.abs(end_pos.x - pos.x) > Math.abs(end_pos.y - pos.y))
        ? Math.abs(end_pos.x - pos.x) : Math.abs(end_pos.y - pos.y);
    let step_x = (end_pos.x - pos.x) / max_steps;
    let step_y = (end_pos.y - pos.y) / max_steps;
    let id = setInterval(() => {
      moved = false;
      erase_ball(ctx, pos);
      if (Math.floor(pos.x) != end_pos.x) {
        pos.x += step_x;
        moved = true;
      }
      if (Math.floor(pos.y) != end_pos.y) {
        pos.y += step_y;
        moved = true;
      }
      draw_ball(ctx, pos);
      if (!moved) {
        clearInterval(id);
        resolve();
      }
    }, 1);
  });
}

function draw_message(ctx, message, pos, color, font, shadow = true) {
  if (shadow) {
    ctx.fillStyle = 'black';
    ctx.font = font;
    ctx.fillText(message, pos.x + 4, pos.y + 4);
  }
  ctx.fillStyle = color;
  ctx.font = font;
  ctx.fillText(message, pos.x, pos.y);
}

class Animation {
  constructor(backgroundCtx, playerCtx, messageCtx) {
    this.backgroundCtx = backgroundCtx;
    this.playerCtx = playerCtx;
    playerCtx.clearRect(0, 0, playerCtx.canvas.width, playerCtx.canvas.height);
    this.messageCtx = messageCtx;
    messageCtx.clearRect(0, 0, messageCtx.canvas.width, messageCtx.canvas.height);
    this.promise = new Promise((resolve, reject) => { resolve(); });
  }

  push(func, delay) {
    this.promise = this.promise.then(() => {
      return new Promise((resolve, reject) => {
        setTimeout(() => {
          func(this.playerCtx);
          resolve();
        }, delay);
      });
    });
  }
  
  animate_text(message, pos, color, font, animation, prefix = 'animate__') {
    this.promise = this.promise.then(() => {
      new Promise((resolve, reject) => {
        const animationName = `${prefix}${animation}`;
        draw_message(this.messageCtx, message, pos, color, font);
        const node = this.messageCtx.canvas;
        node.classList.add(`${prefix}animated`, animationName);
        function handleAnimationEnd(event) {
          event.stopPropagation();
          node.classList.remove(`${prefix}animated`, animationName);
          resolve();
        }
        node.addEventListener('animationend', handleAnimationEnd, {once: true});
      });
    });
  }

  move_player(begin_pos, end_pos, angle, color, has_ball) {
    this.promise = this.promise.then(() => {
      return move_player(this.playerCtx, begin_pos, end_pos, angle, color, has_ball);
    });
  }
  
  rotate_player(pos, begin_angle, end_angle, color, has_ball) {
    this.promise = this.promise.then(() => {
      return rotate_player(this.playerCtx, pos, begin_angle, end_angle, color, has_ball);
    });
  }

  shoot_player(begin_pos, end_pos, angle, color) {
    this.promise = this.promise.then(() => {
      return shoot_player(this.playerCtx, begin_pos, end_pos, angle, color);
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
  constructor(pos) {
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
  constructor(pos, angle, color, ball, has_ball) {
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
    if (len < 0 || len > 200) {
      throw "go(" + len.toString() + "): 'steps' is out of range [0..200]";
    }
    let pos = this.pos;
    let angle = this.angle;
    let has_ball = this.has_ball;
    let color = this.color;
    let end_pos = calc_end_pos(pos, len, this.angle);
    animation.move_player(pos, end_pos, angle, color, has_ball);
    this.pos = end_pos;
  }

  rotate(angle) {
    if (angle < -360 || angle > 360) {
      throw "rotate(" + angle.toString() + "): 'degrees' is out of range [-360..360]";
    }
    let begin_angle = this.angle;
    let end_angle = Math.floor(begin_angle + angle);
    let pos = this.pos;
    let has_ball = this.has_ball;
    let color = this.color;
    animation.rotate_player(pos, begin_angle, end_angle, color, has_ball);
    this.angle = end_angle;
  }

  shoot(power) {
    if (power < 0 || power > 5) {
      throw "shoot(" + power.toString() + "): 'power' is out of range [0..5]";
    }
    if (this.has_ball) {
      let pos = this.pos;
      let end_pos = calc_end_pos(pos, power * 100, this.angle);
      let angle = this.angle;
      let has_ball = this.has_ball;
      let color = this.color;
      animation.shoot_player(pos, end_pos, angle, color);
      ball.pos = end_pos;
      ball.draw();
      this.has_ball = false;
    }
  }
};

var player = null;
var ball = null;
var goal = null;

var editor = null;

function run() {
  var backgroundCtx = document.getElementById("layer1").getContext("2d");
  var playerCtx = document.getElementById("layer2").getContext("2d");
  var messageCtx = document.getElementById("layer3").getContext("2d");
  animation = new Animation(backgroundCtx, playerCtx, messageCtx);
  ball = new Ball(new Point(0, 0));
  let player_start_pos = new Point(100, 50);
  let player_start_angle = 0;
  player = new Player(player_start_pos, player_start_angle, "red", ball, true);
  goal = new Goal(backgroundCtx, new Point(750, 245), 40, 80);
  var imageObj = new Image();
  imageObj.src = "boisko.jpg";
  new Promise((resolve, reject) => {
    imageObj.onload = resolve;
  }).then(() => {
    backgroundCtx.drawImage(imageObj, 0, 0);
    backgroundCtx.lineWidth = 1;
    backgroundCtx.fillStyle = "#FFFFFF";
    backgroundCtx.lineStyle = "#ffff00";
    backgroundCtx.font = "10pt sans-serif";
    backgroundCtx.fillText("goal.ziv.pl", 6, 16);
    goal.draw();
    draw_player(backgroundCtx, player_start_pos, player_start_angle, "red", true, 0.3)
    var textArea = document.getElementById("myText");
    if (!editor) {
      editor = CodeMirror.fromTextArea(textArea, { lineNumbers: true, mode: "javascript" });
      editor.setSize("100%", "400px");
    }
    var errors = document.getElementById("errors");
    errors.textContent = '\u00a0';
    try {
      eval(editor.getDoc().getValue());
    } catch(e) {
      errors.textContent = "ERROR: " + e;
    }
    player.draw();
    is_goal = goal.is_ball_in(ball.pos);
    if (is_goal) {
      animation.animate_text("GOAL!!!", new Point(300, 300), "yellow", "48pt sans-serif", "tada");
    }
    var button_done = document.getElementById("buttonDone");
    button_done.style.visibility = (is_goal ? 'visible' : 'hidden');
  });
};

function go(len) {
  player.go(len);
}

function rotate(angle_degrees) {
  player.rotate(angle_degrees);
}

function shoot(power) {
  player.shoot(power);
}
</script>
</head>
<body onload="run();">
<div align="center"><h1>Code on goal</h1></div>
<div align="center"><p id="counter">Time: -:--</p></div>
<SCRIPT LANGUAGE="JAVASCRIPT">
function zeroPad(num, places) {
  var zero = places - num.toString().length + 1;
  return Array(+(zero > 0 && zero)).join("0") + num;
}
var counter = 0;
var x = setInterval(function() {
  ++counter;
  document.getElementById("counter").innerHTML = "Time: " + Math.floor(counter / 60) + ":" + zeroPad(counter % 60, 2);
}, 1000);
</SCRIPT>
<div align="center"><table width="1200" border="0"><tr><td width="800">
<div style="position: relative;">
<canvas id="layer1" width="799" height="571" style="/*position: absolute;*/ left: 0; top: 0; z-index: 0;"></canvas>
<canvas id="layer2" width="799" height="571" style="position: absolute; left: 0; top: 0; z-index: 1;"></canvas>
<canvas id="layer3" width="799" height="571" style="position: absolute; left: 0; top: 0; z-index: 2;"></canvas>
</div>
</td><td>
<div align="center">
<p>Code:</p>
<form>
<!--
go(100);
rotate(45);
go(100);
rotate(45);
go(150);
rotate(-90);
shoot(5);
-->
<p align="left"><textarea name="Text1" cols="46" rows="30" id="myText">
go(100);
rotate(60);
go(200);
rotate(60);
go(150);
rotate(60);
go(150);
rotate(180);
shoot(1);
</textarea></p>
<p align="center" style="font-size: 12px; color: red; line-height: 16px;" id="errors"/>
<p align="center"><input style="font-size: 26px; color: green;" type="button" value=" START " onClick="run()"></p>
</form>
</div>
<form action="done.php" method="post" >
<?php
$now = time();
echo '<input type="hidden" name="timestamp" value="' . $now . '"/>' . "\n";
$level = 1;
echo '<input type="hidden" name="level" value="' . $level . '"/>' . "\n";
?>
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