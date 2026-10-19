@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between align-items-end mb-4"><div><div class="text-uppercase small accent fw-bold">Kitchen display</div><h1 class="fw-bold mb-0">Kitchen Orders</h1><div class="text-secondary">Confirm and prepare FoodHub orders.</div></div><button id="kLogout" class="btn btn-outline-secondary d-none">Sign out</button></div>
<div id="kBoard" class="row g-3"><div class="col-lg-4"><div class="card food-card p-3"><h5>Confirmed</h5><div id="confirmed"></div></div></div><div class="col-lg-4"><div class="card food-card p-3"><h5>Preparing</h5><div id="preparing"></div></div></div><div class="col-lg-4"><div class="card food-card p-3"><h5>Ready</h5><div id="ready"></div></div></div></div>
@endsection
@push('scripts')
<script>
(()=>{
const tokenKey='fh_kitchen_token';let token=localStorage.getItem(tokenKey)||'';
const $=id=>document.getElementById(id),esc=v=>String(v??'').replace(/[&<>]/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;'}[c]));
async function api(u,o={}){const r=await fetch(u,{...o,headers:{Accept:'application/json','Content-Type':'application/json',Authorization:'Bearer '+token,...(o.headers||{})}});const d=await r.json().catch(()=>({}));if(!r.ok)throw Error(d.message||'Request failed');return d;}
function card(o,next,label){return `<div class="border rounded-4 p-3 mb-3"><div class="d-flex justify-content-between"><strong>${esc(o.order_number)}</strong><span>${esc(o.status)}</span></div><div class="small text-secondary mt-1">${esc(o.customer?.name)} · ${esc(o.branch?.name)}</div><div class="mt-2">${(o.items||[]).map(i=>`<div>${esc(i.quantity)} × ${esc(i.product_name)}</div>`).join('')}</div>${next?`<button class="btn btn-sm btn-fh mt-3 w-100" onclick="move(${o.id},'${next}')">${label}</button>`:''}</div>`}
async function load(){const d=await api('/api/v1/staff/orders?status=confirmed');const p=await api('/api/v1/staff/orders?status=preparing');const r=await api('/api/v1/staff/orders?status=ready');$('confirmed').innerHTML=d.data.map(o=>card(o,'preparing','Start preparing')).join('')||'<div class="text-secondary">None</div>';$('preparing').innerHTML=p.data.map(o=>card(o,'ready','Mark ready')).join('')||'<div class="text-secondary">None</div>';$('ready').innerHTML=r.data.map(o=>card(o,null,'')).join('')||'<div class="text-secondary">None</div>'}
window.move=async(id,status)=>{try{await api('/api/v1/staff/orders/'+id+'/transition',{method:'POST',body:JSON.stringify({status})});load();}catch(e){alert(e.message)}};
async function logout(){try{await fetch('/api/v1/auth/logout',{method:'POST',headers:{Authorization:'Bearer '+token,Accept:'application/json'}})}catch(e){}localStorage.removeItem(tokenKey);window.location.replace('/login/kitchen');}
async function restore(){if(!token){window.location.replace('/login/kitchen');return;}try{const r=await fetch('/api/v1/me',{headers:{Authorization:'Bearer '+token,Accept:'application/json'}});const u=await r.json();if(!r.ok||u.role!=='kitchen')throw Error('Saved kitchen session is invalid.');load();}catch(e){localStorage.removeItem(tokenKey);token='';window.location.replace('/login/kitchen');}}
$('kLogout').addEventListener('click',logout);restore();setInterval(()=>{if(token)load()},15000);
})();
</script>
@endpush
