@extends('layouts.app')

@section('content')
<div class="hero p-4 p-lg-5 mb-5">
    <div class="row align-items-center g-4">
        <div class="col-lg-8">
            <div class="eyebrow mb-2">Rider mobile app</div>
            <h1 class="display-6 fw-bold mb-3">Rider operations are mobile-only.</h1>
            <p class="lead mb-0 opacity-75">FoodHub does not provide a browser login for riders. Use the Flutter mobile app to sign in and manage deliveries.</p>
        </div>
        <div class="col-lg-4 text-lg-end"><i class="bi bi-phone display-1 opacity-75"></i></div>
    </div>
</div>
<div class="row g-4">
    <div class="col-md-4"><div class="card food-card p-4 h-100"><div class="stat-icon mb-3"><i class="bi bi-person-check"></i></div><h5 class="fw-bold">Sign in</h5><p class="text-muted-fh mb-0">Use the FoodHub mobile app with your rider account.</p></div></div>
    <div class="col-md-4"><div class="card food-card p-4 h-100"><div class="stat-icon mb-3"><i class="bi bi-geo-alt"></i></div><h5 class="fw-bold">Live delivery tools</h5><p class="text-muted-fh mb-0">Availability, assignment actions and GPS location updates are handled from the app.</p></div></div>
    <div class="col-md-4"><div class="card food-card p-4 h-100"><div class="stat-icon mb-3"><i class="bi bi-arrow-left"></i></div><h5 class="fw-bold">Wrong portal?</h5><p class="text-muted-fh mb-3">Return to the role selector.</p><a class="btn btn-fh" href="/portal">Open FoodHub portals</a></div></div>
</div>
@endsection
