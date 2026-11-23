// Vẽ biểu đồ bằng canvas thuần (không phụ thuộc CDN hay thư viện ngoài).
const canvas=document.getElementById('revenueChart');
const store=document.getElementById('revenueData');
if(canvas&&store){
 let data=[];try{data=JSON.parse(store.textContent)}catch(e){data=[]}
 const render=()=>{
  if(!data.length)return;
  const host=canvas.parentElement;const w=Math.max(420,Math.floor(host.clientWidth)-4);const h=310;
  const dpr=window.devicePixelRatio||1;canvas.style.width='100%';canvas.style.height=h+'px';canvas.width=w*dpr;canvas.height=h*dpr;
  const c=canvas.getContext('2d');c.setTransform(dpr,0,0,dpr,0,0);
  const left=68,right=22,top=26,bottom=54,plotH=h-top-bottom,plotW=w-left-right;
  const max=Math.max(1,...data.map(x=>Number(x.revenue)||0));
  c.font='12px system-ui';c.strokeStyle='#354061';c.fillStyle='#96a5c5';c.lineWidth=1;
  for(let k=0;k<=4;k++){
   const y=top+plotH*(1-k/4);c.beginPath();c.moveTo(left,y);c.lineTo(w-right,y);c.stroke();
   c.fillText(new Intl.NumberFormat('vi-VN',{notation:'compact',maximumFractionDigits:1}).format(max*k/4),9,y+4);
  }
  const slot=plotW/data.length;const bw=Math.min(52,slot*.59);
  data.forEach((item,i)=>{
   const value=Number(item.revenue)||0;const bh=value/max*plotH;
   const x=left+i*slot+(slot-bw)/2;
   const grad=c.createLinearGradient(x,top,x,top+plotH);grad.addColorStop(0,'#ad82ff');grad.addColorStop(1,'#5b4bd0');c.fillStyle=grad;
   c.fillRect(x,top+plotH-bh,bw,Math.max(2,bh));
   c.fillStyle='#9eabc5';const label=String(item.label);c.save();c.translate(x+bw/2,h-30);c.rotate(-0.40);c.textAlign='right';c.fillText(label.length>12?label.slice(-10):label,0,0);c.restore();
  });
 };
 render();window.addEventListener('resize',render);
}
