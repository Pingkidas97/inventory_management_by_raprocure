@extends('buyer.layouts.app', ['title'=> 'RFQ Sent Successfully', 'sub_title'=>'Create'])

@section('css')
@endsection

@section('content')
    <div class="bg-white">
        <!---Sidebar-->
        @include('buyer.layouts.sidebar-menu')
    </div>

    <!---Section Main-->
    <main class="main flex-grow-1">
        <div class="container-fluid">
            <div class="card">
                <div class="card-body">
                    <div class="row mt-5">
                        <div class="col-md-12 text-center mt-5">
                            <img src="{{ asset('public/assets/images/raprocure-fevicon.png') }}" style="width: 8%;" class="img img-fluid">
                            <h3 style="font-weight: 600;">RFQ No. {{$rfq_number}} Sent Successfully to all your Vendors</h3>
                            <h5 class="mt-2" style="font-weight: 600;"> Exclusive CIS is getting prepared.</h5>
                        </div>
                        <div class="col-md-12 text-center">
                            <div class="d-flex flex-wrap flex-md-nowrap align-items-center justify-content-center gap-3 mt-5 mb-5">
                                <a href="{{ route("buyer.rfq.active-rfq") }}" class="ra-btn btn-outline-primary ra-btn-outline-primary-light text-uppercase text-nowrap font-size-10"><i class="bi bi-file-earmark-play"></i> View All RFQ</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>    
@endsection

@section('scripts')
@endsection