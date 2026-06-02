{{--
Permission helper blade component for checking user permissions.
Usage:
  @can('account_management.create')
    <!-- button or content -->
  @endcan

Or use PermissionHelper in views:
  @if(auth()->user()->hasPermission('account_management.create'))
    <!-- button or content -->
  @endif
--}}

@php
use App\Helpers\PermissionHelper;
@endphp
