const toast=document.querySelector('#toast');if(toast)setTimeout(()=>{toast.style.opacity='0';toast.style.transition='opacity .5s';setTimeout(()=>toast.remove(),600)},4500);
function editProduct(p){document.getElementById('editId').value=p.id;document.getElementById('editName').value=p.name;document.getElementById('editPrice').value=p.price;document.getElementById('editStock').value=p.stock;document.getElementById('editDimensions').value=p.dimensions||'';document.getElementById('editCategory').value=p.category_id;document.getElementById('editMaterial').value=p.material_id;document.getElementById('productForm').scrollIntoView({behavior:'smooth',block:'center'});}
const vendorSelect=document.getElementById('vendorSelect'),materialSelect=document.getElementById('materialSelect');if(vendorSelect&&materialSelect){function syncMaterials(){let first=null;[...materialSelect.options].forEach(o=>{o.hidden=o.dataset.vendor!==vendorSelect.value;o.disabled=o.hidden;if(!o.hidden&&!first)first=o;});if(first)materialSelect.value=first.value;}vendorSelect.addEventListener('change',syncMaterials);syncMaterials();}
const observer=new IntersectionObserver(entries=>entries.forEach(e=>{if(e.isIntersecting){e.target.style.opacity=1;e.target.style.transform='translateY(0)';observer.unobserve(e.target)}}),{threshold:.08});document.querySelectorAll('.product-card,.category-tile,.stat-card').forEach((e,i)=>{e.style.opacity=0;e.style.transform='translateY(25px)';e.style.transition=`opacity .55s ${Math.min(i%8*60,420)}ms,transform .55s ${Math.min(i%8*60,420)}ms,border .25s`;observer.observe(e)});

// Banking checkout: show an explanation when customers select QR transfer.
const transferHint=document.getElementById('transferHint');
if(transferHint){const radios=[...document.querySelectorAll('input[name="method"]')];const syncTransferHint=()=>{transferHint.hidden=!radios.some(x=>x.checked&&x.value==='banking');};radios.forEach(x=>x.addEventListener('change',syncTransferHint));syncTransferHint();}

// Interactive copy buttons. Never copy values from untrusted HTML markup into code.
function copyNotice(message){const old=document.querySelector('.copy-toast');if(old)old.remove();const tip=document.createElement('div');tip.className='copy-toast';tip.textContent=message;document.body.appendChild(tip);setTimeout(()=>tip.remove(),2100);}
document.querySelectorAll('[data-copy]').forEach(button=>button.addEventListener('click',async()=>{
  const value=button.dataset.copy;
  try{
    if(navigator.clipboard?.writeText){await navigator.clipboard.writeText(value);}else{
      const area=document.createElement('textarea');area.value=value;area.style.position='fixed';area.style.opacity='0';document.body.appendChild(area);area.select();if(!document.execCommand('copy'))throw Error('Copy unavailable');area.remove();
    }
    button.classList.add('copied');const label=button.textContent;button.textContent='✓ Đã sao chép';copyNotice('✓ Đã sao chép vào bộ nhớ tạm');setTimeout(()=>{button.classList.remove('copied');button.textContent=label;},1400);
  }catch(e){copyNotice('Không thể sao chép tự động, hãy chọn và sao chép thủ công.');}
}));
// Extra confirmation for payment actions. Clicking submit is NOT proof of bank receipt.
document.querySelectorAll('form[data-confirm]').forEach(form=>form.addEventListener('submit',e=>{if(!window.confirm(form.dataset.confirm))e.preventDefault();}));
document.querySelectorAll('button[data-confirm]').forEach(button=>button.addEventListener('click',e=>{if(!window.confirm(button.dataset.confirm))e.preventDefault();}));

// Wallet preset amounts (client-side UI only, not a payment confirmation).
document.querySelectorAll('[data-wallet-amount]').forEach(btn => btn.addEventListener('click',()=>{
  const field=document.querySelector('input[name="amount"]');
  if(field)field.value=btn.dataset.walletAmount;
}));

// QR DEMO: đếm ngược phía client; server vẫn kiểm tra expires_at trong MySQL.
document.querySelectorAll('[data-demo-countdown]').forEach(el=>{
 let seconds=Math.max(0,Number(el.dataset.seconds)||0);
 const show=()=>{const m=Math.floor(seconds/60);const s=seconds%60;el.textContent=String(m).padStart(2,'0')+':'+String(s).padStart(2,'0');if(seconds<=0){document.querySelector('.demo-confirm-button')?.closest('form')?.setAttribute('hidden','');document.getElementById('demo-refresh-form')?.removeAttribute('hidden');}};
 show();if(seconds>0){const t=setInterval(()=>{seconds--;show();if(seconds<=0)clearInterval(t);},1000);}
});
