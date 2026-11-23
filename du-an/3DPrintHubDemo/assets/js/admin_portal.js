/* Admin độc lập: tìm kiếm / lọc nhiều thuộc tính, sidebar mobile, dashboard tự cập nhật. */
(() => {
  const norm=(value)=>String(value??'').normalize('NFD').replace(/[\u0300-\u036f]/g,'').replace(/đ/g,'d').replace(/Đ/g,'D').toLowerCase().trim();
  document.querySelectorAll('[data-filter-table]').forEach(toolbar=>{
    const tableId=toolbar.dataset.filterTable;
    const table=document.getElementById(tableId);if(!table)return;
    const rows=[...table.querySelectorAll('tbody tr[data-admin-row]')];
    const search=toolbar.querySelector('[data-filter-search]');
    const selectors=[...toolbar.querySelectorAll('select[data-filter-key]')];
    const count=document.querySelector('[data-filter-count="'+tableId+'"]');
    const empty=document.querySelector('[data-empty-for="'+tableId+'"]');
    const prepared=rows.map(row=>({row,search:norm(row.dataset.search||row.textContent)}));
    const apply=()=>{
      const term=norm(search?.value);
      const attrs=selectors.map(s=>({key:s.dataset.filterKey,want:norm(s.value)})).filter(x=>x.want);
      let visible=0;
      prepared.forEach(item=>{
        const matches=(!term || item.search.includes(term)) && attrs.every(f=>norm(item.row.dataset[f.key])===f.want);
        item.row.hidden=!matches;
        if(matches)visible++;
      });
      if(count)count.textContent=`Hiển thị ${visible}/${rows.length} kết quả`;
      if(empty)empty.hidden=visible!==0 || rows.length===0;
    };
    search?.addEventListener('input',apply);
    selectors.forEach(s=>s.addEventListener('change',apply));
    toolbar.querySelector('[data-filter-clear]')?.addEventListener('click',()=>{
      if(search)search.value='';selectors.forEach(s=>s.value='');apply();search?.focus();
      if(location.search.includes('q=')) history.replaceState(null,'',location.pathname+location.search.replace(/([?&])q=[^&]*/,'$1').replace(/[?&]$/,''));
    });
    apply();
  });

  const app=document.getElementById('admin-app');const menu=document.getElementById('admin-menu-toggle');const overlay=document.getElementById('admin-mobile-overlay');
  const toggle=(open)=>{app?.classList.toggle('admin-menu-open',open);menu?.setAttribute('aria-expanded',String(open));document.body.style.overflow=open?'hidden':''};
  menu?.addEventListener('click',()=>toggle(!app.classList.contains('admin-menu-open')));
  overlay?.addEventListener('click',()=>toggle(false));
  window.addEventListener('keydown',(e)=>{
    if(e.key==='Escape')toggle(false);
    if(e.key==='/' && !['INPUT','TEXTAREA','SELECT'].includes(document.activeElement?.tagName)){
      const field=document.querySelector('[data-filter-search]')||document.querySelector('.admin-global-search input');
      if(field){e.preventDefault();field.focus()}
    }
  });

  const dashboard=document.getElementById('salesChart');if(!dashboard)return;
  const formatMoney=(num)=>new Intl.NumberFormat('vi-VN',{maximumFractionDigits:0}).format(Number(num)||0)+'đ';
  const formatNumber=(num)=>new Intl.NumberFormat('vi-VN').format(Number(num)||0);
  const orderLabels={pending:'Chờ xác nhận',printing:'Đang sản xuất',shipping:'Đang giao hàng',completed:'Hoàn tất',cancelled:'Đã hủy'};
  const showRecent=(orders)=>{
    if(!Array.isArray(orders))return;
    const body=document.getElementById('dashboard-orders-body');if(!body)return;body.replaceChildren();
    if(!orders.length){const tr=document.createElement('tr');const td=document.createElement('td');td.colSpan=4;td.className='admin-empty-row';td.textContent='Chưa có đơn hàng.';tr.append(td);body.append(tr);return}
    orders.forEach(o=>{
      const tr=document.createElement('tr');
      const code=document.createElement('td');const title=document.createElement('strong');title.textContent=String(o.order_code||'');const day=document.createElement('span');day.className='admin-subtext';
      const raw=String(o.created_at||'');const m=raw.match(/^(\d{4})-(\d\d)-(\d\d)/);day.textContent=m?`${m[3]}/${m[2]}/${m[1]}`:raw;code.append(title,day);
      const shop=document.createElement('td');shop.textContent=String(o.shop_name||'');
      const amount=document.createElement('td');amount.textContent=formatMoney(o.total_amount);
      const statusCell=document.createElement('td');const pill=document.createElement('span');
      const state=String(o.order_status||'');pill.className='admin-badge '+(orderLabels[state]?'status-'+state:'');pill.textContent=orderLabels[state]||state;statusCell.append(pill);
      tr.append(code,shop,amount,statusCell);body.append(tr);
    });
  };
  let pending=false;
  const refresh=async()=>{
    if(pending)return;
    pending=true;const btn=document.getElementById('admin-refresh-now');if(btn)btn.disabled=true;
    try{
      const res=await fetch('admin_api.php',{headers:{'Accept':'application/json'},credentials:'same-origin',cache:'no-store'});
      if(!res.ok)throw new Error('Không thể tải dữ liệu');
      const payload=await res.json();const stats=payload.stats || {};
      document.querySelectorAll('[data-stat]').forEach(el=>{
        const key=el.dataset.stat;
        const value=key==='pending_total'?Number(stats.pending_shops||0)+Number(stats.pending_products||0):stats[key];
        if(value===undefined)return;
        el.textContent=key==='revenue'?formatMoney(value):formatNumber(value)+(key==='pending_total'?' đang chờ':'');
      });
      showRecent(payload.recent_orders);
      document.dispatchEvent(new CustomEvent('admin:data-updated',{detail:{series:payload.series,order_statuses:payload.order_statuses,sales_insights:payload.sales_insights}}));
      const note=document.getElementById('admin-last-updated');if(note)note.textContent='Cập nhật '+new Date().toLocaleTimeString('vi-VN',{hour:'2-digit',minute:'2-digit'});
    }catch(e){const note=document.getElementById('admin-last-updated');if(note)note.textContent='Chưa đồng bộ được · thử làm mới';}
    finally{pending=false;if(btn)btn.disabled=false}
  };
  document.getElementById('admin-refresh-now')?.addEventListener('click',refresh);
  setInterval(()=>{if(!document.hidden)refresh()},30000);
})();
