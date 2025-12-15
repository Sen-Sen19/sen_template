<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Sen Redirector Physics Prototype</title>
<style>
  body {
    margin: 0;
    background: url('dist/img/background5.jpg') center/cover no-repeat fixed;
    font-family: sans-serif;
    overflow: hidden;
  }
 #controls {
  position: absolute;
  top: 20px;
  left: 50%;
  transform: translateX(-50%);
  display: flex;
  gap: 10px;
  flex-wrap: wrap; /* Allows wrapping on smaller screens */
  align-items: center;
  z-index: 10;
}

#controls button, #controls input {
  padding: 10px 16px;
  border-radius: 20px;
  border: none;
  outline: none;
  cursor: pointer;
  font-weight: 600;
  transition: 0.2s;
}

#controls input {
  width: 200px;
  padding: 10px 16px;
  border-radius: 20px;
  border: 2px solid #005e0cff;
  outline: none;
  background: rgba(255, 255, 255, 0.5)
  color: #fff;
  font-weight: 600;
}

#controls input:focus {
  border-color: #00a81c;
  background: rgba(0,0,0,0.7);
}

#controls button.active {
  background: #00a81cff;
  color: #fff;
  box-shadow: 0 0 10px #00a130ff;
}

#controls button:hover {
  opacity: 0.85;
}

/* Action buttons */
#controls button#addBtn { background: #006effff; color: #fff; }
#controls button#editBtn { background: #ffc107; color: #fff; }
#controls button#backBtn { background: #dc3545; color: #fff; }

</style>
</head>
<body>
<div id="controls">
  <!-- Category buttons -->
  <button onclick="activateCategory('live')">Live</button>
  <button onclick="activateCategory('local')">Local</button>
  <button onclick="activateCategory('web')">Web</button>
  
  <!-- Action buttons -->

  <!-- Search box -->
  <input type="text" placeholder="Search..." oninput="searchBlocks(this.value)">
    <button id="addBtn">Add</button>
  <button id="editBtn">Edit</button>
  <button id="backBtn">Back</button>

</div>

<script src="plugins/pixi.min.js"></script>
<script src="plugins/matter.min.js"></script>
<script>
    document.getElementById('addBtn').addEventListener('click', () => {
  alert('Add button clicked! Implement your add logic here.');
});

document.getElementById('editBtn').addEventListener('click', () => {
  alert('Edit button clicked! Implement your edit logic here.');
});

document.getElementById('backBtn').addEventListener('click', () => {
  alert('Back button clicked! Implement your back logic here.');
});

const CATEGORY_DEFAULT = 0x0001;
const CATEGORY_ARRANGED = 0x0002;
const CATEGORY_IGNORE = 0x0004;

const app = new PIXI.Application({ width: window.innerWidth, height: window.innerHeight, backgroundAlpha: 0 });
document.body.appendChild(app.view);

const Engine = Matter.Engine,
      World = Matter.World,
      Bodies = Matter.Bodies,
      Body = Matter.Body,
      Mouse = Matter.Mouse,
      MouseConstraint = Matter.MouseConstraint;

const engine = Engine.create();
const world = engine.world;
engine.gravity.y = 1;

// Boundaries
const boundaries = [
  Bodies.rectangle(window.innerWidth/2, window.innerHeight+50, window.innerWidth*2, 100, { isStatic: true, restitution:0.5 }),
  Bodies.rectangle(-50, window.innerHeight/2, 100, window.innerHeight*2, { isStatic: true }),
  Bodies.rectangle(window.innerWidth+50, window.innerHeight/2, 100, window.innerHeight*2, { isStatic: true }),
  Bodies.rectangle(window.innerWidth/2, -50, window.innerWidth*2, 100, { isStatic: true })
];
World.add(world, boundaries);

// Data
const data = [
  {name:"Jira", url:"#", cat:"live"},
  {name:"Prod Dashboard", url:"#", cat:"live"},
  {name:"Live API", url:"#", cat:"live"},
  {name:"Localhost", url:"#", cat:"local"},
  {name:"phpMyAdmin", url:"#", cat:"local"},
  {name:"Dev Server", url:"#", cat:"local"},
  {name:"Google", url:"#", cat:"web"},
  {name:"Docs", url:"#", cat:"web"},
  {name:"GitHub", url:"#", cat:"web"},
];

const blocks = [];
const blockSize = 100;

// Create blocks
data.forEach(d=>{
  const body = Bodies.rectangle(Math.random()*app.screen.width, -Math.random()*300, blockSize, blockSize, { 
    restitution:0.5, friction:0.3, frictionAir:0.02,
    collisionFilter: { category: CATEGORY_DEFAULT, mask: CATEGORY_DEFAULT | CATEGORY_ARRANGED } 
  });
  Body.setAngle(body, Math.random()*Math.PI);
  World.add(world, body);

  const gfx = new PIXI.Container();
  const rect = new PIXI.Graphics();
// Generate a random gray shade (from dark to light)
let gray = Math.floor(Math.random() * 156) + 100; // 100–255 for visible gray
let color = (gray << 16) | (gray << 8) | gray;

// Decide border color based on gray shade (contrast with fill)
let borderColor = gray > 180 ? 0x000000 : 0xffffff;

rect.lineStyle(3, borderColor); // 3px border
rect.beginFill(color, 1);
rect.drawRoundedRect(-blockSize/2, -blockSize/2, blockSize, blockSize, 12);
rect.endFill();


// Decide text color based on gray shade
let textColor = gray > 180 ? 0x000000 : 0xffffff;

const text = new PIXI.Text(d.name,{
    fontSize: 16,
    fill: textColor,
    align: 'center',
    wordWrap: true,
    wordWrapWidth: blockSize - 10
});

  text.anchor.set(0.5);
  gfx.addChild(rect,text);
  gfx.interactive = true;
  gfx.cursor = 'pointer';
  gfx.on('pointerdown', ()=>{ window.open(d.url,'_blank'); });

  app.stage.addChild(gfx);
  blocks.push({body,gfx,cat:d.cat,name:d.name});
});

// Ticker
app.ticker.add(()=>{
  blocks.forEach(b=>{
    b.gfx.x = b.body.position.x;
    b.gfx.y = b.body.position.y;
    b.gfx.rotation = b.body.angle;

    if(b.target){
      // Smooth snap
      const dx = b.target.x - b.body.position.x;
      const dy = b.target.y - b.body.position.y;
      const dAngle = b.targetRotation - b.body.angle;

      Body.setVelocity(b.body,{x:dx*0.2, y:dy*0.2});
      Body.setAngularVelocity(b.body,dAngle*0.2);

      if(Math.abs(dx)<1 && Math.abs(dy)<1 && Math.abs(dAngle)<0.05){
        Body.setPosition(b.body,{x:b.target.x, y:b.target.y});
        Body.setAngle(b.body,b.targetRotation);
        b.body.collisionFilter.category = CATEGORY_ARRANGED;
        b.body.collisionFilter.mask = CATEGORY_ARRANGED;
        b.target = null;
      }
    }

    // Keep blocks inside screen
    const half = blockSize/2;
    const pos = b.body.position;
    if(pos.x<half) Body.setPosition(b.body,{x:half, y:pos.y});
    if(pos.x>app.screen.width-half) Body.setPosition(b.body,{x:app.screen.width-half, y:pos.y});
    if(pos.y<half) Body.setPosition(b.body,{x:pos.x, y:half});
    if(pos.y>app.screen.height-half) Body.setPosition(b.body,{x:pos.x, y:app.screen.height-half});
  });
});

// Arrange category
// Arrange category
function activateCategory(cat){
  // Remove active class from all buttons
  document.querySelectorAll('#controls button').forEach(btn => btn.classList.remove('active'));

  // Add active class to the clicked category button
  document.querySelector(`#controls button[onclick="activateCategory('${cat}')"]`).classList.add('active');

  const padding = 40;
  const marginX = 80;
  const marginY = 150;
  const gapX = blockSize + padding;
  const gapY = blockSize + padding;

  const catBlocks = blocks.filter(b=>b.cat===cat);
  const columns = Math.floor((window.innerWidth - marginX*2 + padding)/gapX)||1;
  const totalWidth = (columns-1)*gapX + blockSize;
  const offsetX = (window.innerWidth - totalWidth)/2 + blockSize/2;

  // Arrange selected category
  catBlocks.forEach((b,i)=>{
    const row = Math.floor(i/columns);
    const col = i%columns;
    b.target = { x: offsetX + col*gapX, y: marginY + row*gapY };
    b.targetRotation = 0;
    b.body.collisionFilter.category = CATEGORY_IGNORE;
    b.body.collisionFilter.mask = 0;
  });

  // Mini explosion for other blocks
  blocks.filter(b=>b.cat!==cat).forEach(b=>{
    b.target = null;
    b.targetRotation = null;
    const angle = Math.random() * Math.PI * 2;
    const speed = 5 + Math.random() * 5;
    const vx = Math.cos(angle) * speed;
    const vy = Math.sin(angle) * speed - 5;
    Body.setVelocity(b.body, { x: vx, y: vy });
    Body.setAngularVelocity(b.body, (Math.random()-0.5) * 0.5);
    b.body.collisionFilter.category = CATEGORY_DEFAULT;
    b.body.collisionFilter.mask = CATEGORY_DEFAULT | CATEGORY_ARRANGED;
  });
}

// Search
function searchBlocks(text){
  text = text.toLowerCase();
  
  // Get blocks that start with the search text
  const matchingBlocks = blocks.filter(b => b.name.toLowerCase().startsWith(text) && text !== "");
  const nonMatchingBlocks = blocks.filter(b => !b.name.toLowerCase().startsWith(text) || text === "");
  
  // Arrange matching blocks nicely
  const padding = 40;
  const marginX = 80;
  const marginY = 150;
  const gapX = blockSize + padding;
  const gapY = blockSize + padding;
  const columns = Math.floor((window.innerWidth - marginX*2 + padding)/gapX) || 1;
  const totalWidth = (columns-1)*gapX + blockSize;
  const offsetX = (window.innerWidth - totalWidth)/2 + blockSize/2;

  matchingBlocks.forEach((b,i)=>{
    const row = Math.floor(i/columns);
    const col = i%columns;
    b.target = { x: offsetX + col*gapX, y: marginY + row*gapY };
    b.targetRotation = 0;
    b.body.collisionFilter.category = CATEGORY_IGNORE;
    b.body.collisionFilter.mask = 0;
  });

  // Make non-matching blocks fall naturally
  nonMatchingBlocks.forEach(b=>{
    b.target = null;
    b.targetRotation = null;
    Body.setVelocity(b.body,{
      x:(Math.random()-0.5)*5,
      y:(Math.random()*2+2)
    });
    Body.setAngularVelocity(b.body,(Math.random()-0.5)*0.2);
    b.body.collisionFilter.category = CATEGORY_DEFAULT;
    b.body.collisionFilter.mask = CATEGORY_DEFAULT | CATEGORY_ARRANGED;
  });
}


// Dragging
const mouse = Mouse.create(app.view);
const mouseConstraint = MouseConstraint.create(engine,{ mouse, constraint:{ stiffness:0.2 } });
World.add(world, mouseConstraint);

Engine.run(engine);
</script>
</body>
</html>
