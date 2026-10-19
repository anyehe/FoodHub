@extends('layouts.app')
@section('content')
<div class="row g-4">
    <div class="col-lg-8">
        <div class="hero p-4 mb-4"><span class="badge badge-fh">CUSTOMER PORTAL</span><h1 class="fw-bold mt-2">Order from FoodHub</h1><p class="mb-0 opacity-75">Browse the live menu, choose a branch, review delivery pricing and track your orders.</p></div>
        <div id="auth" class="card food-card p-4 mb-4"><div class="d-flex flex-wrap justify-content-between align-items-center gap-3"><div><h5 class="fw-bold mb-1">Sign in to your FoodHub account</h5><p class="text-secondary mb-0">Access saved addresses, checkout and order tracking.</p></div><a class="btn btn-fh" href="/login/customer">Sign in</a></div><div id="msg" class="small mt-2"></div></div>
        <div class="d-flex justify-content-between align-items-end mb-3"><div><div class="text-uppercase small accent fw-bold">Live catalog</div><h3 class="fw-bold mb-0">Menu</h3></div><div class="d-flex gap-2"><button id="logoutButton" class="btn btn-outline-secondary btn-sm d-none">Sign out</button><button id="refreshProducts" class="btn btn-outline-dark btn-sm">Refresh</button></div></div>
        <div id="products" class="row g-3"></div>
        <div class="card food-card p-4 mt-4"><div class="d-flex justify-content-between"><h4 class="fw-bold">My Orders</h4><button id="refreshOrders" class="btn btn-sm btn-outline-secondary">Refresh</button></div><div id="orders" class="mt-3"></div></div>
    </div>
    <div class="col-lg-4">
        <div class="card food-card p-4 sticky-cart"><div class="d-flex justify-content-between"><h4 class="fw-bold">Cart</h4><span id="cartCount" class="badge badge-fh">0</span></div><div id="cart"></div><hr>
            <label class="form-label">Branch</label><select id="branch" class="form-select"></select>
            <label class="form-label mt-2">Fulfillment</label><select id="fulfillment" class="form-select"><option value="delivery">Delivery</option><option value="pickup">Pickup</option><option value="dine_in">Dine-in</option></select>
            <label class="form-label mt-2">Delivery address</label><select id="address" class="form-select"></select>
            <label class="form-label mt-2">Coupon</label><div class="input-group"><input id="coupon" class="form-control" placeholder="WELCOME50"><button id="quoteButton" class="btn btn-outline-secondary">Quote</button></div>
            <div id="quote" class="small mt-3"></div>
            <label class="form-label mt-2">Payment</label><select id="payment" class="form-select"><option value="cash">Cash</option><option value="gcash">GCash (gateway required)</option><option value="card">Card (gateway required)</option><option value="paypal">PayPal (gateway required)</option></select>
            <button id="checkoutButton" class="btn btn-fh w-100 mt-3">Place order</button><div id="orderMsg" class="small mt-3"></div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
(()=>{
 const $=id=>document.getElementById(id), money=n=>'₱'+Number(n||0).toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2});
 const esc=v=>String(v??'').replace(/[&<>'"]/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;'}[c]));
 let token=localStorage.getItem('fh_customer_token')||'',products=[],cart=[],branches=[],addresses=[];
 function authUI(ok){$('auth').classList.toggle('d-none',ok);$('logoutButton').classList.toggle('d-none',!ok);$('address').disabled=!ok;}
 function clearToken(){token='';localStorage.removeItem('fh_customer_token');authUI(false);}
 async function api(url,opt={}){const r=await fetch(url,{...opt,headers:{Accept:'application/json',...(opt.headers||{}),...(token?{Authorization:'Bearer '+token}:{})}});const d=await r.json().catch(()=>({}));if(r.status===401){clearToken();throw Error('Unauthenticated.');}if(!r.ok)throw Error(d.message||Object.values(d.errors||{}).flat().join(' ')||'Request failed.');return d;}
 async function loadProducts(){try{const branchId=$('branch').value;const d=await api('/api/v1/products'+(branchId?'?branch_id='+encodeURIComponent(branchId):''));products=d.data||[];renderProducts();}catch(e){$('products').innerHTML='<div class="col-12 text-danger">'+esc(e.message)+'</div>';}}
 function renderProducts(){$('products').innerHTML=products.map(p=>`<div class="col-md-6"><div class="card food-card p-3"><span class="small accent">${esc(p.category?.name)}</span><h5 class="fw-bold">${esc(p.name)}</h5><p class="small text-secondary">${esc(p.description)}</p><div class="d-flex justify-content-between align-items-center"><strong>${money(p.base_price)}</strong><button class="btn btn-sm btn-fh" onclick="add(${p.id})">Add</button></div></div></div>`).join('')||'<div class="col-12 text-secondary">No products available.</div>';}
 window.add=id=>{const p=products.find(x=>x.id===id);if(!p)return;const x=cart.find(i=>i.product_id===id);if(x)x.quantity++;else cart.push({product_id:id,name:p.name,unit_price:Number(p.base_price),quantity:1});renderCart();};
 function renderCart(){const count=cart.reduce((a,x)=>a+x.quantity,0),sub=cart.reduce((a,x)=>a+x.unit_price*x.quantity,0);$('cartCount').textContent=count;$('cart').innerHTML=cart.length?cart.map((x,i)=>`<div class="d-flex justify-content-between align-items-center py-2"><div><div class="fw-semibold">${esc(x.name)}</div><small>${x.quantity} × ${money(x.unit_price)}</small></div><div class="btn-group btn-group-sm"><button class="btn btn-outline-secondary" onclick="qty(${i},-1)">−</button><button class="btn btn-outline-secondary" onclick="qty(${i},1)">+</button><button class="btn btn-outline-danger" onclick="removeItem(${i})">×</button></div></div>`).join(''):'<div class="text-secondary small py-4">Your cart is empty.</div>';if(!cart.length)$('quote').textContent='';}
 window.qty=(i,d)=>{cart[i].quantity+=d;if(cart[i].quantity<=0)cart.splice(i,1);renderCart();};window.removeItem=i=>{cart.splice(i,1);renderCart();};
 async function loadCustomerData(){branches=await api('/api/v1/branches');$('branch').innerHTML=branches.map(b=>`<option value="${b.id}">${esc(b.name)}</option>`).join('');if(token){addresses=await api('/api/v1/addresses');$('address').innerHTML=addresses.map(a=>`<option value="${a.id}">${esc(a.label)} — ${esc(a.line1)}, ${esc(a.city)}</option>`).join('')||'<option value="">Add an address first</option>';}else{$('address').innerHTML='<option value="">Sign in for delivery addresses</option>';}await loadProducts();}
 async function quote(){if(!cart.length){$('quote').textContent='Add an item first.';return;}try{const d=await api('/api/v1/checkout/quote',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({branch_id:Number($('branch').value),fulfillment_type:$('fulfillment').value,coupon:$('coupon').value.trim()||null,items:cart.map(x=>({product_id:x.product_id,quantity:x.quantity,options:[]}))})});$('quote').innerHTML=`<div class="d-flex justify-content-between"><span>Subtotal</span><span>${money(d.subtotal)}</span></div><div class="d-flex justify-content-between"><span>Discount</span><span>−${money(d.discount_total)}</span></div><div class="d-flex justify-content-between"><span>Tax</span><span>${money(d.tax_total)}</span></div><div class="d-flex justify-content-between"><span>Delivery</span><span>${money(d.delivery_fee)}</span></div><hr><div class="d-flex justify-content-between fw-bold"><span>Total</span><span>${money(d.grand_total)}</span></div>`;}catch(e){$('quote').textContent=e.message;}}
 async function checkout(){if(!token){$('orderMsg').textContent='Please sign in first.';return;}if(!cart.length){$('orderMsg').textContent='Add an item first.';return;}if($('fulfillment').value==='delivery'&&!$('address').value){$('orderMsg').textContent='Select a delivery address.';return;}$('checkoutButton').disabled=true;try{const d=await api('/api/v1/orders',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({branch_id:Number($('branch').value),address_id:$('address').value?Number($('address').value):null,fulfillment_type:$('fulfillment').value,payment_method:$('payment').value,coupon:$('coupon').value.trim()||null,items:cart.map(x=>({product_id:x.product_id,quantity:x.quantity,options:[]}))})});$('orderMsg').textContent='Order '+d.order_number+' created successfully.';$('orderMsg').className='small mt-3 text-success';cart=[];renderCart();await loadOrders();await quote();}catch(e){$('orderMsg').textContent=e.message;$('orderMsg').className='small mt-3 text-danger';}finally{$('checkoutButton').disabled=false;}}
 async function loadOrders(){if(!token){$('orders').innerHTML='<div class="text-secondary">Sign in to view orders.</div>';return;}try{const d=await api('/api/v1/orders');$('orders').innerHTML=(d.data||[]).map(o=>`<div class="border rounded-4 p-3 mb-3"><div class="d-flex justify-content-between"><strong>${esc(o.order_number)}</strong><span class="badge text-bg-light">${esc(o.status)}</span></div><div class="small text-secondary">${esc(o.branch?.name)} · ${esc(o.fulfillment_type)} · ${money(o.grand_total)}</div><div class="d-flex flex-wrap gap-1 mt-3">${(o.status_history||[]).map(h=>`<span class="badge ${h.status===o.status?'text-bg-success':'text-bg-light'}">${esc(h.status)}</span>`).join('')}</div></div>`).join('')||'<div class="text-secondary">No orders yet.</div>';}catch(e){$('orders').textContent=e.message;}}
 function logout(){fetch('/api/v1/auth/logout',{method:'POST',headers:{Accept:'application/json',Authorization:'Bearer '+token}}).catch(()=>{});clearToken();$('orders').innerHTML='<div class="text-secondary">Sign in to view orders.</div>';}
$('logoutButton').onclick=logout;$('refreshProducts').onclick=loadProducts;$('refreshOrders').onclick=loadOrders;$('quoteButton').onclick=quote;$('checkoutButton').onclick=checkout;$('branch').onchange=()=>{loadProducts();quote()};$('fulfillment').onchange=quote;
 renderCart();
 async function restoreSession(){
   if(!token){authUI(false);await loadCustomerData();await loadOrders();return;}
   try{const d=await api('/api/v1/me');if(d.role!=='customer')throw Error('Saved session belongs to another FoodHub role.');authUI(true);await loadCustomerData();await loadOrders();}
   catch(e){clearToken();authUI(false);$('msg').textContent='Please sign in with your customer account.';$('msg').className='small mt-2 text-danger';await loadCustomerData();await loadOrders();}
 }
 restoreSession();
})();
</script>
@endpush
