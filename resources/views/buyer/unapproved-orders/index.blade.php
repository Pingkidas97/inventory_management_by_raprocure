@extends('buyer.layouts.app', ['title'=>'Manage Role'])

@section('css')
@endsection

@section('content')
    <div class="bg-white">
        <!---Sidebar-->
        @include('buyer.layouts.sidebar')
    </div>

    <!---Section Main-->
    <main class="main flex-grow-1 inner-main">
        <div class="container-fluid">
            <div class="bg-white favourite-vendor user-manage-role">
                <div class="card-head-line">
                    <h3>Manage Role</h3>
                    <a href="{{route('buyer.role-permission.create-role')}}" class="btn ra-btn ra-btn-primary small-btn">+ Add New Role</a>
                </div>
                <div class="table-responsive">
                    <table class="product-listing-table w-100">
                        <thead>
                            <tr>
                                <th>Sr.No.</th>
                                <th width="700">ROLE NAME</th>
                                <th class="text-center">MANAGE PERMISSION</th>
                                <th class="text-center">STATUS</th>
                                <th class="text-center">MODIFIED</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>1</td>
                                <td>QA</td>
                                <td class="text-center">
                                    <a href="{{route('buyer.role-permission.edit-role', 1)}}" class="btn btn-link ra-btn-outline-primary">
                                        <span class="bi bi-lock-fill" aria-hidden="true"></span>
                                    </a>
                                </td>
                                <td class="text-center"><label class="ra-switch-checkbox">
                                        <input type="checkbox" name="status" id="statusChecked" checked="">
                                        <span class="slider round"></span>
                                    </label></td>
                                <td class="text-center">11/06/2025</td>
                            </tr>
                            <tr>
                                <td>2</td>
                                <td>test role</td>
                                <td class="text-center">
                                    <a href="{{route('buyer.role-permission.edit-role', 1)}}" class="btn btn-link ra-btn-outline-primary">
                                        <span class="bi bi-lock-fill" aria-hidden="true"></span>
                                    </a>
                                </td>
                                <td class="text-center"><label class="ra-switch-checkbox">
                                        <input type="checkbox" name="status" id="statusChecked" checked="">
                                        <span class="slider round"></span>
                                    </label></td>
                                <td class="text-center">04/06/2025</td>
                            </tr>
                            <tr>
                                <td>3</td>
                                <td>test role</td>
                                <td class="text-center">
                                    <a href="{{route('buyer.role-permission.edit-role', 1)}}" class="btn btn-link ra-btn-outline-primary">
                                        <span class="bi bi-lock-fill" aria-hidden="true"></span>
                                    </a>
                                </td>
                                <td class="text-center">
                                    <label class="ra-switch-checkbox">
                                        <input type="checkbox" name="status" id="statusChecked" checked="">
                                        <span class="slider round"></span>
                                    </label>
                                </td>
                                <td class="text-center">06/06/2025</td>
                            </tr>
                            <tr>
                                <td>4</td>
                                <td>test role</td>
                                <td class="text-center">
                                    <a href="{{route('buyer.role-permission.edit-role', 1)}}" class="btn btn-link ra-btn-outline-primary">
                                        <span class="bi bi-lock-fill" aria-hidden="true"></span>
                                    </a>
                                </td>
                                <td class="text-center"><label class="ra-switch-checkbox">
                                        <input type="checkbox" name="status" id="statusChecked">
                                        <span class="slider round"></span>
                                    </label></td>
                                <td class="text-center">07/06/2025</td>
                            </tr>
                            <tr>
                                <td>5</td>
                                <td>test role</td>
                                <td class="text-center">
                                    <a href="{{route('buyer.role-permission.edit-role', 1)}}" class="btn btn-link ra-btn-outline-primary">
                                        <span class="bi bi-lock-fill" aria-hidden="true"></span>
                                    </a>
                                </td>
                                <td class="text-center"><label class="ra-switch-checkbox">
                                        <input type="checkbox" name="status" id="statusChecked" checked="">
                                        <span class="slider round"></span>
                                    </label></td>
                                <td class="text-center">10/06/2025</td>
                            </tr>
                            <tr>
                                <td>6</td>
                                <td>test role</td>
                                <td class="text-center">
                                    <a href="{{route('buyer.role-permission.edit-role', 1)}}" class="btn btn-link ra-btn-outline-primary">
                                        <span class="bi bi-lock-fill" aria-hidden="true"></span>
                                    </a>
                                </td>
                                <td class="text-center"><label class="ra-switch-checkbox">
                                        <input type="checkbox" name="status" id="statusChecked" checked="">
                                        <span class="slider round"></span>
                                    </label></td>
                                <td class="text-center">10/06/2025</td>
                            </tr>
                            <tr>
                                <td>7</td>
                                <td>QA</td>
                                <td class="text-center">
                                    <a href="{{route('buyer.role-permission.edit-role', 1)}}" class="btn btn-link ra-btn-outline-primary">
                                        <span class="bi bi-lock-fill" aria-hidden="true"></span>
                                    </a>
                                </td>
                                <td class="text-center"><label class="ra-switch-checkbox">
                                        <input type="checkbox" name="status" id="statusChecked" checked="">
                                        <span class="slider round"></span>
                                    </label></td>
                                <td class="text-center">15/06/2025</td>
                            </tr>
                            <tr>
                                <td>8</td>
                                <td>QA</td>
                                <td class="text-center">
                                    <a href="{{route('buyer.role-permission.edit-role', 1)}}" class="btn btn-link ra-btn-outline-primary">
                                        <span class="bi bi-lock-fill" aria-hidden="true"></span>
                                    </a>
                                </td>
                                <td class="text-center"><label class="ra-switch-checkbox">
                                        <input type="checkbox" name="status" id="statusChecked" checked="">
                                        <span class="slider round"></span>
                                    </label></td>
                                <td class="text-center">16/06/2025</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
@endsection

@section('scripts')

@endsection