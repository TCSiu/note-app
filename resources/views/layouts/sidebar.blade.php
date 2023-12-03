<nav id="sidebar" class="sidebar js-sidebar">
	<div class="sidebar-content js-simplebar">
		<a class="sidebar-brand" href=""><span class="align-middle">FYP</span></a>

		<div class="sidebar-user">
			<div class="d-flex justify-content-center">
				<div class="flex-shrink-0">
					<img src="{{ asset('img/default icon.jpg') }}" class="avatar img-fluid rounded me-1" alt="User icon" />
				</div>
				<div class="flex-grow-1 ps-2">
					<a class="sidebar-user-title dropdown-toggle" href="#" data-bs-toggle="dropdown">
                        temp
					</a>
					<div class="dropdown-menu dropdown-menu-start">
						@includeIf('layouts.parts.personal_menu')
					</div>
					<div class="sidebar-user-subtitle"></div>
				</div>
			</div>
		</div>

		<ul class="sidebar-nav">

			<li class="sidebar-item">
				<a class="sidebar-link" href=""><i class="align-middle" data-feather="sliders"></i> <span class="align-middle">{{ __('Dashboard') }}</span></a>
			</li>
			
			<li class="sidebar-header">
				<span class="align-middle">{{ __('Delivery Order') }}</span>
			</li>

			<li class="sidebar-item">
				<a class="sidebar-link" href=""><i class="align-middle" data-feather="package"></i> <span class="align-middle">{{ __('View All Orders') }}</span></a>
			</li>

			<li class="sidebar-item">
				<a class="sidebar-link" href=""><i class="align-middle" data-feather="plus-circle"></i> <span class="align-middle">{{ __('Create Order') }}</span></a>
			</li>

			<li class="sidebar-header">
				{{ __('Task') }}
			</li>

			<li class="sidebar-item">
				<a class="sidebar-link" href=""><i class="align-middle" data-feather="truck"></i> <span class="align-middle">{{ __('Route Planning') }}</span></a>
			</li>

			<li class="sidebar-header">
				{{ __('Staff Account Management') }}
			</li>

			<li class="sidebar-item">
				<a class="sidebar-link" href=""><i class="align-middle" data-feather="user"></i> <span class="align-middle">{{ __('View All Staff Accounts') }}</span></a>
			</li>

			<li class="sidebar-item">
				<a class="sidebar-link" href=""><i class="align-middle" data-feather="plus-circle"></i> <span class="align-middle">{{ __('Create Staff Account') }}</span></a>
			</li>

		</ul>
		<div class="sidebar-cta">
			<div class="sidebar-cta-content">
				<div class="d-grid">
					<a href="{{ route('logout') }}" class="btn btn-primary">{{ __('Logout') }}</a>
				</div>
			</div>
		</div>
	</div>
</nav>

@push('scripts')
<script type="text/javascript">
const dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'));
const dropdownList = dropdownElementList.map(function (dropdownToggleEl) {
    return new bootstrap.Dropdown(dropdownToggleEl);
})
</script>
@endpush
