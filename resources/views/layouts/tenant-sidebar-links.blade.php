@php
$links = [
    [
        'name' => 'Tổng quan (Phòng)',
        'route' => 'tenant.dashboard',
        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>'
    ],
    [
        'name' => 'Hóa đơn & Thanh toán',
        'route' => 'tenant.invoices.index',
        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>'
    ],
    [
        'name' => 'Hợp đồng của tôi',
        'route' => 'tenant.contracts.index',
        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>'
    ],
    [
        'name' => 'Báo cáo sự cố',
        'route' => 'tenant.maintenance-tickets.index',
        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>'
    ],
    [
        'name' => 'Hồ sơ cá nhân',
        'route' => 'profile.edit',
        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>'
    ]
];
@endphp

@foreach($links as $link)
    <a href="{{ route($link['route']) }}" class="group flex items-center px-3 py-3 text-sm font-medium rounded-xl transition-all duration-200 
        {{ request()->routeIs($link['route']) || (isset($link['active_pattern']) && request()->routeIs($link['active_pattern'])) ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white' }}">
        
        <svg class="mr-3 flex-shrink-0 h-6 w-6 
            {{ request()->routeIs($link['route']) || (isset($link['active_pattern']) && request()->routeIs($link['active_pattern'])) ? 'text-blue-600 dark:text-blue-400' : 'text-gray-400 dark:text-gray-500 group-hover:text-gray-500 dark:group-hover:text-gray-300' }}" 
            fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            {!! $link['icon'] !!}
        </svg>
        {{ $link['name'] }}
    </a>
@endforeach
