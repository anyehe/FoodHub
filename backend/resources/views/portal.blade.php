@extends('layouts.app')

@section('content')
<style>
.portal-landing{min-height:calc(100vh - 48px);padding:28px 0 54px}
.portal-landing .landing-wrap{background:#fff;border:1px solid #e2eeee;border-radius:32px;overflow:hidden;box-shadow:0 24px 70px rgba(0,49,53,.10)}
.portal-landing .landing-hero{position:relative;overflow:hidden;background:linear-gradient(135deg,#003135 0%,#024950 62%,#087d85 100%);color:#fff;padding:52px 48px 44px}
.portal-landing .landing-hero:before,.portal-landing .landing-hero:after{content:'';position:absolute;border-radius:50%;pointer-events:none}
.portal-landing .landing-hero:before{width:360px;height:360px;right:-130px;top:-190px;background:rgba(175,221,229,.13)}
.portal-landing .landing-hero:after{width:230px;height:230px;left:48%;bottom:-180px;background:rgba(150,71,52,.24)}
.portal-landing .hero-content{position:relative;z-index:1;max-width:850px}
.portal-landing .hero-brand{display:inline-flex;align-items:center;gap:12px;color:#fff;text-decoration:none;margin-bottom:26px}
.portal-landing .hero-brand .logo-mark{width:48px;height:48px;border-radius:15px;background:rgba(15,164,175,.95);box-shadow:0 12px 28px rgba(0,0,0,.15)}
.portal-landing .hero-brand strong{font-family:'Plus Jakarta Sans',sans-serif;font-size:1.35rem;letter-spacing:-.5px}
.portal-landing .hero-eyebrow{display:inline-flex;align-items:center;gap:8px;padding:7px 11px;border:1px solid rgba(175,221,229,.24);background:rgba(255,255,255,.07);border-radius:999px;color:#d9f4f5;font-size:.74rem;font-weight:800;text-transform:uppercase;letter-spacing:.13em}
.portal-landing .hero-title{font-family:'Plus Jakarta Sans',sans-serif;font-size:clamp(2.25rem,5vw,4.2rem);line-height:1.02;letter-spacing:-2.4px;font-weight:800;margin:18px 0 15px;max-width:850px}
.portal-landing .hero-title span{color:#8fe2e6}
.portal-landing .hero-copy{font-size:1.05rem;line-height:1.7;color:rgba(255,255,255,.78);max-width:760px;margin:0}
.portal-landing .hero-actions{display:flex;flex-wrap:wrap;gap:10px;margin-top:28px}
.portal-landing .hero-actions .btn{padding:.78rem 1.05rem;border-radius:13px;font-weight:800}
.portal-landing .hero-actions .btn-light{color:var(--fh1)}
.portal-landing .hero-meta{display:flex;flex-wrap:wrap;gap:9px;margin-top:22px}
.portal-landing .hero-meta span{display:inline-flex;align-items:center;gap:6px;padding:7px 10px;border-radius:999px;background:rgba(255,255,255,.07);color:rgba(255,255,255,.78);font-size:.78rem}
.portal-landing .workspace-area{padding:34px 38px 38px;background:linear-gradient(180deg,#f8fcfc,#f3f9fa)}
.portal-landing .section-kicker{text-transform:uppercase;letter-spacing:.13em;font-size:.72rem;font-weight:800;color:var(--fh4)}
.portal-landing .section-title-lg{font-family:'Plus Jakarta Sans',sans-serif;font-weight:800;letter-spacing:-1px;color:var(--fh1)}
.portal-landing .workspace-card{height:100%;display:flex;flex-direction:column;background:#fff;border:1px solid #dfebec;border-radius:22px;padding:22px;box-shadow:0 10px 30px rgba(0,49,53,.055);text-decoration:none;color:var(--ink);transition:transform .18s ease,box-shadow .18s ease,border-color .18s ease}
.portal-landing .workspace-card:hover{transform:translateY(-5px);box-shadow:0 20px 42px rgba(0,49,53,.12);border-color:#b9dfe2}
.portal-landing .workspace-icon{width:50px;height:50px;border-radius:15px;display:flex;align-items:center;justify-content:center;background:#e7f7f8;color:var(--fh2);font-size:1.3rem;margin-bottom:18px}
.portal-landing .workspace-card.admin .workspace-icon{background:#e7f4f4;color:#003135}.portal-landing .workspace-card.branch .workspace-icon{background:#fff0eb;color:#964734}.portal-landing .workspace-card.kitchen .workspace-icon{background:#fff4e7;color:#b66b13}.portal-landing .workspace-card.customer .workspace-icon{background:#e8f6ff;color:#176c9b}
.portal-landing .workspace-name{font-family:'Plus Jakarta Sans',sans-serif;font-weight:800;font-size:1.05rem;margin-bottom:7px}.portal-landing .workspace-copy{font-size:.9rem;color:var(--muted);line-height:1.55;margin-bottom:20px}.portal-landing .workspace-link{margin-top:auto;display:flex;align-items:center;justify-content:space-between;font-size:.84rem;font-weight:800;color:var(--fh2)}
.portal-landing .workspace-card .badge-role{display:inline-flex;align-self:flex-start;padding:5px 8px;border-radius:999px;background:#f0f7f7;color:#5d777a;font-size:.68rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em;margin-bottom:12px}
.portal-landing .bottom-strip{margin-top:20px;padding:18px 20px;background:#003135;color:#fff;border-radius:18px;display:flex;align-items:center;justify-content:space-between;gap:20px}.portal-landing .bottom-strip .small{color:rgba(255,255,255,.72)}
@media(max-width:767px){.portal-landing{padding:14px 0 35px}.portal-landing .landing-hero{padding:34px 24px 32px}.portal-landing .hero-title{letter-spacing:-1.5px}.portal-landing .workspace-area{padding:26px 18px}.portal-landing .bottom-strip{align-items:flex-start;flex-direction:column}.portal-landing .hero-actions .btn{width:100%}}
</style>

<div class="portal-landing">
    <div class="landing-wrap">
        <section class="landing-hero">
            <div class="hero-content">
                <a class="hero-brand" href="/"><span class="logo-mark"><i class="bi bi-fork-knife"></i></span><strong>Food<span style="color:var(--fh4)">Hub</span></strong></a>
                <div class="hero-eyebrow"><i class="bi bi-shield-check"></i> Secure workspace access</div>
                <h1 class="hero-title">Everything FoodHub, <span>in the right workspace.</span></h1>
                <p class="hero-copy">Choose where you work. Customer ordering, administration, branch operations and kitchen production each have their own focused FoodHub workspace.</p>
                <div class="hero-actions">
                    <a class="btn btn-light" href="/login/customer"><i class="bi bi-bag-heart me-2"></i>Order as a customer</a>
                    <a class="btn btn-outline-light" href="/login/admin"><i class="bi bi-grid-1x2 me-2"></i>Open Admin</a>
                </div>
                <div class="hero-meta">
                    <span><i class="bi bi-lock-fill"></i> Role-based access</span>
                    <span><i class="bi bi-arrow-repeat"></i> Separate sessions</span>
                    <span><i class="bi bi-phone"></i> Mobile rider app</span>
                </div>
            </div>
        </section>

        <section class="workspace-area">
            <div class="d-flex flex-column flex-lg-row align-items-lg-end justify-content-between gap-3 mb-4">
                <div>
                    <div class="section-kicker mb-2">Choose your workspace</div>
                    <h2 class="section-title-lg h3 mb-2">Where would you like to sign in?</h2>
                    <p class="text-muted-fh mb-0">Your account will only open the workspace assigned to its role.</p>
                </div>
                <a href="/" class="btn btn-outline-fh"><i class="bi bi-house me-2"></i>Back to FoodHub</a>
            </div>

            <div class="row g-3">
                <div class="col-md-6 col-xl-3">
                    <a href="/login/customer" class="workspace-card customer">
                        <span class="badge-role">Customer</span>
                        <span class="workspace-icon"><i class="bi bi-person-heart"></i></span>
                        <div class="workspace-name">Customer Ordering</div>
                        <p class="workspace-copy">Browse food, manage your cart, checkout and track your deliveries.</p>
                        <span class="workspace-link">Sign in <i class="bi bi-arrow-right"></i></span>
                    </a>
                </div>
                <div class="col-md-6 col-xl-3">
                    <a href="/login/admin" class="workspace-card admin">
                        <span class="badge-role">Platform</span>
                        <span class="workspace-icon"><i class="bi bi-shield-lock"></i></span>
                        <div class="workspace-name">Admin Control Center</div>
                        <p class="workspace-copy">Manage branches, people, menus, orders, inventory, promotions and reports.</p>
                        <span class="workspace-link">Sign in <i class="bi bi-arrow-right"></i></span>
                    </a>
                </div>
                <div class="col-md-6 col-xl-3">
                    <a href="/login/branch" class="workspace-card branch">
                        <span class="badge-role">Operations</span>
                        <span class="workspace-icon"><i class="bi bi-building"></i></span>
                        <div class="workspace-name">Branch Operations</div>
                        <p class="workspace-copy">Run branch orders, POS, kitchen coordination and assigned operations.</p>
                        <span class="workspace-link">Sign in <i class="bi bi-arrow-right"></i></span>
                    </a>
                </div>
                <div class="col-md-6 col-xl-3">
                    <a href="/login/kitchen" class="workspace-card kitchen">
                        <span class="badge-role">Production</span>
                        <span class="workspace-icon"><i class="bi bi-fire"></i></span>
                        <div class="workspace-name">Kitchen Display</div>
                        <p class="workspace-copy">Work the kitchen queue from confirmed orders through preparation to ready.</p>
                        <span class="workspace-link">Sign in <i class="bi bi-arrow-right"></i></span>
                    </a>
                </div>
            </div>

            <div class="bottom-strip">
                <div class="d-flex align-items-start gap-3">
                    <i class="bi bi-phone fs-4"></i>
                    <div><strong>Rider workspace is mobile-first.</strong><div class="small mt-1">Riders use the FoodHub mobile app for availability, assignments, GPS updates and delivery completion.</div></div>
                </div>
                <span class="badge rounded-pill text-bg-light text-dark px-3 py-2">Rider Web Login Disabled</span>
            </div>
        </section>
    </div>
</div>
@endsection
