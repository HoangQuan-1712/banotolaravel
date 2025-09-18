@extends('layouts.app')

@section('content')

  <style>
    /* ====== THEME ====== */
    :root{
      --gradA:#667eea;   /* tím xanh */
      --gradB:#764ba2;   /* tím đậm */
      --accent:#ffd700;  /* vàng nhấn */
      --ink:#0f172a;     /* text chính */
      --muted:#64748b;   /* text phụ */
      --card:#ffffff;
      --soft:#f6f7fb;
    }
    body{ font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial; color:var(--ink); background:var(--soft); }

    /* ====== NAVBAR (trùng tông header) ====== */
    .navbar{
      background: linear-gradient(135deg, var(--gradA) 0%, var(--gradB) 100%);
      box-shadow: 0 10px 30px rgba(118,75,162,.25);
    }
    .navbar .navbar-brand, .navbar .nav-link{ color:#fff !important; font-weight:700; letter-spacing:.2px; }
    .navbar .nav-link:hover{ color: var(--accent) !important; }

    /* ====== HERO ====== */
    .hero{
      min-height: 78vh;
      background:
        radial-gradient(1000px 400px at 10% 10%, rgba(255,255,255,.18), transparent 60%),
        radial-gradient(1000px 400px at 90% 10%, rgba(255,255,255,.14), transparent 60%),
        linear-gradient(135deg, var(--gradA), var(--gradB));
      color:#fff;
      display:grid; place-items:center;
      position:relative; overflow:hidden;
    }
    .hero:before{
      content:""; position:absolute; inset:-20%;
      background:url('https://images.unsplash.com/photo-1503376780353-7e6692767b70?auto=format&fit=crop&w=1600&q=80') center/cover no-repeat;
      mix-blend-mode:overlay; opacity:.25; filter: blur(2px) saturate(1.2);
    }
    .hero .wrap{ position:relative; z-index:2; text-align:center; max-width: 980px; padding: 0 1rem; }
    .headline{ font-weight:900; font-size: clamp(2.2rem, 4vw + 1rem, 4.2rem); line-height:1.05; }
    .subline{ font-size: clamp(1rem, 1.1vw + .6rem, 1.35rem); opacity:.95; margin-top: .85rem; }
    .hero-cta{ margin-top:1.8rem; display:flex; gap:.75rem; justify-content:center; flex-wrap:wrap; }
    .btn-accent{
      background: var(--accent); color:#000; border:none; font-weight:800; letter-spacing:.3px;
      padding:.85rem 1.35rem; border-radius:999px; box-shadow:0 10px 25px rgba(255,215,0,.35);
      transition: transform .25s ease, box-shadow .25s ease;
    }
    .btn-accent:hover{ transform: translateY(-3px) scale(1.02); box-shadow:0 16px 38px rgba(255,215,0,.45); }
    .btn-ghost{ border:2px solid rgba(255,255,255,.75); color:#fff; font-weight:700; padding:.82rem 1.25rem; border-radius:999px; }
    .hero-metrics{ margin-top:2rem; display:flex; gap:1.25rem; justify-content:center; flex-wrap:wrap; }
    .metric{ background: rgba(255,255,255,.12); backdrop-filter: blur(6px); border:1px solid rgba(255,255,255,.2); padding:.75rem 1rem; border-radius:12px; font-weight:600; }

    /* ====== SEARCH / FILTER BAR ====== */
    .search-bar{
      margin-top:16px;
      background: var(--card);
      border-radius:18px; padding: 1rem 1rem;
      box-shadow: 0 10px 30px rgba(10,10,30,.08);
      border:1px solid #eef1f6;
    }
    .search-bar .form-control, .search-bar .form-select{
      border-radius:12px; border:1px solid #e6e9f0;
    }
    .search-bar .btn{ border-radius:12px; font-weight:700; }

    /* ====== PRODUCT GRID ====== */
    .section{ padding: 3.5rem 0; }
    .section h2{ font-weight:900; letter-spacing:.3px; }
    .grid{ display:grid; grid-template-columns: repeat(12, 1fr); gap: 1.2rem; }
    @media (max-width: 992px){ .grid{ grid-template-columns: repeat(8, 1fr);} }
    @media (max-width: 576px){ .grid{ grid-template-columns: repeat(4, 1fr);} }
    .col-3c{ grid-column: span 3; }
    @media (max-width: 992px){ .col-3c{ grid-column: span 4; } }
    @media (max-width: 576px){ .col-3c{ grid-column: span 4; } }

    .card-car{
      background: var(--card);
      border:0; border-radius:16px; overflow:hidden; position:relative;
      box-shadow: 0 12px 30px rgba(16,24,40,.08);
      transform: translateZ(0); transition: transform .35s cubic-bezier(.2,.8,.2,1), box-shadow .35s;
    }
    .card-car:hover{ transform: translateY(-6px); box-shadow: 0 22px 50px rgba(16,24,40,.16); }
    .card-car .thumb{
      position:relative; aspect-ratio: 16/10; overflow:hidden; background:#111; }
    .card-car img{ width:100%; height:100%; object-fit:cover; transform: scale(1.02); transition: transform .6s ease; }
    .card-car:hover img{ transform: scale(1.07); }
    .badge-float{
      position:absolute; top:.75rem; left:.75rem;
      background: linear-gradient(135deg, #22c55e, #16a34a); color:#fff; font-weight:800;
      border-radius:999px; padding:.35rem .6rem; font-size:.8rem; box-shadow: 0 8px 18px rgba(34,197,94,.35);
    }
    .badge-vip{
      position:absolute; top:.75rem; right:.75rem;
      background: linear-gradient(135deg, #f59e0b, #eab308); color:#111; font-weight:900;
      border-radius:999px; padding:.35rem .6rem; font-size:.8rem; box-shadow: 0 8px 18px rgba(245,158,11,.35);
    }
    .card-car .body{ padding: 1rem 1rem 1.2rem; }
    .car-title{ font-weight:900; font-size:1.05rem; margin-bottom:.25rem; }
    .car-meta{ color: var(--muted); font-size:.92rem; }
    .price{ font-weight:900; font-size:1.15rem; letter-spacing:.2px; }
    .strike{ text-decoration: line-through; opacity:.5; font-weight:600; margin-left:.4rem; }
    .cta-row{ display:flex; align-items:center; justify-content:space-between; margin-top:.8rem; }
    .btn-quick{
      border:0; background:linear-gradient(135deg, var(--gradA), var(--gradB)); color:#fff; font-weight:800;
      padding:.6rem .9rem; border-radius:12px; box-shadow:0 10px 22px rgba(102,126,234,.25);
    }
    .btn-ghost-dark{ border:2px solid #e5e7eb; border-radius:12px; font-weight:800; color:var(--ink); background:#fff; }

    /* ====== MARQUEE BRANDS ====== */
    .brands{ background:#fff; border-top:1px solid #eef1f6; border-bottom:1px solid #eef1f6; }
    .marquee{ display:flex; gap:3rem; overflow:auto; white-space:nowrap; padding: .9rem 0; scrollbar-width:none; }
    .marquee img{ height:28px; opacity:.75; filter: grayscale(100%); transition: opacity .2s, filter .2s; }
    .marquee img:hover{ opacity:1; filter: none; }

    /* ====== FOOTER ====== */
    footer{
      background: linear-gradient(135deg, var(--gradA) 0%, var(--gradB) 100%);
      color:#fff; padding: 2.2rem 0; text-align:center; margin-top: 2rem;
    }
  </style>

  <!-- HERO -->
  <header class="hero">
    <div class="wrap">
      <h1 class="headline">Drive Your Dream. Today.</h1>
      <p class="subline">Premium selection • Smart finance • Real-time support • VIP rewards</p>
      <div class="hero-cta">
        <a href="#catalog" class="btn-accent"><i class="bi bi-rocket-takeoff me-1"></i> Explore Inventory</a>
        <a href="#offers" class="btn-ghost"><i class="bi bi-gift me-1"></i> See Offers</a>
      </div>
      <div class="hero-metrics">
        <div class="metric"><i class="bi bi-star-fill me-1"></i> 4.9/5 Satisfaction</div>
        <div class="metric"><i class="bi bi-shield-check me-1"></i> 3-Year Warranty</div>
        <div class="metric"><i class="bi bi-lightning me-1"></i> Fast Delivery</div>
      </div>
    </div>
  </header>

  <!-- SEARCH / FILTER -->
  <div class="container">
    <form class="search-bar row g-2 align-items-center mx-0" action="{{ route('products.index') }}" method="GET">
      <div class="col-12 col-md">
        <input class="form-control" name="q" value="{{ request('q') }}" placeholder="Tìm kiếm theo tên, hãng, mô tả…">
      </div>
      <div class="col-6 col-md-3">
        <select class="form-select" name="category">
          <option value="">Tất cả danh mục</option>
          @isset($brands)
            @foreach($brands as $brand)
              <option value="{{ $brand->id }}" {{ request('category') == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
            @endforeach
          @endisset
        </select>
      </div>
      <div class="col-6 col-md-3">
        <select class="form-select" name="price">
          <option value="">Mức giá</option>
          <option value="lt-300" {{ request('price')==='lt-300'?'selected':'' }}>Dưới 300 triệu</option>
          <option value="300-800" {{ request('price')==='300-800'?'selected':'' }}>300–800 triệu</option>
          <option value="gt-800" {{ request('price')==='gt-800'?'selected':'' }}>Trên 800 triệu</option>
        </select>
      </div>
      <div class="col-12 col-md-auto">
        <button class="btn btn-dark px-4" type="submit"><i class="bi bi-search me-1"></i>Tìm xe</button>
      </div>
    </form>

    @isset($brands)
    <div class="d-flex flex-wrap gap-2 mt-2">
      @foreach($brands as $brand)
        <a class="badge rounded-pill text-bg-light border" href="{{ route('products.index') }}?category={{ $brand->id }}">{{ $brand->name }}</a>
      @endforeach
      <a class="badge rounded-pill text-bg-primary" href="{{ route('products.index') }}">Tất cả xe</a>
    </div>
    @endisset
  </div>

  

  <!-- CATALOG -->
  <main id="catalog" class="section container">
    <div class="d-flex align-items-end justify-content-between mb-3">
      <div>
        <h2 class="mb-1">Featured Cars</h2>
        <div class="text-muted">Handpicked vehicles with exclusive benefits</div>
      </div>
      <div class="d-flex gap-2">
        <a class="btn btn-outline-secondary btn-sm" href="{{ route('categories.index') }}"><i class="bi bi-card-list me-1"></i> Danh Mục</a>
        <a class="btn btn-outline-primary btn-sm" href="{{ route('products.index') }}"><i class="bi bi-grid me-1"></i> Tất Cả Xe</a>
      </div>
    </div>

    <div class="grid">
      @php
        $hasFeatured = isset($featuredProducts) && count($featuredProducts);
      @endphp

      @if($hasFeatured)
        @foreach($featuredProducts as $p)
          <article class="card-car col-3c">
            <div class="thumb">
              <img src="{{ $p->image_url }}" alt="{{ $p->name }}">
              @if(!empty($vipIds) && in_array($p->id, $vipIds))
                <span class="badge-vip">VIP</span>
              @endif
            </div>
            <div class="body">
              <div class="car-title">{{ $p->name }}</div>
              <div class="car-meta">{{ optional($p->category)->name ?? 'Danh mục khác' }}</div>
              <div class="d-flex align-items-center gap-2 mt-1">
                <div class="price">${{ number_format($p->price, 2) }}</div>
              </div>
              <div class="cta-row">
                <a href="{{ route('products.show', $p->id) }}" class="btn-quick">Xem chi tiết</a>
                <a class="btn-ghost-dark" href="{{ route('products.index') }}?q={{ urlencode($p->name) }}">
                  <i class="bi bi-chat-dots"></i> Chat
                </a>
              </div>
            </div>
          </article>
        @endforeach
      @else
        @php
          $demo = [
            ['name'=>'Mẫu xe VIP','img'=>asset('images/default-car.jpg'),'price'=>2350000000],
            ['name'=>'SUV Gia đình','img'=>asset('images/default-car.jpg'),'price'=>1150000000],
            ['name'=>'Sedan Hạng sang','img'=>asset('images/default-car.jpg'),'price'=>1580000000],
            ['name'=>'Hatchback City','img'=>asset('images/default-car.jpg'),'price'=>620000000],
          ];
        @endphp
        @foreach($demo as $d)
          <article class="card-car col-3c">
            <div class="thumb">
              <img src="{{ $d['img'] }}" alt="{{ $d['name'] }}">
              <span class="badge-vip">VIP</span>
            </div>
            <div class="body">
              <div class="car-title">{{ $d['name'] }}</div>
              <div class="car-meta">Automatic • Full option</div>
              <div class="d-flex align-items-center gap-2 mt-1">
                <div class="price">${{ number_format($d['price'], 2) }}</div>
              </div>
              <div class="cta-row">
                <a href="{{ route('products.index') }}" class="btn-quick">Xem chi tiết</a>
                <a class="btn-ghost-dark" href="{{ route('products.index') }}"><i class="bi bi-chat-dots"></i> Chat</a>
              </div>
            </div>
          </article>
        @endforeach
      @endif
    </div>
  </main>

  <!-- OFFERS / VOUCHERS PREVIEW -->
  <section id="offers" class="section container">
    <div class="row g-4">
      <div class="col-lg-6">
        <div class="p-4 rounded-4" style="background:linear-gradient(135deg,#22c55e,#16a34a); color:#fff;">
          <h3 class="mb-1"><i class="bi bi-gift"></i> Tiered Choice Offers</h3>
          <p class="mb-3">Choose ONE exclusive perk when your order hits the tier. Save more with curated rewards.</p>
          <a href="#" class="btn btn-light fw-bold rounded-pill px-4">See Eligible Tiers</a>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="p-4 rounded-4" style="background:linear-gradient(135deg,#f59e0b,#eab308); color:#111;">
          <h3 class="mb-1"><i class="bi bi-shuffle"></i> Random Gift Events</h3>
          <p class="mb-3">Sign your order during event time to win a surprise gift — weighted & fair.</p>
          <a href="#" class="btn btn-dark fw-bold rounded-pill px-4">Try Your Luck</a>
        </div>
      </div>
    </div>
  </section>

  @push('styles')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  @endpush

@endsection
