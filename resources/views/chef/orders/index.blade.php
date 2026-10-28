@extends('admin.layouts.master')

@section('title', 'Papan Dapur')
@section('heading', 'Papan Dapur')
@section('subheading', 'Pesanan terkonfirmasi, urut dari yang paling lama menunggu')

@section('content')
<div data-auto-refresh="30"></div>

@php
    use App\Enums\OrderStatus;

    $columns = [
        ['status' => OrderStatus::Confirmed, 'next' => OrderStatus::Cooking, 'action' => 'Mulai Masak'],
        ['status' => OrderStatus::Cooking, 'next' => OrderStatus::Cooked, 'action' => 'Selesai Dimasak'],
        ['status' => OrderStatus::Cooked, 'next' => OrderStatus::Served, 'action' => 'Sudah Disajikan'],
    ];
@endphp

<div class="row g-3 mb-3">
    <div class="col-sm-6 col-lg-3">
        <div class="rk-card rk-stat">
            <div class="rk-stat__icon text-bg-success"><i class="fas fa-utensils"></i></div>
            <div>
                <p class="rk-stat__value">{{ $servedToday }}</p>
                <p class="rk-stat__label">Disajikan Hari Ini</p>
            </div>
        </div>
    </div>
    @foreach ($columns as $column)
        <div class="col-sm-6 col-lg-3">
            <div class="rk-card rk-stat">
                <div class="rk-stat__icon text-bg-{{ $column['status']->color() }}">
                    <i class="fas {{ $column['status']->icon() }}"></i>
                </div>
                <div>
                    <p class="rk-stat__value">{{ $queues[$column['status']->value]->count() }}</p>
                    <p class="rk-stat__label">{{ $column['status']->label() }}</p>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="row g-3">
    @foreach ($columns as $column)
        @php
            $orders = $queues[$column['status']->value];
        @endphp

        <div class="col-lg-4">
            <div class="rk-kitchen-column">
                <div class="rk-kitchen-column__head">
                    <i class="fas {{ $column['status']->icon() }} text-{{ $column['status']->color() }}"></i>
                    {{ $column['status']->label() }}
                    <span class="badge rounded-pill text-bg-light ms-auto">{{ $orders->count() }}</span>
                </div>

                <div class="rk-kitchen-column__body">
                    @forelse ($orders as $order)
                        <article class="rk-ticket">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <span class="rk-ticket__code d-block">{{ $order->order_code }}</span>
                                    <small class="text-body-secondary">
                                        <i class="fas fa-clock me-1"></i>{{ $order->created_at?->diffForHumans(short: true) ?? '-' }}
                                    </small>
                                </div>
                                <span class="badge rounded-pill text-bg-dark">Meja {{ $order->table_number }}</span>
                            </div>

                            <ul class="list-unstyled small mb-2">
                                @foreach ($order->orderItems as $orderItem)
                                    <li>
                                        <span class="fw-semibold">{{ $orderItem->quantity }}&times;</span>
                                        {{ $orderItem->item?->name ?? 'Menu' }}
                                    </li>
                                @endforeach
                            </ul>

                            @if ($order->note)
                                <p class="alert alert-warning py-1 px-2 small mb-2">
                                    <i class="fas fa-note-sticky me-1"></i>{{ $order->note }}
                                </p>
                            @endif

                            <div class="d-flex gap-2">
                                <form action="{{ route('chef.orders.status', $order) }}" method="POST" class="flex-grow-1">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="{{ $column['next']->value }}">
                                    <button type="submit" class="btn btn-sm btn-primary w-100">
                                        <i class="fas {{ $column['next']->icon() }} me-1"></i> {{ $column['action'] }}
                                    </button>
                                </form>
                                <a href="{{ route('chef.orders.show', $order) }}" class="btn btn-sm btn-light border" title="Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
                        </article>
                    @empty
                        <p class="text-body-secondary small text-center mb-0 py-3">Tidak ada pesanan.</p>
                    @endforelse
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection
