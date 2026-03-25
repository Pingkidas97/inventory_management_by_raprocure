@extends('buyer.layouts.app', ['title'=>'Buyer Profile', 'sub_title'=>'Create'])

@section('css')
@endsection

@section('content')
    <div class="card">
        <div class="order_confirm">
            <div class="raprosvg-container">
                <img src="{{ asset('public/assets/images/raprocure-fevicon.png') }}" alt="">
            </div>
            <div class="order_confirm_head text-center">
                <h3>Buyer Profile Status </h3>
                <p>Thank you for registering with raProcure. Your profile is currently under verification.</p>
                <p> You will receive a message on your registered email, once your account is verified</p>
                <a href="{{ route("buyer.profile") }}" class="btn-rfq btn-rfq-primary profile_edit_btn">Edit Profile</a>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
@endsection