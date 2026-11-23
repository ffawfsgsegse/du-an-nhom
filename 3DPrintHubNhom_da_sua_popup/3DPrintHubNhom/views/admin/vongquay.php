<?php
require_once __DIR__ . '/../../config.php';
$userId = (int)($_SESSION['uid'] ?? 0);
$daQuayHomNay = false;

if ($userId > 0) {
    $check = $pdo->prepare("
        SELECT COUNT(*)
        FROM user_rewards
        WHERE user_id = ?
          AND created_at >= CURDATE()
          AND created_at < CURDATE() + INTERVAL 1 DAY
    ");
    $check->execute([$userId]);
    $daQuayHomNay = (int)$check->fetchColumn() > 0;
}

$wheelRows = $pdo->query("
    SELECT id, name, discount_percent
    FROM promotions
    WHERE status = 1
      AND quantity > 0
      AND (start_date IS NULL OR start_date <= CURDATE())
      AND (end_date IS NULL OR end_date >= CURDATE())
    ORDER BY id
")->fetchAll(PDO::FETCH_ASSOC);
// Server controls actual results. The visible segments mirror eligible database prizes.
$wheelSegments = $wheelRows ?: [['id'=>0,'name'=>'Chưa có phần thưởng','discount_percent'=>0]];
?>

<style>
.wv-shell{position:relative;isolation:isolate;overflow:hidden;margin:32px auto;padding:32px 18px 38px;border-radius:30px;background:radial-gradient(circle at 50% 0%,#5a32a2 0%,#211142 45%,#0d1028 100%);color:#fff;text-align:center;box-shadow:0 24px 70px #160e4a77;font-family:system-ui,Arial,sans-serif}
.wv-shell *{box-sizing:border-box}.wv-kicker{letter-spacing:3px;color:#f9d880;font-weight:800;font-size:12px}.wv-title{margin:8px 0;font-size:clamp(26px,4vw,44px);font-weight:900;color:#ffe7a0;text-shadow:0 0 24px #f6ad34aa}.wv-sub{color:#e2d8fa}.wv-layout{display:flex;justify-content:center;align-items:center;flex-wrap:wrap;gap:32px;margin-top:26px}.wv-wheel-wrap{width:min(85vw,470px);aspect-ratio:1;position:relative;filter:drop-shadow(0 0 25px #edb95288)}#wv-canvas{width:100%;height:100%;border:10px solid #f9d77b;border-radius:50%;background:#301a5b;box-shadow:inset 0 0 0 4px #fff3b3,0 0 0 5px #a76a2a;transition:none}.wv-pointer{position:absolute;z-index:2;top:-12px;left:50%;transform:translateX(-50%);width:0;height:0;border-left:20px solid transparent;border-right:20px solid transparent;border-top:43px solid #fff4a2;filter:drop-shadow(0 3px 3px #0009)}.wv-center{position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);background:linear-gradient(#fff1b3,#eab44c);width:65px;height:65px;border:5px solid #fff7d2;border-radius:50%;display:grid;place-items:center;font-size:26px;box-shadow:0 0 20px #ffd86d}.wv-info{max-width:280px;text-align:center}.wv-prizes{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin:20px 0}.wv-prizes img{width:100%;height:100px;object-fit:contain;background:#ffffff12;border:1px solid #ffffff35;border-radius:12px;padding:5px}.wv-btn{padding:15px 36px;border:0;border-radius:99px;background:linear-gradient(120deg,#fff1a8,#f2a72d);color:#3e235a;font-size:18px;font-weight:900;cursor:pointer;box-shadow:0 8px 25px #ffcc6177}.wv-btn:disabled{opacity:.55;cursor:wait}.wv-result{min-height:48px;margin-top:16px;color:#ffedb2;font-weight:800}.wv-note{font-size:12px;color:#cfc2e5}.wv-overlay[hidden]{display:none!important}.wv-overlay{position:fixed;inset:0;z-index:99999;background:#09071beF;display:grid;place-items:center;padding:20px;overflow:auto}.wv-popup{position:relative;max-width:520px;width:100%;padding:28px 20px;border-radius:26px;background:radial-gradient(circle at top,#6b369b,#24103f 70%);border:3px solid #f7c85d;box-shadow:0 0 80px #f6be5788;text-align:center;animation:wv-pop .6s cubic-bezier(.18,1.4,.35,1)}@keyframes wv-pop{from{transform:scale(.4);opacity:0}to{transform:scale(1);opacity:1}}.wv-popup h2{color:#ffe9a5;font-size:clamp(24px,5vw,38px);margin:8px 0}.wv-popup img{display:block;width:100%;max-height:290px;object-fit:contain;margin:10px auto;border-radius:15px}.wv-close{position:absolute;right:12px;top:10px;background:#ffffff33;border:0;border-radius:50%;width:36px;height:36px;color:white;font-size:22px;cursor:pointer}.wv-confetti{position:fixed;z-index:100000;top:-20px;width:9px;height:16px;pointer-events:none;animation:wv-fall 3.5s linear forwards}@keyframes wv-fall{to{transform:translateY(110vh) rotate(900deg);opacity:0}}
#wv-canvas {
    width: 500px !important;
    height: 500px !important;
    max-width: 100%;
}

.wv-shell {
    max-width: 700px !important;
    margin: 5px auto !important;
    padding: 5px !important;
}
/* Thu nhỏ toàn bộ vòng quay VIP còn 70% */
.wv-shell {
    zoom: 0.7;
    max-width: 1100px !important;
    margin: 10px auto !important;
}
 .wv-prizes span{display:grid;place-items:center;min-height:76px;border:1px solid #ffffff45;border-radius:12px;background:#ffffff12;font-weight:700} .wv-overlay[hidden]{display:none!important}
</style>

<?php if ($daQuayHomNay): ?>
    
<?php else: ?>
    <section class="wv-shell" aria-label="Vòng quay may mắn demo">
<div class="wv-kicker">✦ 3DPRINTHUB ✦ GIẢI THƯỞNG MÔ PHỎNG ✦</div><h2 class="wv-title">🎡 VÒNG QUAY MAY MẮN VIP</h2><p class="wv-sub">Thử vận may – nhận quà bất ngờ!</p>
<div class="wv-layout"><div class="wv-wheel-wrap"><div class="wv-pointer"></div><canvas id="wv-canvas" width="800" height="800"></canvas><div class="wv-center">★</div></div>
<div class="wv-info"><strong>✨ GIẢI THƯỞNG ĐẶC BIỆT ✨</strong><div class="wv-prizes"><span>🎁 Quà bất ngờ</span><span>⭐ Ưu đãi</span><span>🎉 May mắn</span><span>🎟️ Voucher</span></div><button class="wv-btn" id="wv-spin" type="button">🎁 QUAY NGAY</button><div id="wv-result" class="wv-result" aria-live="polite"></div><div class="wv-note">Giải thưởng lớn chỉ để trình diễn trong bài tập, không phải cam kết trao giải thật.</div></div></div>
</section>
<div id="wv-overlay" class="wv-overlay" hidden role="dialog" aria-modal="true" aria-labelledby="wv-winner-title"><div class="wv-popup"><button class="wv-close" id="wv-close" aria-label="Đóng">×</button><div style="font-size:36px">🏆🎉🏆</div><h2 id="wv-winner-title">CHÚC MỪNG BẠN!</h2><img id="wv-win-img" alt="Hình ảnh phần thưởng"><h3 id="wv-win-name"></h3><p>Giải thưởng mô phỏng dành cho bài tập</p><button class="wv-btn" id="wv-ok">TUYỆT VỜI!</button></div></div>
<script>
(()=>{
const prizes = <?= json_encode($wheelSegments, JSON_UNESCAPED_UNICODE|JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?>;
const canvas=document.getElementById('wv-canvas'),ctx=canvas.getContext('2d'),button=document.getElementById('wv-spin'),result=document.getElementById('wv-result'),overlay=document.getElementById('wv-overlay');
const palette=['#e74c83','#7e51e8','#158ca9','#d68a26','#1c9b82','#ac4cb5','#d95464','#3c70c7','#c59b30','#9159c6','#25a7a4','#a94473'];let rot=0;
function draw(){const n=prizes.length,arc=2*Math.PI/n;ctx.clearRect(0,0,800,800);ctx.save();ctx.translate(400,400);for(let i=0;i<n;i++){const a=i*arc;ctx.beginPath();ctx.moveTo(0,0);ctx.arc(0,0,375,a,a+arc);ctx.closePath();ctx.fillStyle=palette[i%palette.length];ctx.fill();ctx.strokeStyle='#ffdf8e';ctx.lineWidth=3;ctx.stroke();ctx.save();ctx.rotate(a+arc/2);ctx.textAlign='right';ctx.textBaseline='middle';ctx.fillStyle='white';ctx.font='bold '+(n>10?19:25)+'px system-ui';const name=String(prizes[i].name);const txt=name.length>22?name.slice(0,20)+'…':name;ctx.fillText(txt,354,0,270);ctx.restore()}ctx.restore()};draw();
function prizeImage(name){const t=name.toLowerCase();if(t.includes('iphone'))return 'iphone.png';if(t.includes('samsung')||t.includes('s26'))return 'samsung.png';if(t.includes('xiaomi'))return 'xiaomi.png';if(t.includes('lamborghini')||t.includes('lambogini'))return 'lamborghini.png';return null}
function confetti(){for(let i=0;i<100;i++){const e=document.createElement('i');e.className='wv-confetti';e.style.left=Math.random()*100+'vw';e.style.background=['#ffda6b','#f66ca9','#8b76ff','#6de5ef'][i%4];e.style.animationDelay=Math.random()*.8+'s';e.style.animationDuration=(2+Math.random()*2)+'s';document.body.appendChild(e);setTimeout(()=>e.remove(),5000)}}
function speak(name){if(!('speechSynthesis'in window))return;window.speechSynthesis.cancel();const u=new SpeechSynthesisUtterance('Chúc mừng bạn đã trúng '+name);u.lang='vi-VN';u.rate=.9;window.speechSynthesis.speak(u)}
function show(name){const img=prizeImage(name);const winImg=document.getElementById('wv-win-img');if(img){winImg.src='assets/wheel-prizes/'+img;winImg.hidden=false;winImg.onerror=()=>{winImg.hidden=true}}else{winImg.hidden=true;}document.getElementById('wv-win-name').textContent=name;overlay.hidden=false;confetti();speak(name)}
function hideWheel() {
    const wheel = document.querySelector('.wv-shell');
    if (wheel) {
        wheel.style.display = 'none';
    }
}
function close(){overlay.hidden=true;hideWheel();if(typeof window.closePromoWheelPopup==='function')window.closePromoWheelPopup();if('speechSynthesis'in window)window.speechSynthesis.cancel()};document.getElementById('wv-close').onclick=close;document.getElementById('wv-ok').onclick=close;
button.addEventListener('click',async()=>{button.disabled=true;result.textContent='Đang quay...';try{const response=await fetch('api/spin.php',{method:'POST',credentials:'same-origin'});const data=await response.json();if(!response.ok||!data.success)throw Error(data.message||'Không quay được');const i=prizes.findIndex(p=>Number(p.id)===Number(data.id));if(i<0)throw Error('Giải thưởng đã thay đổi, hãy tải lại trang để đồng bộ vòng quay.');const arc=2*Math.PI/prizes.length;const target=-Math.PI/2-(i+.5)*arc;const delta=((target-(rot%(2*Math.PI)))%(2*Math.PI)+2*Math.PI)%(2*Math.PI);const start=rot,end=rot+Math.PI*2*6+delta,t0=performance.now();await new Promise(resolve=>{function frame(now){const t=Math.min((now-t0)/5200,1),ease=1-Math.pow(1-t,4);canvas.style.transform='rotate('+(start+(end-start)*ease)+'rad)';if(t<1)requestAnimationFrame(frame);else{rot=end;resolve()}}requestAnimationFrame(frame)});result.textContent='🎉 Bạn nhận được: '+data.name;show(String(data.name))}catch(e){result.textContent='⚠️ '+e.message}finally{button.disabled=false}});
})();
</script>
<?php endif; ?>