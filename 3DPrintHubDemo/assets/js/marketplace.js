// Các xử lý giao diện được giữ trong JS riêng; kiểm tra quyền luôn thực hiện ở PHP.
document.querySelectorAll('form[data-confirm]').forEach(form=>form.addEventListener('submit', e=>{
 if(!window.confirm(form.dataset.confirm))e.preventDefault();
}));
document.querySelectorAll('button[data-confirm]').forEach(btn=>btn.addEventListener('click', e=>{
 if(!window.confirm(btn.dataset.confirm))e.preventDefault();
}));
const vendorSelect=document.getElementById('vendorSelect');
const materialSelect=document.getElementById('materialSelect');
if(vendorSelect&&materialSelect){
 const selectMaterial=()=>{let first=null;[...materialSelect.options].forEach(o=>{
  if(!o.dataset.vendor)return;
  const allow=o.dataset.vendor===vendorSelect.value;
  o.hidden=!allow;o.disabled=!allow;
  if(allow&&!first)first=o;
 });
 const current=materialSelect.selectedOptions[0];if(!current||current.disabled)materialSelect.value=first?first.value:'';
 };
 vendorSelect.addEventListener('change',selectMaterial);selectMaterial();
}

// QR DEMO: chỉ là đồng hồ giao diện; PHP kiểm tra expires_at trong database.
document.querySelectorAll('[data-demo-countdown]').forEach(node=>{
 let sec=Math.max(0,Number(node.dataset.seconds)||0);
 const draw=()=>{node.textContent=String(Math.floor(sec/60)).padStart(2,'0')+':'+String(sec%60).padStart(2,'0');};draw();
 if(sec>0){const timer=setInterval(()=>{sec--;draw();if(sec<=0){clearInterval(timer);location.reload();}},1000);}
});
