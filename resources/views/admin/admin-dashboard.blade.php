@extends('admin.layouts.app_first', [
'title' => 'Dashboard',
'sub_title' => ''
])
@section('css')
<style type="text/css">
.table>tbody>tr:nth-child(odd) {
    background-color: #fff4ef !important;
}
</style>
@endsection
@section('breadcrumb')
<div class="breadcrumb-header">
    <div class="container-fluid">
        <h5 class="breadcrumb-line">
            <i class="bi bi-pin"></i>
            <a href="{{ route('admin.dashboard') }}">Dashboard</a>
        </h5>
    </div>
</div>
@endsection
@section('content')
<div class="row">
<div class="col-12 col-md-12 col-lg-6">
        <div class="card-body p-0">
            <div id="carouselExampleInterval" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-inner">
                    <div class="carousel-item active" data-bs-interval="10000">
                        <div class="ads_img">
                            <img src="{{ asset('public/assets/superadmin/images/default_ads_banner.jpeg') }}" alt="" />
                        </div>
                    </div>
                    <div class="carousel-item" data-bs-interval="2000">
                        <div class="ads_img">
                            <img src="{{ asset('public/assets/superadmin/images/default_ads_banner.jpeg') }}" alt="" />
                        </div>
                    </div>
                    <div class="carousel-item">
                        <div class="ads_img">
                            <img src="{{ asset('public/assets/superadmin/images/default_ads_banner.jpeg') }}" alt="" />
                        </div>
                    </div>
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleInterval"
                    data-bs-slide="prev">
                    <span class="my_prev" aria-hidden="true"></span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleInterval"
                    data-bs-slide="next">
                    <span class="my_nxt" aria-hidden="true"></span>
                </button>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-12 col-lg-6">
        <div class="card-body p-0">
            <div id="carouselExampleInterval" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-inner">
                    <div class="carousel-item active" data-bs-interval="10000">
                        <div class="ads_img">
                            <img src="{{ asset('public/assets/superadmin/images/default_ads_banner.jpeg') }}" alt="" />
                        </div>
                    </div>
                    <div class="carousel-item" data-bs-interval="2000">
                        <div class="ads_img">
                            <img src="{{ asset('public/assets/superadmin/images/default_ads_banner.jpeg') }}" alt="" />
                        </div>
                    </div>
                    <div class="carousel-item">
                        <div class="ads_img">
                            <img src="{{ asset('public/assets/superadmin/images/default_ads_banner.jpeg') }}" alt="" />
                        </div>
                    </div>
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleInterval"
                    data-bs-slide="prev">
                    <span class="my_prev" aria-hidden="true"></span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleInterval"
                    data-bs-slide="next">
                    <span class="my_nxt" aria-hidden="true"></span>
                </button>
            </div>
        </div>
    </div>
</div>
<!-- <form method="POST" action="{{ route('admin.logout') }}">
        @csrf
        <button type="submit">Logout</button>
    </form> -->
@endsection
@section('script')

@endsection