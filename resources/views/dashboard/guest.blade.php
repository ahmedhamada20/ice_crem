@extends('layouts.admin')
@section('title', __('Dashboard'))

@section('content')
<div class="card shadow-sm" style="max-width: 600px; margin: 3rem auto;">
    <div class="card-body text-center p-5">
        <i class="bi bi-person-badge display-1 text-muted mb-3"></i>
        <h5>مرحباً، {{ auth()->user()->name }}</h5>
        <p class="text-muted">لم يتم تعيين دور لحسابك بعد. يرجى التواصل مع المدير لتفعيل الصلاحيات.</p>
        <form method="POST" action="{{ route('logout') }}" class="mt-3">
            @csrf
            <button class="btn btn-outline-secondary btn-sm">{{ __('Logout') }}</button>
        </form>
    </div>
</div>
@endsection
