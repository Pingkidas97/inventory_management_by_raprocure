@extends('buyer.layouts.app', ['title'=>'Change Password'])

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
            <div class="row">
                <div class="col-12 col-sm-7 col-md-6 col-lg-5 col-xl-4 mx-auto">

                    <div class="card change-password rounded-4 overflow-hidden mt-md-5 mb-0">
                        <div class="card-header bg-white py-3 py-md-4 px-md-4 px-lg-5">
                            <h1 class="font-size-16 mb-0 text-uppercase text-deep-blue">Change Password</h1>
                        </div>
                        <div class="card-body py-4 px-md-4 px-lg-5">
                            <div class="mb-3">
                                <label>Current Passwsord<span class="text-danger"> * </span></label>
                                <div class="password-type-input">
                                    <input type="password" class="form-control pe-5" placeholder="Current Passwsord"
                                        value="" name="old_password" id="currentpassword">
                                    <span class="bi bi-eye-slash" id="passwordEye1"
                                        onclick="passwordHideShow('currentpassword','passwordEye1')"></span>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label>Change Password<span class="text-danger"> * </span></label>
                                <div class="password-type-input">
                                    <input type="password" class="form-control pe-5" placeholder="Change Password"
                                        value="" name="Change Password" id="changepassword">
                                    <span class="bi bi-eye-slash" id="passwordEye2"
                                        onclick="passwordHideShow('changepassword','passwordEye2')"></span>
                                </div>
                                <span class="error-msg">Password must be minimum 8 characters</span>
                            </div>
                            <div class="mb-3">
                                <label>Confirm Passwsord<span class="text-danger"> * </span></label>
                                <div class="password-type-input">
                                    <input type="password" class="form-control pe-5" placeholder="Confirm Passwsord"
                                        value="" name="Confirm Passwsord" id="confirmpassword">
                                    <span class="bi bi-eye-slash" id="passwordEye3"
                                        onclick="passwordHideShow('confirmpassword','passwordEye3')"></span>
                                </div>
                            </div>
                            <div class="ms-auto d-table">
                                <button type="submit" class="ra-btn small-btn ra-btn-primary">
                                    Submit
                                </button>
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