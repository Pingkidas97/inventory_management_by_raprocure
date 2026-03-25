@extends('vendor.layouts.app', ['title'=>'Vendor Profile', 'sub_title'=>'Create'])

@section('css')
@endsection

@section('content')

    <div class="bg-white mt-10">
        <div class="order_confirm">
            <div class="raprosvg-container">
                <img src="{{ asset('public/assets/images/raprocure-fevicon.png') }}">
            </div>
            <div class="order_confirm_head text-center animated growIn go">
                <h3>Vendor Profile Status </h3>
                <p>Thank you for registering with raProcure. Your profile is currently under verification.</p>
                <p> You will receive a message on your registered email, once your account is verified</p>
                <a href="{{ route("vendor.profile") }}" class="btn-rfq btn-rfq-primary profile_edit_btn">Edit Profile</a>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
@endsection