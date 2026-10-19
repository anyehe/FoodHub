@extends('layouts.app')

@section('content')
@endsection

@push('scripts')
<script>
(() => {
    const configs = {
        admin: { title:'Platform Administration', eyebrow:'Admin workspace', description:'Sign in to manage the FoodHub platform, branches, people, menu, orders and controls.', role:'admin', token:'fh_admin_token', redirect:'/admin', security:'Only administrator accounts can enter this workspace.' },
        branch: { title:'Branch Operations', eyebrow:'Branch workspace', description:'Sign in to manage your assigned branch, POS, orders and kitchen coordination.', role:'branch_manager', token:'fh_branch_token', redirect:'/branch', security:'Your account is limited to its assigned branch.' },
        kitchen: { title:'Kitchen Display', eyebrow:'Kitchen workspace', description:'Sign in to work the kitchen queue and move orders from confirmed to ready.', role:'kitchen', token:'fh_kitchen_token', redirect:'/kitchen', security:'Only kitchen accounts can enter the kitchen workspace.' },
        customer: { title:'Order from FoodHub', eyebrow:'Customer account', description:'Sign in to save addresses, place orders, view your cart and track deliveries.', role:'customer', token:'fh_customer_token', redirect:'/customer', security:'Customer sessions stay separate from every staff workspace.' }
    };
    const role = @json($role ?? 'admin');
    const cfg = configs[role] || configs.admin;
    const $ = id => document.getElementById(id);
    $('authEyebrow').textContent = cfg.eyebrow;
    $('authTitle').textContent = cfg.title;
    $('authDescription').textContent = cfg.description;
    $('authSecurityText').textContent = cfg.security;
    $('workspaceEmail').placeholder = role === 'customer' ? 'you@example.com' : role + '@yourdomain.com';

    const allTokens=['fh_customer_token','fh_admin_token','fh_branch_token','fh_kitchen_token','fh_waiter_token','fh_rider_token'];
    function clearOtherTokens(){ allTokens.filter(k=>k!==cfg.token).forEach(k=>localStorage.removeItem(k)); }
    function setMsg(message, ok=false){ $('workspaceLoginMsg').textContent=message; $('workspaceLoginMsg').className='small mt-3 '+(ok?'text-success':'text-danger'); }

    async function login(){
        const button=$('workspaceLoginButton');
        button.disabled=true;
        button.innerHTML='<span class="spinner-border spinner-border-sm me-2"></span>Signing in…';
        setMsg('');
        try{
            const r=await fetch('/api/v1/auth/login',{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json'},body:JSON.stringify({email:$('workspaceEmail').value.trim(),password:$('workspacePassword').value})});
            const d=await r.json().catch(()=>({}));
            if(!r.ok) throw new Error(d.message||Object.values(d.errors||{}).flat().join(' ')||'Unable to sign in.');
            if(d.user?.role!==cfg.role) throw new Error('This account does not have access to the '+cfg.eyebrow.toLowerCase()+'.');
            if(role==='branch' && !d.user?.branch_id) throw new Error('This branch manager has no assigned branch. Contact an administrator.');
            if(!d.token) throw new Error('The server did not return an authentication token.');
            clearOtherTokens();
            localStorage.setItem(cfg.token,d.token);
            setMsg('Signed in successfully. Opening your workspace…',true);
            window.location.replace(cfg.redirect);
        }catch(e){ setMsg(e.message||'Unable to sign in.'); button.disabled=false; button.innerHTML='<i class="bi bi-box-arrow-in-right me-2"></i>Sign in securely'; }
    }
    $('workspaceLoginForm').addEventListener('submit',e=>{e.preventDefault();login();});
    async function restore(){
        const saved=localStorage.getItem(cfg.token);
        if(!saved)return;
        try{const r=await fetch('/api/v1/me',{headers:{Authorization:'Bearer '+saved,Accept:'application/json'}});const u=await r.json().catch(()=>({}));if(r.ok&&u.role===cfg.role&&(role!=='branch'||u.branch_id))window.location.replace(cfg.redirect);}
        catch(e){localStorage.removeItem(cfg.token);}
    }
    $('workspaceEmail').focus();
    restore();
})();
</script>
@endpush
