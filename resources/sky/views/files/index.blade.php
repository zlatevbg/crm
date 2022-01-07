@extends('layouts.main')

@section('content')
   Hello, I am {{ $user->getGivenName() }}
@endsection
