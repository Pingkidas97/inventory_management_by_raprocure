@extends('vendor.layouts.app_first')

@section('title', 'Dashboard - Raprocure')

@section('content')
<section class="container-fluid">
  <div class="row pt-2">
    <div class="col-12 col-md-6 col-lg-6 mb-3 mb-sm-0">
      <div class="card card-dashboard">
        <div class="d-flex card-header bg-graident header-container justify-content-between align-items-center border-bottom-0 py-3">
          <h6 class="card-title text-white mb-0"><a href="">Products</a></h6>
          <a href="javascript:void(0)" class="text-white font-size-13">See All Product</a>
        </div>
        <div class="card-body table-responsive p-0">
          <table class="table ra-table ra-table-stripped">
            <thead>
              <tr>
                <th scope="col">Product Name</th>
                <th scope="col">Division</th>
                <th scope="col">Category</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td><span>APRON GUIDE WITH COOLING SYSTEM</span></td>
                <td><span>CCM</span> </td>
                <td><span>EQUIPMENT</span></td>
              </tr>
              <tr>
                <td><span>CNC FLOOR TYPE RAM STYLE HORIZONTAL BOR...</span></td>
                <td><span>GENERAL</span> </td>
                <td><span>EQUIPMENT</span></td>
              </tr>
              <tr>
                <td><span>LEAF SPRINGS FOR STEAM GLANDS</span></td>
                <td><span>POWER</span> </td>
                <td><span>MECHANICAL</span></td>
              </tr>
              <tr>
                <td><span>HYDRAULIC CYLINDER FOR SLIDE GATE SYSTEM</span></td>
                <td><span>CCM</span> </td>
                <td><span>CYLINDER</span></td>
              </tr>
              <tr>
                <td><span>AMMONIA BUFFER SOLUTION</span></td>
                <td><span>GENERAL</span> </td>
                <td><span>LABORATORY</span></td>
              </tr>
              <tr>
                <td><span>AIRCRAFT LANDING GEAR</span></td>
                <td><span>CCM</span> </td>
                <td><span>SWITCHES</span></td>
              </tr>
              <tr>
                <td><span>BICYCLE FRAMES</span></td>
                <td><span>POWER</span> </td>
                <td><span>ELECTRICAL</span></td>
              </tr>
              <tr>
                <td><span>DP TEST MATERIALS</span></td>
                <td><span>GENERAL</span> </td>
                <td><span>CONSUMABLE</span></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <div class="col-12 col-md-6 col-lg-6">
      <div class="p-0">
        <div id="carouselExampleInterval" class="carousel slide carousel-dashboard" data-bs-ride="carousel">
          <div class="carousel-inner">
            <div class="carousel-item active" data-bs-interval="10000">
              <div class="ads-img">
                <img src="{{ asset('public/assets/vendor/images/slider-1.jpg') }}" alt="" />
              </div>
            </div>
            <div class="carousel-item" data-bs-interval="2000">
              <div class="ads-img">
                <img src="{{ asset('public/assets/vendor/images/slider-2.png') }}" alt="" />
              </div>
            </div>
          </div>
          <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleInterval"
            data-bs-slide="prev">
            <span class="btn-prev-slider" aria-hidden="true"></span>
          </button>
          <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleInterval"
            data-bs-slide="next">
            <span class="btn-next-slider" aria-hidden="true"></span>
          </button>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection