@extends('layouts.app')

@section('content')
<div class="container">
    <chats :user="{{ auth()->user() }}"></chats> 
</div>
@endsection
 
<style type="text/css">
	li.active {
		font-size: 18px !important;
		font-weight:bolder !important;
	}
</style>