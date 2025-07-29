@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
   <div class="row">
       <div class="col-md-3 grid-margin stretch-card">
           <div class="card">
               <div class="card-body">
                   <div class="d-flex justify-content-between align-items-baseline">
                       <h6 class="card-title mb-0">Total Users</h6>
                       <div class="dropdown mb-2">
                           <i class="fas fa-users text-muted" style="font-size: 2rem;"></i>
                       </div>
                   </div>
                   <div class="row">
                       <div class="col-6 col-md-12 col-xl-5">
                           <h3 class="mb-2">{{ number_format($totalUsers) }}</h3>
                       </div>
                   </div>
               </div>
           </div>
       </div>
       
       <div class="col-md-3 grid-margin stretch-card">
           <div class="card">
               <div class="card-body">
                   <div class="d-flex justify-content-between align-items-baseline">
                       <h6 class="card-title mb-0">Total Vendors</h6>
                       <div class="dropdown mb-2">
                           <i class="fas fa-home text-muted" style="font-size: 2rem;"></i>
                       </div>
                   </div>
                   <div class="row">
                       <div class="col-6 col-md-12 col-xl-5">
                           <h3 class="mb-2">{{ number_format($totalVendors) }}</h3>
                           <div class="d-flex align-items-baseline">
                               <p class="text-success">
                                   <span>{{ $activeVendors }} Active</span>
                               </p>
                           </div>
                       </div>
                   </div>
               </div>
           </div>
       </div>
       
       <div class="col-md-3 grid-margin stretch-card">
           <div class="card">
               <div class="card-body">
                   <div class="d-flex justify-content-between align-items-baseline">
                       <h6 class="card-title mb-0">Total Menus</h6>
                       <div class="dropdown mb-2">
                           <i class="fas fa-utensils text-muted" style="font-size: 2rem;"></i>
                       </div>
                   </div>
                   <div class="row">
                       <div class="col-6 col-md-12 col-xl-5">
                           <h3 class="mb-2">{{ number_format($totalMenus) }}</h3>
                           <div class="d-flex align-items-baseline">
                               <p class="text-success">
                                   <span>{{ $availableMenus }} Available</span>
                               </p>
                           </div>
                       </div>
                   </div>
               </div>
           </div>
       </div>
       
       <div class="col-md-3 grid-margin stretch-card">
           <div class="card">
               <div class="card-body">
                   <div class="d-flex justify-content-between align-items-baseline">
                       <h6 class="card-title mb-0">Total Orders</h6>
                       <div class="dropdown mb-2">
                           <i class="fas fa-shopping-cart text-muted" style="font-size: 2rem;"></i>
                       </div>
                   </div>
                   <div class="row">
                       <div class="col-6 col-md-12 col-xl-5">
                           <h3 class="mb-2">{{ number_format($totalOrders) }}</h3>
                       </div>
                   </div>
               </div>
           </div>
       </div>
   </div>

   <div class="row">
       <div class="col-lg-12 col-xl-12 grid-margin stretch-card">
           <div class="card">
               <div class="card-body">
                   <h6 class="card-title">Order Status Overview</h6>
                   <div class="row">
                       <div class="col-6 col-md-3">
                           <div class="d-flex justify-content-between align-items-center border-bottom pb-2">
                               <p>Pending</p>
                               <p class="text-muted">{{ $pendingOrders }}</p>
                           </div>
                       </div>
                       <div class="col-6 col-md-3">
                           <div class="d-flex justify-content-between align-items-center border-bottom pb-2">
                               <p>Processing</p>
                               <p class="text-muted">{{ $processingOrders }}</p>
                           </div>
                       </div>
                       <div class="col-6 col-md-3">
                           <div class="d-flex justify-content-between align-items-center border-bottom pb-2">
                               <p>On Delivery</p>
                               <p class="text-muted">{{ $onDeliveryOrders }}</p>
                           </div>
                       </div>
                       <div class="col-6 col-md-3">
                           <div class="d-flex justify-content-between align-items-center border-bottom pb-2">
                               <p>Completed</p>
                               <p class="text-muted">{{ $completedOrders }}</p>
                           </div>
                       </div>
                   </div>
               </div>
           </div>
       </div>
   </div>
@endsection

@section('scripts')
   @if($dailyOrders->count() >= 3)
       <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
       <script>
           // Daily Orders Chart
           const ctx = document.getElementById('ordersChart').getContext('2d');
           const chart = new Chart(ctx, {
               type: 'line',
               data: {
                   labels: {!! json_encode($dailyOrders->pluck('date')->map(function($date) { return date('M d', strtotime($date)); })) !!},
                   datasets: [{
                       label: 'Orders',
                       data: {!! json_encode($dailyOrders->pluck('count')) !!},
                       borderColor: 'rgb(75, 192, 192)',
                       tension: 0.1
                   }]
               },
               options: {
                   responsive: true,
                   plugins: {
                       title: {
                           display: true,
                           text: 'Daily Orders'
                       }
                   },
                   scales: {
                       y: {
                           beginAtZero: true
                       }
                   }
               }
           });
       </script>
   @endif
@endsection