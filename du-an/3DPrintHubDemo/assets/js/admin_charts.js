/* Biểu đồ Admin - canvas thuần, dữ liệu từ SQL; không dùng CDN. */
(() => {
  const parse = (id) => {try{return JSON.parse(document.getElementById(id)?.textContent || '[]')}catch{return []}};
  let dashboardSales = parse('admin-sales-data');
  let dashboardStatuses = parse('admin-status-data');
  const reportSales = parse('revenueData');
  const fmtCompact = (n) => new Intl.NumberFormat('vi-VN',{notation:'compact',maximumFractionDigits:1}).format(n);
  const fmtNumber = (n) => new Intl.NumberFormat('vi-VN').format(n);

  function init(canvas, desiredHeight) {
    const rect = canvas.getBoundingClientRect();
    const w=Math.max(260, Math.round(rect.width || canvas.parentElement.clientWidth || 420));
    const h=desiredHeight;
    const ratio=Math.min(2,window.devicePixelRatio || 1);
    canvas.width=Math.round(w*ratio);canvas.height=Math.round(h*ratio);
    const ctx=canvas.getContext('2d');ctx.setTransform(ratio,0,0,ratio,0,0);
    ctx.clearRect(0,0,w,h);
    return {ctx,w,h};
  }
  function roundedBar(ctx,x,y,w,h,r){if(h<=0)return;ctx.beginPath();ctx.roundRect(x,y,w,Math.max(2,h),[Math.min(r,h/2),Math.min(r,h/2),0,0]);ctx.fill()}
  function sales(canvas,items,isReport=false) {
    if(!canvas)return;
    const {ctx,w,h}=init(canvas,isReport?290:235);
    const L=w>500?62:48,R=13,T=22,B=40,PW=w-L-R,PH=h-T-B;
    ctx.font='10px DM Sans, system-ui,sans-serif';ctx.textBaseline='middle';ctx.strokeStyle='#9fd3e029';ctx.fillStyle='#83a9bf';ctx.lineWidth=1;
    const values=items.map(x=>Math.max(0,Number(x.revenue)||0));const rawMax=Math.max(0,...values);
    const scaleMax=rawMax<=0?1:rawMax*1.12;
    for(let step=0;step<=4;step++){
      const y=T+PH*(1-step/4);ctx.beginPath();ctx.moveTo(L,y);ctx.lineTo(w-R,y);ctx.stroke();
      ctx.textAlign='right';ctx.fillText(fmtCompact(scaleMax*step/4),L-8,y);
    }
    if(!items.length){ctx.fillStyle='#8aafbd';ctx.textAlign='center';ctx.fillText('Chưa có dữ liệu doanh thu hoàn tất',w/2,T+PH/2);return}
    const slot=PW/items.length;const bw=Math.max(5,Math.min(35,slot*.47));
    items.forEach((it,i)=>{
      const value=values[i];const bh=value/scaleMax*PH;
      const x=L+slot*i+(slot-bw)/2;const y=T+PH-bh;
      const grad=ctx.createLinearGradient(x,y,x,T+PH);grad.addColorStop(0,'#68f0e6');grad.addColorStop(1,'#298dbf');ctx.fillStyle=grad;
      if(value>0)roundedBar(ctx,x,y,bw,bh,Math.min(5,bw/3));
      if(i % (w<560?Math.max(1,Math.ceil(items.length/5)):Math.max(1,Math.ceil(items.length/8)))===0 || i===items.length-1){
        ctx.fillStyle='#a1becb';ctx.textAlign='center';
        let label=String(it.label ?? ''); if(label.length>10)label=label.slice(-10);
        ctx.fillText(label,x+bw/2,T+PH+23);
      }
    });
  }
  const labels={pending:'Chờ xác nhận',printing:'Đang sản xuất',shipping:'Đang giao',completed:'Hoàn tất',cancelled:'Đã hủy'};
  const colors={pending:'#f7c970',printing:'#b3a4ff',shipping:'#5ac7ed',completed:'#58dfb4',cancelled:'#fd8ea4'};
  function donut(canvas,rows){
    if(!canvas)return;
    const {ctx,w,h}=init(canvas,165);const centerX=w/2,centerY=h/2;
    const data=rows.map(x=>({status:String(x.order_status),value:Number(x.n)||0})).filter(x=>x.value>0);
    const total=data.reduce((s,v)=>s+v.value,0),r=Math.min(w,h)*.42,thickness=Math.max(13,r*.23);
    ctx.lineWidth=thickness;ctx.lineCap='round';
    ctx.beginPath();ctx.strokeStyle='#31596b';ctx.arc(centerX,centerY,r,0,2*Math.PI);ctx.stroke();
    if(total>0){let a=-Math.PI/2;data.forEach(item=>{const sweep=item.value/total*2*Math.PI;
      ctx.beginPath();ctx.strokeStyle=colors[item.status]||'#9cc9e4';ctx.arc(centerX,centerY,r,a+0.018,Math.max(a+0.018,a+sweep-0.018));ctx.stroke();a+=sweep;
    })}
    ctx.textAlign='center';ctx.fillStyle='#f0ffff';ctx.font='700 27px Space Grotesk,system-ui';ctx.fillText(fmtNumber(total),centerX,centerY+6);
    ctx.font='11px DM Sans,system-ui';ctx.fillStyle='#8ab2c6';ctx.fillText('đơn hàng',centerX,centerY+25);
    const legend=document.getElementById('ordersDonutLegend');if(!legend)return;
    legend.replaceChildren();
    if(!data.length){const empty=document.createElement('p');empty.className='admin-helper';empty.textContent='Chưa có đơn hàng.';legend.appendChild(empty);return}
    data.forEach(item=>{
      const el=document.createElement('div');el.className='admin-legend-item';
      const dot=document.createElement('span');dot.className='admin-legend-dot';dot.style.background=colors[item.status]||'#9cc9e4';
      const txt=document.createElement('span');txt.textContent=labels[item.status]||item.status;
      const n=document.createElement('b');n.textContent=fmtNumber(item.value);
      el.append(dot,txt,n);legend.appendChild(el);
    });
  }
  let resizeFrame=0;
  const redraw=()=>{
    sales(document.getElementById('salesChart'),dashboardSales);
    sales(document.getElementById('revenueChart'),reportSales,true);
    donut(document.getElementById('ordersDonut'),dashboardStatuses);
  };
  window.addEventListener('resize',()=>{cancelAnimationFrame(resizeFrame);resizeFrame=requestAnimationFrame(redraw)});
  document.addEventListener('admin:data-updated',(event)=>{
    if(Array.isArray(event.detail?.series))dashboardSales=event.detail.series;
    if(Array.isArray(event.detail?.order_statuses))dashboardStatuses=event.detail.order_statuses;
    redraw();
  });
  redraw();
})();
