@extends('layouts.customer')
@section('title', 'Phòng trọ tại Vinh, Nghệ An')
@section('main-class', 'customer-home')
@section('content')
@php
    $availableCount = (int) ($catalogStats->available_count ?? 0);
    $startingPrice = (float) ($catalogStats->starting_price ?? 0);
    $largestArea = (float) ($catalogStats->largest_area ?? 0);
@endphp
<section class="catalog-hero">
    <div class="catalog-hero-stage">
        <img class="hero-backdrop" src="{{ asset('images/home/living-space.jpg') }}" srcset="{{ asset('images/home/living-space-small.jpg') }} 800w, {{ asset('images/home/living-space.jpg') }} 2200w" sizes="100vw" alt="Ảnh minh họa không gian sống có nội thất" width="2200" height="1467" fetchpriority="high" decoding="async">
        <div class="container hero-content">
            <div class="catalog-hero-copy">
                <span class="hero-live"><i aria-hidden="true"></i>{{ $availableCount }} phòng đang sẵn sàng</span>
                <h1>Trọ Nhà Mình</h1>
                <p class="hero-subtitle">Phòng trọ tại Vinh, Nghệ An</p>
                <p>Một nơi ở vừa vặn với bạn. Xem phòng, tìm mức giá phù hợp và hẹn gặp trực tiếp ban quản lý.</p>
                <div class="hero-actions"><a class="btn btn-primary" href="#tim-phong"><i class="fas fa-search" aria-hidden="true"></i>Tìm phòng của bạn</a><a class="btn btn-outline-secondary" href="#chi-nhanh"><i class="fas fa-map-marker-alt" aria-hidden="true"></i>Các chi nhánh</a></div>
                <div class="hero-trust"><span><i class="fas fa-check-circle" aria-hidden="true"></i>Giá thuê công khai</span><span><i class="fas fa-phone-alt" aria-hidden="true"></i>Một đầu mối quản lý</span></div>
            </div>
        </div>
    </div>
    <div class="container hero-metrics" aria-label="Tổng quan phòng trọ">
        <div><strong>{{ $availableCount }}</strong><span>phòng đang trống</span></div>
        <div><strong>{{ $branches->count() }}</strong><span>chi nhánh ở Vinh</span></div>
        <div><strong>{{ $startingPrice > 0 ? number_format($startingPrice / 1000000, 1, ',', '.') . ' triệu' : 'Liên hệ' }}</strong><span>giá thuê từ / tháng</span></div>
        <div><strong>{{ $largestArea > 0 ? number_format($largestArea, 0, ',', '.') . ' m²' : 'Đa dạng' }}</strong><span>diện tích lớn nhất</span></div>
    </div>
</section>
<section class="quick-discovery" aria-label="Khám phá phòng nhanh">
    <div class="container">
        <div class="section-heading-row compact"><div><span class="section-kicker">Tìm theo ngân sách</span><h2>Chọn mức giá bạn thấy hợp lý</h2></div><p>Những lựa chọn phổ biến để bắt đầu tìm phòng nhanh hơn.</p></div>
        <div class="budget-grid">
            <a class="budget-choice" href="{{ route('home', ['max_price' => 2500000]) }}#tim-phong"><span class="budget-icon"><i class="fas fa-wallet" aria-hidden="true"></i></span><span><strong>Đến 2,5 triệu</strong><small>Giữ ngân sách gọn gàng</small></span><i class="fas fa-arrow-right" aria-hidden="true"></i></a>
            <a class="budget-choice" href="{{ route('home', ['min_price' => 2500000, 'max_price' => 4000000]) }}#tim-phong"><span class="budget-icon"><i class="fas fa-home" aria-hidden="true"></i></span><span><strong>2,5 đến 4 triệu</strong><small>Cân đối chi phí mỗi tháng</small></span><i class="fas fa-arrow-right" aria-hidden="true"></i></a>
            <a class="budget-choice" href="{{ route('home', ['min_price' => 4000000]) }}#tim-phong"><span class="budget-icon"><i class="fas fa-building" aria-hidden="true"></i></span><span><strong>Từ 4 triệu</strong><small>Thêm lựa chọn cho bạn</small></span><i class="fas fa-arrow-right" aria-hidden="true"></i></a>
        </div>
    </div>
</section>
@if($highlightRooms->isNotEmpty())
    <section class="highlight-section" aria-labelledby="highlight-heading">
        <div class="container">
            <div class="section-heading-row"><div><h2 id="highlight-heading">Phòng mới trong danh sách</h2><p class="heading-description">Xem ảnh, diện tích và giá thuê trước khi hẹn xem.</p></div><a class="section-link" href="#tim-phong">Xem toàn bộ phòng <i class="fas fa-arrow-right" aria-hidden="true"></i></a></div>
            <div class="highlight-grid">
                @foreach($highlightRooms as $room)
                    <a class="highlight-room-card" href="{{ route('room.show', $room) }}">
                        <div class="highlight-room-photo"><x-room-image :room="$room" :loading="$loop->first ? 'eager' : 'lazy'" /><span><i class="far fa-images" aria-hidden="true"></i>{{ count(array_unique(array_filter($room->images ?? []))) ?: 1 }}</span></div>
                        <div class="highlight-room-body"><div class="highlight-room-meta"><span>{{ $room->roomType->name ?? 'Phòng cho thuê' }}</span><span class="highlight-available"><i aria-hidden="true"></i>Còn trống</span></div><h3>Phòng {{ $room->name }}</h3><p><i class="fas fa-map-marker-alt" aria-hidden="true"></i>{{ $room->house->name }}</p><div class="highlight-room-price"><strong>{{ number_format($room->price / 1000000, 1, ',', '.') }} triệu <small>/ tháng</small></strong><span>{{ $room->area ? number_format((float) $room->area, 0, ',', '.') . ' m²' : 'Xem diện tích' }} · {{ $room->max_occupants }} người</span></div><span class="highlight-room-cta">Xem chi tiết <i class="fas fa-arrow-right" aria-hidden="true"></i></span></div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endif
<section class="catalog-intro" id="tim-phong">
    <div class="container">
        <div class="catalog-title-row">
            <div><span class="section-kicker">Danh sách phòng</span><h2>Tìm phòng theo nhu cầu của bạn</h2><p>Chọn mức giá, số người ở hoặc chi nhánh để lọc danh sách phù hợp.</p></div>
            <span class="location-note"><i class="fas fa-map-marker-alt" aria-hidden="true"></i> Vinh, Nghệ An</span>
        </div>
        <form id="catalogFilter" action="{{ route('home') }}#tim-phong" method="GET" class="catalog-search">
            @if(!empty($filters['house_id']))<input type="hidden" name="house_id" value="{{ $filters['house_id'] }}">@endif
            <div class="search-field search-keyword"><label for="q">Tên phòng hoặc địa chỉ</label><input type="search" id="q" name="q" maxlength="100" value="{{ $filters['q'] ?? '' }}" placeholder="Ví dụ: Lê Mao" autocomplete="off"></div>
            <div class="search-field"><label for="min_price">Giá từ (đ/tháng)</label><input type="number" id="min_price" name="min_price" min="0" max="100000000" step="1" inputmode="numeric" value="{{ $filters['min_price'] ?? '' }}" placeholder="Không giới hạn"></div>
            <div class="search-field"><label for="max_price">Giá đến (đ/tháng)</label><input type="number" id="max_price" name="max_price" min="0" max="100000000" step="1" inputmode="numeric" value="{{ $filters['max_price'] ?? '' }}" placeholder="Không giới hạn"></div>
            <div class="search-field search-occupants"><label for="occupants">Số người ở</label><select name="occupants" id="occupants"><option value="">Tất cả</option>@foreach(range(1, 4) as $people)<option value="{{ $people }}" @selected(($filters['occupants'] ?? '') == $people)>{{ $people }} người</option>@endforeach</select></div>
            <button class="btn btn-primary" type="submit"><i class="fas fa-search" aria-hidden="true"></i> Tìm phòng</button>
        </form>
        <nav class="branch-tabs" aria-label="Chọn chi nhánh">
            <a class="branch-tab {{ empty($filters['house_id']) ? 'active' : '' }}" href="{{ route('home', request()->except(['house_id', 'page'])) }}#tim-phong" @if(empty($filters['house_id'])) aria-current="page" @endif>Tất cả chi nhánh <span>{{ $branches->sum('available_rooms_count') }}</span></a>
            @foreach($branches as $branch)
                <a class="branch-tab {{ ($filters['house_id'] ?? '') == $branch->id ? 'active' : '' }}" href="{{ route('home', array_merge(request()->except('page'), ['house_id' => $branch->id])) }}#tim-phong" @if(($filters['house_id'] ?? '') == $branch->id) aria-current="page" @endif>{{ $branch->name }} <span>{{ $branch->available_rooms_count }}</span></a>
            @endforeach
        </nav>
    </div>
</section>
<div class="container catalog-body">
    <div class="catalog-layout">
        <section class="catalog-results" id="danh-sach-phong" aria-label="Danh sách phòng">
            <div class="catalog-toolbar">
                <div><h2>{{ number_format($rooms->total(), 0, ',', '.') }} phòng còn trống</h2>@if(request()->filled('q') || request()->filled('min_price') || request()->filled('max_price') || request()->filled('occupants') || request()->filled('house_id'))<a class="filter-clear" href="{{ route('home') }}"><i class="fas fa-times" aria-hidden="true"></i> Xóa bộ lọc</a>@else<p>Giá thuê theo tháng</p>@endif</div>
                <label class="catalog-sort"><span>Sắp xếp</span><select aria-label="Sắp xếp phòng" name="sort" form="catalogFilter" class="form-select" onchange="this.form.requestSubmit()">@foreach(['newest' => 'Mới cập nhật', 'price_asc' => 'Giá thấp đến cao', 'price_desc' => 'Giá cao đến thấp', 'area_desc' => 'Diện tích lớn nhất'] as $value => $label)<option value="{{ $value }}" @selected(($filters['sort'] ?? 'newest') === $value)>{{ $label }}</option>@endforeach</select></label>
            </div>
            <div class="room-list">
                @foreach($rooms as $room)
                    <article class="room-listing">
                        <a href="{{ route('room.show', $room) }}" class="listing-photo" aria-label="Xem phòng {{ $room->name }} tại {{ $room->house->name }}"><x-room-image :room="$room" :loading="$loop->first ? 'eager' : 'lazy'" /><span class="photo-count"><i class="far fa-images" aria-hidden="true"></i> {{ count(array_unique(array_filter($room->images ?? []))) ?: 1 }}</span></a>
                        <div class="listing-info">
                            <div class="listing-topline"><span>{{ $room->roomType->name ?? 'Phòng cho thuê' }}</span><span class="available-label"><span aria-hidden="true"></span>Còn trống</span></div>
                            <h3><a href="{{ route('room.show', $room) }}">Phòng {{ $room->name }}, {{ $room->house->name }}</a></h3>
                            <p class="listing-address"><i class="fas fa-map-marker-alt" aria-hidden="true"></i>{{ $room->house->address }}</p>
                            <div class="listing-price">{{ number_format($room->price, 0, ',', '.') }} đ <small>/ tháng</small></div>
                            <dl class="listing-specs"><div><dt>Diện tích</dt><dd>{{ $room->area !== null ? rtrim(rtrim(number_format((float) $room->area, 2, ',', '.'), '0'), ',') . ' m²' : 'Chưa cập nhật' }}</dd></div><div><dt>Số người ở</dt><dd>{{ $room->max_occupants }} người</dd></div><div><dt>Vị trí</dt><dd>Tầng {{ $room->floor }}</dd></div></dl>
                            <div class="listing-extras"><span><i class="fas fa-file-invoice-dollar" aria-hidden="true"></i>Giá phòng hiển thị trước</span>@if($room->services->isNotEmpty())<span><i class="fas fa-bolt" aria-hidden="true"></i>{{ $room->services->take(2)->pluck('name')->join(', ') }}</span>@else<span><i class="fas fa-info-circle" aria-hidden="true"></i>Xem phí dịch vụ tại chi tiết</span>@endif</div>
                            <div class="listing-bottom"><span><i class="fas fa-clock" aria-hidden="true"></i>Cập nhật {{ $room->updated_at?->format('d/m/Y') }}</span><a href="{{ route('room.show', $room) }}">Xem chi tiết <i class="fas fa-angle-right" aria-hidden="true"></i></a></div>
                        </div>
                    </article>
                @endforeach
            </div>
            @if($rooms->isEmpty())<div class="catalog-empty"><i class="fas fa-search" aria-hidden="true"></i><h3>Chưa có phòng phù hợp</h3><p>Thử mức giá khác hoặc xem phòng ở chi nhánh khác.</p><a class="btn btn-outline-primary" href="{{ route('home') }}">Xem tất cả phòng</a></div>@endif
            @if($rooms->hasPages())
                <div class="catalog-pagination" aria-label="Phân trang phòng">{{ $rooms->links('pagination::bootstrap-5') }}</div>
            @endif
        </section>
        <aside class="catalog-sidebar" id="chi-nhanh-sidebar" aria-label="Các chi nhánh">
            <h2>Các chi nhánh</h2>
            @foreach($branches as $branch)
                <article class="branch-entry"><div><h3>{{ $branch->name }}</h3><p>{{ $branch->address }}</p><a class="branch-room-link" href="{{ route('home', ['house_id' => $branch->id]) }}">{{ $branch->available_rooms_count }} phòng còn trống <i class="fas fa-angle-right" aria-hidden="true"></i></a>@if($branch->phone)<a class="branch-phone" href="tel:{{ preg_replace('/[^0-9+]/', '', $branch->phone) }}"><i class="fas fa-phone-alt" aria-hidden="true"></i>{{ $branch->phone }}</a>@endif</div></article>
            @endforeach
            <div class="catalog-contact"><i class="far fa-comment-dots" aria-hidden="true"></i><h3>Hẹn xem phòng</h3><p>Liên hệ chi nhánh để hẹn thời gian và xác nhận phòng còn trống.</p>@if($zaloNumber)<a href="https://zalo.me/{{ preg_replace('/[^0-9]/', '', $zaloNumber) }}" target="_blank" rel="noopener" class="btn btn-outline-secondary w-100">Nhắn qua Zalo <i class="fas fa-external-link-alt" aria-hidden="true"></i></a>@else<a href="#lien-he" class="btn btn-outline-secondary w-100">Liên hệ ban quản lý</a>@endif</div>
        </aside>
    </div>
</div>
<section class="branch-showcase" id="chi-nhanh" aria-labelledby="branches-heading">
    <div class="container">
        <div class="section-heading-row"><div><h2 id="branches-heading">Các chi nhánh tại Vinh</h2><p class="heading-description">Một đầu mối quản lý, nhiều lựa chọn nơi ở.</p></div><span class="location-note"><i class="fas fa-map-marker-alt" aria-hidden="true"></i>Vinh, Nghệ An</span></div>
        <div class="branch-grid">
            @foreach($branches as $branch)
                <article class="branch-card">
                    <div class="branch-card-top"><span class="branch-icon"><i class="fas fa-building" aria-hidden="true"></i></span><span class="branch-availability"><i aria-hidden="true"></i>{{ $branch->available_rooms_count }} phòng trống</span></div>
                    <h3>{{ $branch->name }}</h3>
                    <p class="branch-card-address"><i class="fas fa-map-marker-alt" aria-hidden="true"></i>{{ $branch->address }}</p>
                    <p class="branch-card-description">{{ $branch->description ?: 'Liên hệ ban quản lý để biết thêm về chi nhánh.' }}</p>
                    <div class="branch-card-meta"><span><strong>{{ $branch->total_rooms_count }}</strong> phòng</span><span><strong>{{ $branch->available_rooms_count }}</strong> đang trống</span></div>
                    <div class="branch-card-actions"><a href="{{ route('home', ['house_id' => $branch->id]) }}#tim-phong">Xem phòng tại đây <i class="fas fa-arrow-right" aria-hidden="true"></i></a>@if($branch->phone)<a class="branch-card-phone" href="tel:{{ preg_replace('/[^0-9+]/', '', $branch->phone) }}" aria-label="Gọi {{ $branch->phone }}" title="Gọi {{ $branch->phone }}"><i class="fas fa-phone" aria-hidden="true"></i></a>@endif</div>
                </article>
            @endforeach
        </div>
    </div>
</section>
<section class="living-section" aria-labelledby="living-heading">
    <div class="container">
        <h2 id="living-heading">Ở trọ, mọi việc rõ ràng hơn</h2>
        <div class="living-grid">
            <article><i class="fas fa-user-check" aria-hidden="true"></i><h3>Liên hệ đúng người</h3><p>Các chi nhánh cùng một chủ quản lý. Gọi trực tiếp theo thông tin của chi nhánh bạn chọn.</p></article>
            <article><i class="fas fa-file-invoice-dollar" aria-hidden="true"></i><h3>Biết trước chi phí</h3><p>Giá thuê, diện tích và dịch vụ được hiển thị trong từng phòng để bạn cân đối ngân sách.</p></article>
            <article><i class="far fa-bell" aria-hidden="true"></i><h3>Theo dõi yêu cầu thuê</h3><p>Đăng nhập để xem trạng thái yêu cầu và nhận thông báo khi ban quản lý phản hồi.</p></article>
        </div>
    </div>
</section>
<section class="rental-process" aria-labelledby="process-heading">
    <div class="container">
        <div class="section-heading-row compact"><div><h2 id="process-heading">Từ xem phòng đến nhận phòng</h2></div><p>Ba bước để bắt đầu thuê tại Trọ Nhà Mình.</p></div>
        <ol class="process-grid">
            <li><span class="process-number">1</span><div><h3>Chọn phòng</h3><p>Lọc theo giá, chi nhánh và số người ở phù hợp với bạn.</p></div></li>
            <li><span class="process-number">2</span><div><h3>Gửi yêu cầu</h3><p>Điền thông tin cơ bản, chưa cần thanh toán khi gửi yêu cầu.</p></div></li>
            <li><span class="process-number">3</span><div><h3>Xác nhận & nhận phòng</h3><p>Hẹn xem, thống nhất hợp đồng và nhận hướng dẫn từ ban quản lý.</p></div></li>
        </ol>
        @guest
            <div class="process-actions"><p>Đã tìm thấy phòng phù hợp?</p><a class="btn btn-primary" href="{{ route('register') }}"><i class="fas fa-user-plus" aria-hidden="true"></i>Đăng ký tài khoản</a><a href="{{ route('login') }}">Đã có tài khoản? Đăng nhập</a></div>
        @endguest
    </div>
</section>
<section class="faq-section" aria-labelledby="faq-heading">
    <div class="container faq-layout">
        <div><h2 id="faq-heading">Câu hỏi thường gặp</h2><p>Một vài điều bạn có thể cần biết trước khi hẹn xem phòng.</p></div>
        <div class="faq-list">
            <details><summary>Gửi yêu cầu đặt thuê có mất phí không?<i class="fas fa-plus" aria-hidden="true"></i></summary><p>Không. Bạn chỉ gửi thông tin để ban quản lý xác nhận phòng và hẹn xem. Khoản cọc chỉ được thống nhất sau khi hai bên đồng ý.</p></details>
            <details><summary>Giá điện, nước và wifi được tính thế nào?<i class="fas fa-plus" aria-hidden="true"></i></summary><p>Mỗi phòng hiển thị các dịch vụ đang áp dụng ở trang chi tiết. Điện, nước tính theo chỉ số; các khoản cố định được ghi rõ theo tháng.</p></details>
            <details><summary>Có thể xem phòng trước khi quyết định không?<i class="fas fa-plus" aria-hidden="true"></i></summary><p>Có. Bạn có thể gọi cho chi nhánh hoặc nhắn ban quản lý để hẹn thời gian xem phòng thực tế.</p></details>
        </div>
    </div>
</section>
@endsection
