@extends('buyer.layouts.app', ['title'=>'Buyer Dashboard'])

@section('css')
@endsection

@section('content')
    <div class="bg-white">
        <!---Sidebar-->
        @include('buyer.layouts.sidebar-default')
    </div>

    <!---Section Main-->
    <main class="main flex-grow-1">
        <div class="container-fluid">
            <div class="card">
                <div class="card-content-stretch p-4">                    
                    <h2>Buyer Dashboard <br/> Hello, {{ $user->name }}, Welcome to Buyer Dashboard</h2>
                </div>
            </div>
        </div>
    </main>
@endsection

@section('scripts')
@endsection