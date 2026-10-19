@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-end mb-4">
    <div>
        <div class="text-uppercase small accent fw-bold">Branch operations</div>
        <h1 class="fw-bold mb-0">Branch Manager Portal</h1>
        <div class="text-secondary">Manage your assigned branch operations without entering the admin or customer areas.</div>
    </div>

    <div class="d-flex gap-2">
        <button class="btn btn-outline-secondary d-none" id="logoutBtn" type="button">
            <i class="bi bi-box-arrow-right me-1"></i> Sign out
        </button>
        <button class="btn btn-fh" id="refreshBtn" type="button">
            <i class="bi bi-arrow-clockwise me-1"></i> Refresh
        </button>
    </div>
</div>

<div id="branchWorkspace" class="d-none mb-4">
    <div class="d-flex flex-wrap gap-2">
        <button class="btn btn-fh branch-nav active" data-panel="branchOverview"><i class="bi bi-grid-1x2 me-1"></i>Overview</button>
        <button class="btn btn-outline-fh branch-nav" data-panel="branchPos"><i class="bi bi-calculator me-1"></i>POS</button>
        <button class="btn btn-outline-fh branch-nav" data-panel="branchOrders"><i class="bi bi-receipt me-1"></i>Orders</button>
        <button class="btn btn-outline-fh branch-nav" data-panel="branchKitchen"><i class="bi bi-fire me-1"></i>Kitchen</button>
    </div>
</div>

<div id="branchOverview" class="branch-panel">
<div id="stats" class="row g-3 mb-4"></div>

<div class="card food-card p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div><h5 class="fw-bold mb-0">Recent Orders</h5><small class="text-secondary">Live orders for your branch or operations scope.</small></div>
        <div class="d-flex gap-2"><a class="btn btn-sm btn-outline-fh" href="/kitchen">Kitchen</a><a class="btn btn-sm btn-fh" href="/portal">Portals</a></div>
    </div>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead><tr><th>Order</th><th>Customer</th><th>Status</th><th>Total</th></tr></thead>
            <tbody id="recentOrdersBody"><tr><td colspan="4" class="text-secondary">No orders loaded.</td></tr></tbody>
        </table>
    </div>
</div>

<div class="card food-card p-4">
    <div class="d-flex justify-content-between align-items-center">
        <h5 class="fw-bold mb-0">Live API Status</h5>
        <span id="apiStatus" class="badge text-bg-secondary">Not connected</span>
    </div>

    <pre id="raw" class="bg-light p-3 rounded mt-3 mb-0" style="min-height:120px">Not authenticated.</pre>
</div>
</div>

<div id="branchOrders" class="branch-panel d-none">
    <div class="card food-card p-4"><div class="d-flex justify-content-between align-items-center mb-3"><div><h5 class="fw-bold mb-0">Branch Orders</h5><small class="text-secondary">Orders visible only to this assigned branch.</small></div><button class="btn btn-fh btn-sm" id="branchOrdersRefresh">Refresh</button></div><div class="table-responsive"><table class="table align-middle"><thead><tr><th>Order</th><th>Customer</th><th>Status</th><th>Total</th></tr></thead><tbody id="branchOrdersBody"><tr><td colspan="4" class="text-secondary">Sign in to load orders.</td></tr></tbody></table></div></div>
</div>

<div id="branchKitchen" class="branch-panel d-none">
    <div class="card food-card p-4"><div class="d-flex justify-content-between align-items-center mb-3"><div><h5 class="fw-bold mb-0">Kitchen Coordination</h5><small class="text-secondary">Kitchen has its own secure workspace; this view is for branch coordination.</small></div><a class="btn btn-outline-fh btn-sm" href="/kitchen">Open Kitchen Workspace</a></div><div class="alert alert-info border-0 rounded-4 mb-0">Kitchen staff should work from the dedicated Kitchen portal. Branch managers can monitor branch orders here without sharing the kitchen session.</div></div>
</div>

<div id="branchPos" class="branch-panel d-none">
    <div class="row g-4">
        <div class="col-xl-8"><div class="card food-card p-4"><div class="d-flex justify-content-between align-items-center mb-3"><div><div class="text-uppercase small accent fw-bold">Branch POS</div><h5 class="fw-bold mb-0">Walk-in & Dine-in Sales</h5><small id="posBranchName" class="text-secondary"></small></div><button class="btn btn-sm btn-outline-fh" id="posRefresh">Refresh menu</button></div><div id="posProducts" class="row g-3"></div></div></div>
        <div class="col-xl-4"><div class="card food-card p-4 sticky-cart"><h5 class="fw-bold">POS Cart</h5><div id="posCart" class="py-2"></div><hr><select id="posPayment" class="form-select"><option value="cash">Cash</option><option value="gcash">GCash</option><option value="card">Card</option></select><button id="posPlace" class="btn btn-fh w-100 mt-3">Create POS order</button><div id="posMsg" class="small mt-3"></div></div></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(() => {
    const logoutBtn = document.getElementById('logoutBtn');
    const refreshBtn = document.getElementById('refreshBtn');
    const apiStatus = document.getElementById('apiStatus');
    const raw = document.getElementById('raw');
    const stats = document.getElementById('stats');

    let token = localStorage.getItem('fh_branch_token') || '';
    // Remove the legacy shared token key used by older builds.
    localStorage.removeItem('fh_token');

    function setMessage(message, success = false) {
        loginMsg.textContent = message;
        loginMsg.className = 'small mt-3 ' + (success ? 'text-success' : 'text-danger');
    }

    function setAuthenticatedUI(authenticated) {
        document.getElementById('branchWorkspace').classList.toggle('d-none', !authenticated);
        logoutBtn.classList.toggle('d-none', !authenticated);
    }

    function clearToken() {
        token = '';
        localStorage.removeItem('fh_branch_token');
        setAuthenticatedUI(false);
        apiStatus.className = 'badge text-bg-secondary';
        apiStatus.textContent = 'Not connected';
        raw.textContent = 'Please sign in.';
        stats.innerHTML = '';
    }

    async function restoreSession() {
        if (!token) { window.location.replace('/login/branch'); return; }

        try {
            const response = await fetch('/api/v1/me', {
                headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
            });
            const data = await response.json().catch(() => ({}));
            if (!response.ok || data.role !== 'branch_manager' || !data.branch_id) {
                throw new Error('Saved branch manager session is invalid.');
            }
            setAuthenticatedUI(true);
            await loadDashboard();
        } catch (error) {
            clearToken();
            window.location.replace('/login/branch');
        }
    }

    async function loadDashboard() {
        if (!token) {
            window.location.replace('/login/branch');
            return;
        }

        try {
            const response = await fetch('/api/v1/staff/dashboard', {
                method: 'GET',
                headers: {
                    'Authorization': 'Bearer ' + token,
                    'Accept': 'application/json'
                }
            });

            const data = await response.json().catch(() => ({}));

            if (response.status === 401) {
                clearToken();
                apiStatus.className = 'badge text-bg-danger';
                apiStatus.textContent = 'Session expired';
                raw.textContent = JSON.stringify(data, null, 2);
                stats.innerHTML = '';
                setMessage('Your session has expired. Please sign in again.');
                return;
            }

            if (response.status === 403) {
                apiStatus.className = 'badge text-bg-danger';
                apiStatus.textContent = 'Access denied';
                raw.textContent = JSON.stringify(data, null, 2);
                stats.innerHTML = '';
                setAuthenticatedUI(true);
                return;
            }

            apiStatus.className = 'badge ' + (response.ok ? 'text-bg-success' : 'text-bg-danger');
            apiStatus.textContent = response.ok ? 'API connected' : 'API error';
            raw.textContent = JSON.stringify(data, null, 2);

            if (!response.ok) {
                stats.innerHTML = '';
                return;
            }

            renderStats(data);
            renderRecentOrders(data.recent_orders || []);
        } catch (error) {
            console.error(error);
            apiStatus.className = 'badge text-bg-danger';
            apiStatus.textContent = 'Connection error';
            raw.textContent = error.toString();
        }
    }

    function renderStats(data) {
        const entries = Object.entries(data).filter(([key]) => key !== 'recent_orders');

        stats.innerHTML = entries.map(([key, value]) => {
            const isRevenue = typeof value === 'number' && key.toLowerCase().includes('revenue');
            const displayValue = isRevenue
                ? '₱' + Number(value).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })
                : value;

            return `
                <div class="col-md-4 col-lg">
                    <div class="card stat p-3 h-100">
                        <div class="small text-secondary text-uppercase">${key.replaceAll('_', ' ')}</div>
                        <div class="fs-3 fw-bold">${displayValue}</div>
                    </div>
                </div>`;
        }).join('');
    }

    function renderRecentOrders(orders) {
        const body = document.getElementById('recentOrdersBody');
        if (!orders.length) { body.innerHTML = '<tr><td colspan="4" class="text-secondary">No orders yet.</td></tr>'; return; }
        body.innerHTML = orders.map(order => `
            <tr>
                <td><strong>${order.order_number || ''}</strong><div class="small text-secondary">${order.fulfillment_type || ''}</div></td>
                <td>${order.customer?.name || 'Customer'}</td>
                <td><span class="badge text-bg-light">${String(order.status || '').replaceAll('_',' ')}</span></td>
                <td>₱${Number(order.grand_total || 0).toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2})}</td>
            </tr>`).join('');
    }

    async function logout() {
        if (token) {
            try {
                await fetch('/api/v1/auth/logout', {
                    method: 'POST',
                    headers: {
                        'Authorization': 'Bearer ' + token,
                        'Accept': 'application/json'
                    }
                });
            } catch (error) {
                console.warn('Logout request failed; clearing local token anyway.', error);
            }
        }

        clearToken();
        stats.innerHTML = '';
        raw.textContent = 'Not authenticated.';
        apiStatus.className = 'badge text-bg-secondary';
        apiStatus.textContent = 'Not connected';
        setMessage('Signed out.', true);
    }

    let posProducts = [];
    let posCart = [];
    let currentBranchId = null;

    function showBranchPanel(panelId) {
        document.querySelectorAll('.branch-panel').forEach(panel => panel.classList.add('d-none'));
        const target = document.getElementById(panelId);
        if (target) target.classList.remove('d-none');
        document.querySelectorAll('.branch-nav').forEach(btn => {
            const active = btn.dataset.panel === panelId;
            btn.classList.toggle('active', active);
            btn.classList.toggle('btn-fh', active);
            btn.classList.toggle('btn-outline-fh', !active);
        });
        if (panelId === 'branchPos' && token) loadPosProducts();
        if (panelId === 'branchOrders' && token) loadBranchOrders();
    }

    document.querySelectorAll('.branch-nav').forEach(btn => btn.addEventListener('click', () => showBranchPanel(btn.dataset.panel)));

    async function loadPosProducts() {
        if (!token) return;
        try {
            const me = await fetch('/api/v1/me', {headers:{Authorization:'Bearer '+token,Accept:'application/json'}}).then(r=>r.json());
            if (me.role !== 'branch_manager' || !me.branch_id) throw new Error('Invalid branch session.');
            currentBranchId = me.branch_id;
            document.getElementById('posBranchName').textContent = me.branch?.name || 'Assigned branch';
            const r = await fetch('/api/v1/products?branch_id='+encodeURIComponent(me.branch_id), {headers:{Authorization:'Bearer '+token,Accept:'application/json'}});
            const d = await r.json();
            if (!r.ok) throw new Error(d.message || 'Unable to load POS menu.');
            posProducts = d.data || d || [];
            document.getElementById('posProducts').innerHTML = posProducts.map(p => `<div class="col-md-6 col-lg-4"><div class="card border-0 bg-light rounded-4 p-3 h-100"><span class="small accent">${escapeHtml(p.category?.name || 'Menu')}</span><h6 class="fw-bold mt-1">${escapeHtml(p.name)}</h6><div class="d-flex justify-content-between align-items-center mt-3"><strong>₱${Number(p.base_price||0).toFixed(2)}</strong><button class="btn btn-sm btn-fh" onclick="addPos(${p.id})">Add</button></div></div></div>`).join('') || '<div class="col-12 text-secondary">No products available.</div>';
        } catch (e) { document.getElementById('posProducts').innerHTML = `<div class="col-12 text-danger">${escapeHtml(e.message)}</div>`; }
    }

    function escapeHtml(v){return String(v??'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));}
    function renderPosCart(){
        const el=document.getElementById('posCart');
        el.innerHTML=posCart.length?posCart.map((x,i)=>`<div class="d-flex justify-content-between align-items-center py-2"><div><strong>${escapeHtml(x.name)}</strong><div class="small text-secondary">${x.quantity} × ₱${Number(x.unit_price).toFixed(2)}</div></div><div class="btn-group btn-group-sm"><button class="btn btn-outline-secondary" onclick="posQty(${i},-1)">−</button><button class="btn btn-outline-secondary" onclick="posQty(${i},1)">+</button></div></div>`).join('')+`<hr><div class="d-flex justify-content-between fw-bold"><span>Total</span><span>₱${posCart.reduce((a,x)=>a+x.unit_price*x.quantity,0).toFixed(2)}</span></div>`:'<div class="text-secondary py-4">Cart is empty.</div>';
    }
    window.addPos=id=>{const p=posProducts.find(x=>x.id===id);if(!p)return;const x=posCart.find(x=>x.product_id===id);if(x)x.quantity++;else posCart.push({product_id:id,name:p.name,unit_price:Number(p.base_price),quantity:1});renderPosCart();};
    window.posQty=(i,d)=>{posCart[i].quantity+=d;if(posCart[i].quantity<1)posCart.splice(i,1);renderPosCart();};
    async function placePos(){if(!posCart.length)return;const b=document.getElementById('posPlace');b.disabled=true;try{const r=await fetch('/api/v1/pos/orders',{method:'POST',headers:{Authorization:'Bearer '+token,Accept:'application/json','Content-Type':'application/json'},body:JSON.stringify({branch_id:currentBranchId,items:posCart.map(x=>({product_id:x.product_id,quantity:x.quantity,options:[]})),payment_method:document.getElementById('posPayment').value})});const d=await r.json();if(!r.ok)throw new Error(d.message||'Unable to create POS order.');document.getElementById('posMsg').textContent='POS order '+d.order_number+' created.';document.getElementById('posMsg').className='small mt-3 text-success';posCart=[];renderPosCart();loadDashboard();}catch(e){document.getElementById('posMsg').textContent=e.message;document.getElementById('posMsg').className='small mt-3 text-danger';}finally{b.disabled=false;}}
    document.getElementById('posPlace').addEventListener('click',placePos);
    document.getElementById('posRefresh').addEventListener('click',loadPosProducts);
    document.getElementById('branchOrdersRefresh').addEventListener('click',loadBranchOrders);
    async function loadBranchOrders(){try{const r=await fetch('/api/v1/staff/orders',{headers:{Authorization:'Bearer '+token,Accept:'application/json'}});const d=await r.json();if(!r.ok)throw new Error(d.message||'Unable to load orders.');const body=document.getElementById('branchOrdersBody');body.innerHTML=(d.data||[]).map(o=>`<tr><td><strong>${escapeHtml(o.order_number)}</strong></td><td>${escapeHtml(o.customer?.name||'Customer')}</td><td><span class="badge text-bg-light">${escapeHtml(o.status)}</span></td><td>₱${Number(o.grand_total||0).toFixed(2)}</td></tr>`).join('')||'<tr><td colspan="4" class="text-secondary">No orders.</td></tr>';}catch(e){document.getElementById('branchOrdersBody').innerHTML=`<tr><td colspan="4" class="text-danger">${escapeHtml(e.message)}</td></tr>`;}}
    renderPosCart();
    const hashPanel = ({pos:'branchPos',orders:'branchOrders',kitchen:'branchKitchen'}[location.hash.replace('#','')] || 'branchOverview');
    showBranchPanel(hashPanel);

    refreshBtn.addEventListener('click', loadDashboard);
    logoutBtn.addEventListener('click', logout);

    if (token) {
        setAuthenticatedUI(true);
        loadDashboard();
    } else {
        setAuthenticatedUI(false);
    }
    restoreSession();
})();
</script>
@endpush
