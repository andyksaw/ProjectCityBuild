@extends('admin.layouts.admin')

@section('title', 'Groups')

@section('body')
    <div class="table-responsive">
        <table class="table">
            <thead>
            <tr>
                <th>Name</th>
                <th>Members</th>
                <th>Alias</th>
                <th>Discourse Name</th>
                <th>Is Default?</th>
                <th>Is Staff?</th>
                <th>Is Admin?</th>
                <th>Panel Access?</th>
            </tr>
            </thead>
            <tbody>
            @foreach($groups as $group)
                <tr>
                    <td>{{ $group->name }}</td>
                    <td>
                        <a href="{{ route('front.panel.groups.accounts', $group) }}">{{ number_format($group->accounts_count) }}</a>
                    </td>
                    <td>{{ $group->alias }}</td>
                    <td>
                        @if ($group->discourse_name != "")
                            <a href="https://forums.projectcitybuild.com/g/{{ $group->discourse_name }}" class="text-muted me-2" target="_blank"><i class="fab fa-discourse"></i></a>
                        @endif
                        {{ $group->discourse_name }}
                    </td>
                    <td><x-bs.fa-boolean :data="$group->is_default" false-class="text-muted" /></td>
                    <td><x-bs.fa-boolean :data="$group->is_staff" false-class="text-muted" /></td>
                    <td><x-bs.fa-boolean :data="$group->is_admin" false-class="text-muted" /></td>
                    <td><x-bs.fa-boolean :data="$group->can_access_panel" false-class="text-muted" /></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection
