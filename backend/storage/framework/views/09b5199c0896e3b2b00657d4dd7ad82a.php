<?php $__env->startSection('content'); ?>
<div class="hero p-4 p-lg-5 mb-5">
    <div class="row align-items-center g-4">
        <div class="col-lg-8">
            <div class="eyebrow mb-2">FoodHub workspaces</div>
            <h1 class="display-5 fw-bold mb-3">Sign in to the workspace built for your role.</h1>
            <p class="lead mb-0 opacity-75">The customer website, platform administration, branch operations/POS and kitchen display are separate workspaces. Each role signs in only to the workspace it needs; riders use the mobile app.</p>
        </div>
        <div class="col-lg-4 text-lg-end">
            <a class="btn btn-light btn-lg rounded-4 fw-bold" href="/login/customer"><i class="bi bi-bag-heart me-2"></i>Customer sign in</a>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-6 col-xl-4">
        <a href="/login/customer" class="text-decoration-none text-dark">
            <div class="card food-card p-4 h-100 hoverable">
                <span class="chip mb-3 align-self-start"><i class="bi bi-person-heart me-1"></i>Customer</span>
                <h4 class="section-title">Customer Ordering</h4>
                <p class="text-muted-fh">Browse food, keep your cart, checkout, view order history and track your delivery.</p>
                <span class="btn btn-fh mt-auto">Customer sign in <i class="bi bi-arrow-right ms-1"></i></span>
            </div>
        </a>
    </div>
    <div class="col-md-6 col-xl-4">
        <a href="/login/admin" class="text-decoration-none text-dark">
            <div class="card food-card p-4 h-100 hoverable">
                <span class="chip mb-3 align-self-start"><i class="bi bi-shield-lock me-1"></i>Platform</span>
                <h4 class="section-title">Super Admin</h4>
                <p class="text-muted-fh">Manage branches, people, menu, orders, inventory, tables, coupons, reports and platform controls.</p>
                <span class="btn btn-outline-fh mt-auto">Admin sign in <i class="bi bi-arrow-right ms-1"></i></span>
            </div>
        </a>
    </div>
    <div class="col-md-6 col-xl-4">
        <a href="/login/branch" class="text-decoration-none text-dark">
            <div class="card food-card p-4 h-100 hoverable">
                <span class="chip mb-3 align-self-start"><i class="bi bi-building me-1"></i>Branch</span>
                <h4 class="section-title">Branch Manager</h4>
                <p class="text-muted-fh">See only your assigned branch operations, orders, reports and coordination tools.</p>
                <span class="btn btn-outline-fh mt-auto">Branch sign in <i class="bi bi-arrow-right ms-1"></i></span>
            </div>
        </a>
    </div>
    <div class="col-md-6 col-xl-4">
        <a href="/login/kitchen" class="text-decoration-none text-dark">
            <div class="card food-card p-4 h-100 hoverable">
                <span class="chip mb-3 align-self-start"><i class="bi bi-fire me-1"></i>Kitchen</span>
                <h4 class="section-title">Kitchen Display</h4>
                <p class="text-muted-fh">Work the kitchen queue: confirm, prepare and mark orders ready.</p>
                <span class="btn btn-outline-fh mt-auto">Kitchen sign in <i class="bi bi-arrow-right ms-1"></i></span>
            </div>
        </a>
    </div>
    <div class="col-md-6 col-xl-4">
        <div class="card food-card p-4 h-100 hoverable">
            <span class="chip mb-3 align-self-start"><i class="bi bi-calculator me-1"></i>Branch workspace</span>
            <h4 class="section-title">Branch POS</h4>
            <p class="text-muted-fh">POS is intentionally part of the branch website. A branch manager can open the POS inside the same authenticated branch workspace without a second staff login.</p>
            <a href="/login/branch" class="btn btn-outline-fh mt-auto">Open Branch + POS <i class="bi bi-arrow-right ms-1"></i></a>
        </div>
    </div>
    <div class="col-md-6 col-xl-4">
        <div class="card food-card p-4 h-100 border border-2" style="border-color:var(--fh5)!important">
            <span class="chip mb-3 align-self-start"><i class="bi bi-phone me-1"></i>Mobile only</span>
            <h4 class="section-title">Rider App</h4>
            <p class="text-muted-fh">Riders do not use the FoodHub web portal. Sign in through the FoodHub mobile app for availability, assignments, GPS updates and delivery completion.</p>
            <div class="alert alert-info border-0 rounded-4 mb-0"><i class="bi bi-info-circle me-1"></i>Rider web login is intentionally disabled.</div>
        </div>
    </div>
</div>

<div class="card food-card p-4 mt-5">
    <div class="row align-items-center g-3">
        <div class="col-auto"><div class="stat-icon"><i class="bi bi-lock"></i></div></div>
        <div class="col">
            <h5 class="fw-bold mb-1">Separate sessions</h5>
            <p class="text-muted-fh mb-0">Each workspace stores its own browser session key and validates the signed-in role before showing its dashboard. Customer cart and orders stay in the customer area instead of appearing inside staff workspaces.</p>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\FoodHub-Advanced-Portal-Login-Redesign-Fixed2\backend\resources\views/portal.blade.php ENDPATH**/ ?>