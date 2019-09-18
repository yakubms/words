@extends('layouts.default')

@section('content')
    @include('layouts.nav')
    <div>
        <router-view>
        </router-view>
    </div>
@endsection
