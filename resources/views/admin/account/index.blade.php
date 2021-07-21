@extends('admin.layouts.admin')

@section('title', $title ?? 'Accounts')

@section('toolbar')
    @if($showSearch ?? true)
    <div class="d-flex align-items-center">
        @if(!empty($query))
            <a href="{{ route('front.panel.accounts.index') }}" class="d-inline-block me-5 link-danger"><i class="fas fa-times me-2"></i>Clear</a>
        @endif
        <form action="{{ route('front.panel.accounts.index') }}" method="get">
            <div class="input-group input-group-sm">
                <input type="text" class="form-control" name="query" value="{{ $query }}" aria-describedby="button-search">
                <button class="btn btn-outline-secondary" type="submit" id="button-search"><i class="fas fa-search" aria-label="Search"></i></button>
            </div>
        </form>
    </div>
    @endif
@endsection

@section('body')
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
            <tr>
                <th>ID</th>
                <th>Email</th>
                <th>Username</th>
                <th>Last Logged In</th>
                <th>Manage</th>
            </tr>
            </thead>
            <tbody>
            @foreach($accounts as $account)
                <tr>
                    <td>{{ $account->account_id }}</td>
                    <td>{{ $account->email }}</td>
                    <td>{{ $account->username }}</td>
                    <td>{{ $account->last_login_at }}</td>
                    <td class="actions">
                        <a href="{{ route('front.panel.accounts.show', $account->account_id) }}">Manage</a>
                        <a href="{{ route('front.panel.accounts.discourse-admin-redirect', $account->account_id) }}" target="_blank">Discourse</a>
                    </td>
                </tr>
            @endforeach
            </tbody>

        </table>

        {{ $accounts->links('vendor.pagination.bootstrap-4') }}
    </div>
@endsection

