<?php
include './config/connection.php';
include './common_service/common_functions.php';

$message = '';

?>
<!DOCTYPE html>
<html lang="en">
<head>
 <?php include './config/site_css_links.php';?>
 <?php include './config/data_tables_css.php';?>
 <title>POS - Medical Care and Information system</title>
  <style>
    /* Enhanced POS Styling */
    .pos-container {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      min-height: 100vh;
      padding: 20px 0;
    }
    
    .pos-card {
      background: white;
      border-radius: 15px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
      border: none;
      overflow: hidden;
    }
    
    .pos-header {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      padding: 20px;
      border-radius: 15px 15px 0 0;
    }
    
    .pos-header h3 {
      margin: 0;
      font-weight: 600;
      font-size: 1.5rem;
    }
    
    .pos-body {
      padding: 25px;
    }
    
    /* Enhanced Medicine Cards */
    .medicine-card {
      background: white;
      border: 2px solid #e9ecef;
      border-radius: 12px;
      padding: 15px;
      margin-bottom: 15px;
      transition: all 0.3s ease;
      cursor: pointer;
      position: relative;
      overflow: hidden;
    }
    
    .medicine-card:hover {
      border-color: #667eea;
      box-shadow: 0 5px 15px rgba(102, 126, 234, 0.2);
      transform: translateY(-2px);
    }
    
    .medicine-card.added {
      border-color: #28a745;
      background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
    }
    
    .medicine-name {
      font-weight: 600;
      font-size: 14px;
      color: #2c3e50;
      margin-bottom: 5px;
      line-height: 1.3;
    }
    
    .medicine-packing {
      font-size: 12px;
      color: #6c757d;
      margin-bottom: 8px;
    }
    
    .medicine-price {
      font-size: 16px;
      font-weight: 700;
      color: #28a745;
      margin-bottom: 8px;
    }
    
    .stock-badge {
      position: absolute;
      top: 10px;
      right: 10px;
      font-size: 10px;
      padding: 4px 8px;
      border-radius: 12px;
      font-weight: 600;
    }
    
    .stock-badge.low {
      background: #f8d7da;
      color: #721c24;
    }
    
    .stock-badge.critical {
      background: #f5c6cb;
      color: #721c24;
    }
    
    .stock-badge.good {
      background: #d1ecf1;
      color: #0c5460;
    }
    
    .add-btn {
      width: 100%;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      border: none;
      color: white;
      padding: 8px 12px;
      border-radius: 8px;
      font-weight: 600;
      font-size: 12px;
      transition: all 0.3s ease;
    }
    
    .add-btn:hover {
      transform: translateY(-1px);
      box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    }
    
    .add-btn:disabled {
      background: #6c757d;
      cursor: not-allowed;
      transform: none;
      box-shadow: none;
    }
    
    /* Enhanced Search */
    .search-container {
      position: relative;
      margin-bottom: 20px;
    }
    
    .search-input {
      border: 2px solid #e9ecef;
      border-radius: 25px;
      padding: 12px 20px 12px 45px;
      font-size: 14px;
      transition: all 0.3s ease;
    }
    
    .search-input:focus {
      border-color: #667eea;
      box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }
    
    .search-icon {
      position: absolute;
      left: 15px;
      top: 50%;
      transform: translateY(-50%);
      color: #6c757d;
    }
    
    .clear-search-btn {
      position: absolute;
      right: 10px;
      top: 50%;
      transform: translateY(-50%);
      background: none;
      border: none;
      color: #6c757d;
      font-size: 18px;
      cursor: pointer;
    }
    
    /* Enhanced Cart Table */
    .cart-container {
      background: #f8f9fa;
      border-radius: 12px;
      padding: 20px;
      margin-top: 20px;
    }
    
    .cart-header {
      display: flex;
      justify-content: between;
      align-items: center;
      margin-bottom: 15px;
    }
    
    .cart-title {
      font-size: 18px;
      font-weight: 600;
      color: #2c3e50;
      margin: 0;
    }
    
    .item-count-badge {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      padding: 5px 12px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: 600;
    }
    
    .cart-table {
      background: white;
      border-radius: 8px;
      overflow: hidden;
      box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    /* Hide DataTables pagination info for cart table */
    .cart-table + .dataTables_info,
    .cart-table + * + .dataTables_info {
      display: none !important;
    }
    
    /* Hide any DataTables pagination elements near cart table */
    #selected_items_table_wrapper .dataTables_info,
    #selected_items_table_wrapper .dataTables_length,
    #selected_items_table_wrapper .dataTables_filter,
    #selected_items_table_wrapper .dataTables_paginate {
      display: none !important;
    }
    
    .cart-table th {
      background: #f8f9fa;
      color: #495057;
      font-weight: 600;
      font-size: 13px;
      padding: 12px 8px;
      border: none;
    }
    
    .cart-table td {
      padding: 12px 8px;
      border: none;
      border-bottom: 1px solid #e9ecef;
      vertical-align: middle;
    }
    
    .cart-table tbody tr:hover {
      background: #f8f9fa;
    }
    
    .quantity-controls {
      display: flex;
      align-items: center;
      gap: 5px;
    }
    
    .qty-btn {
      width: 28px;
      height: 28px;
      border: 1px solid #dee2e6;
      background: white;
      border-radius: 4px;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: all 0.2s ease;
    }
    
    .qty-btn:hover {
      background: #e9ecef;
      border-color: #adb5bd;
    }
    
    .qty-input {
      width: 50px;
      text-align: center;
      border: 1px solid #dee2e6;
      border-radius: 4px;
      padding: 4px;
      font-size: 12px;
    }
    
    .remove-btn {
      background: #dc3545;
      color: white;
      border: none;
      border-radius: 4px;
      padding: 4px 8px;
      font-size: 12px;
      cursor: pointer;
      transition: all 0.2s ease;
    }
    
    .remove-btn:hover {
      background: #c82333;
    }
    
    .subtotal {
      font-weight: 600;
      color: #28a745;
    }
    
    .total-row {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      font-weight: 600;
    }
    
    .total-row td {
      border: none;
      padding: 15px 8px;
    }
    
    /* Enhanced Buttons */
    .process-sale-btn {
      background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
      border: none;
      color: white;
      padding: 15px 30px;
      border-radius: 8px;
      font-weight: 600;
      font-size: 16px;
      width: 100%;
      margin-top: 20px;
      transition: all 0.3s ease;
    }
    
    .process-sale-btn:hover:not(:disabled) {
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
    }
    
    .process-sale-btn:disabled {
      background: #6c757d;
      cursor: not-allowed;
      transform: none;
      box-shadow: none;
    }
    
    .clear-cart-btn {
      background: #dc3545;
      border: none;
      color: white;
      padding: 8px 16px;
      border-radius: 6px;
      font-size: 12px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.2s ease;
    }
    
    .clear-cart-btn:hover {
      background: #c82333;
    }
    
    /* Patient Selection */
    .patient-section {
      background: #f8f9fa;
      border-radius: 8px;
      padding: 15px;
      margin-bottom: 20px;
    }
    
    .patient-section label {
      font-weight: 600;
      color: #495057;
      margin-bottom: 8px;
    }
    
    .form-control {
      border: 2px solid #e9ecef;
      border-radius: 6px;
      padding: 8px 12px;
      transition: all 0.3s ease;
    }
    
    .form-control:focus {
      border-color: #667eea;
      box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }
    
    /* Prescription Section */
    .prescription-section {
      background: #fff3cd;
      border: 1px solid #ffeaa7;
      border-radius: 8px;
      padding: 15px;
      margin-bottom: 20px;
    }
    
    .prescription-item {
      background: white;
      border-radius: 6px;
      padding: 10px;
      margin-bottom: 8px;
      border-left: 4px solid #667eea;
    }
    
    .prescription-item:last-child {
      margin-bottom: 0;
    }
    
    /* Loading States */
    .loading {
      opacity: 0.6;
      pointer-events: none;
    }
    
    .spinner {
      display: inline-block;
      width: 20px;
      height: 20px;
      border: 3px solid #f3f3f3;
      border-top: 3px solid #667eea;
      border-radius: 50%;
      animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }
    
    /* Responsive Design */
    @media (max-width: 768px) {
      .pos-container {
        padding: 10px 0;
      }
      
      .pos-body {
        padding: 15px;
      }
      
      .medicine-card {
        padding: 12px;
      }
      
      .cart-table th,
      .cart-table td {
        padding: 8px 4px;
        font-size: 12px;
      }
      
      .quantity-controls {
        flex-direction: column;
        gap: 2px;
      }
      
      .qty-btn {
        width: 24px;
        height: 24px;
      }
      
      .qty-input {
        width: 40px;
      }
      
      /* On mobile, show 1 medicine per row for better readability */
      .medicine-list {
        grid-template-columns: 1fr !important;
      }
    }
    
    /* Custom Scrollbar */
    .medicine-list::-webkit-scrollbar {
      width: 6px;
    }
    
    .medicine-list::-webkit-scrollbar-track {
      background: #f1f1f1;
      border-radius: 3px;
    }
    
    .medicine-list::-webkit-scrollbar-thumb {
      background: #c1c1c1;
      border-radius: 3px;
    }
    
    .medicine-list::-webkit-scrollbar-thumb:hover {
      background: #a8a8a8;
    }
    
    /* Enhanced Low Stock Alert Modal */
    .low-stock-modal {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.8);
      z-index: 10000;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
      backdrop-filter: blur(10px);
      animation: fadeIn 0.4s ease-out;
    }
    
    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }
    
    .low-stock-modal-content {
      background: white;
      border-radius: 15px;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4);
      max-width: 600px;
      width: 100%;
      max-height: 60vh;
      overflow: hidden;
      animation: slideInScale 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
      position: relative;
      display: flex;
      flex-direction: column;
    }
    
    @keyframes slideInScale {
      0% {
        opacity: 0;
        transform: scale(0.8) translateY(-50px);
      }
      100% {
        opacity: 1;
        transform: scale(1) translateY(0);
      }
    }
    
    .low-stock-header {
      background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%);
      color: white;
      padding: 20px;
      text-align: center;
      position: relative;
      overflow: hidden;
    }
    
    .low-stock-header::before {
      content: '';
      position: absolute;
      top: -50%;
      left: -50%;
      width: 200%;
      height: 200%;
      background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 70%);
      animation: shimmer 4s ease-in-out infinite;
    }
    
    @keyframes shimmer {
      0%, 100% { transform: translateX(-100%) translateY(-100%) rotate(0deg); }
      50% { transform: translateX(100%) translateY(100%) rotate(180deg); }
    }
    
    .low-stock-header h3 {
      margin: 0;
      font-size: 1.6rem;
      font-weight: 700;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      position: relative;
      z-index: 1;
    }
    
    .low-stock-header .alert-icon {
      font-size: 2rem;
      animation: bounce 2.5s infinite;
    }
    
    @keyframes bounce {
      0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
      40% { transform: translateY(-12px); }
      60% { transform: translateY(-6px); }
    }
    
    .low-stock-header p {
      margin: 8px 0 0 0;
      opacity: 0.95;
      font-size: 1rem;
      position: relative;
      z-index: 1;
    }
    
    .low-stock-body {
      padding: 20px;
      flex: 1;
      overflow-y: auto;
      background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
      min-height: 0;
    }
    
    .stock-category {
      margin-bottom: 15px;
    }
    
    .category-header {
      display: flex;
      align-items: center;
      gap: 8px;
      margin-bottom: 10px;
      padding: 10px 14px;
      border-radius: 10px;
      font-weight: 700;
      font-size: 1rem;
      position: relative;
      overflow: hidden;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    .category-header::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      opacity: 0.1;
      background: linear-gradient(45deg, transparent 30%, rgba(255,255,255,0.4) 50%, transparent 70%);
      animation: slide 3s infinite;
    }
    
    @keyframes slide {
      0% { transform: translateX(-100%); }
      100% { transform: translateX(100%); }
    }
    
    .category-critical {
      background: linear-gradient(135deg, #ffebee 0%, #ffcdd2 100%);
      color: #c62828;
      border-left: 8px solid #d32f2f;
    }
    
    .category-low {
      background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%);
      color: #ef6c00;
      border-left: 8px solid #ff9800;
    }
    
    .category-out {
      background: linear-gradient(135deg, #f3e5f5 0%, #e1bee7 100%);
      color: #7b1fa2;
      border-left: 8px solid #9c27b0;
    }
    
    .medicine-item {
      background: white;
      border: 2px solid #e8e8e8;
      border-radius: 10px;
      padding: 12px;
      margin-bottom: 8px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
      position: relative;
      overflow: hidden;
    }
    
    .medicine-item::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,0.5), transparent);
      transition: left 0.8s;
    }
    
    .medicine-item:hover::before {
      left: 100%;
    }
    
    .medicine-item:hover {
      transform: translateY(-4px) scale(1.02);
      box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
      border-color: #667eea;
    }
    
    .medicine-info {
      flex: 1;
    }
    
    .medicine-item-name {
      font-weight: 700;
      color: #2c3e50;
      margin-bottom: 4px;
      font-size: 1rem;
      line-height: 1.3;
    }
    
    .medicine-item-details {
      font-size: 0.9rem;
      color: #6c757d;
      display: flex;
      gap: 15px;
      align-items: center;
    }
    
    .medicine-item-details span {
      display: flex;
      align-items: center;
      gap: 8px;
    }
    
    .stock-indicator {
      display: flex;
      align-items: center;
      gap: 8px;
      font-weight: 700;
      font-size: 0.9rem;
      padding: 8px 12px;
      border-radius: 20px;
      background: rgba(0,0,0,0.05);
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }
    
    .stock-critical {
      color: #d32f2f;
      background: rgba(211, 47, 47, 0.1);
    }
    
    .stock-low {
      color: #f57c00;
      background: rgba(245, 124, 0, 0.1);
    }
    
    .stock-out {
      color: #7b1fa2;
      background: rgba(123, 31, 162, 0.1);
    }
    
    .low-stock-footer {
      background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
      padding: 15px 20px;
      display: flex;
      justify-content: center;
      align-items: center;
      border-top: 2px solid #e9ecef;
      flex-shrink: 0;
    }
    
    .alert-actions {
      display: flex;
      gap: 15px;
    }
    
    .btn-dismiss {
      background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
      color: white;
      border: none;
      padding: 10px 20px;
      border-radius: 12px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      gap: 8px;
      box-shadow: 0 3px 10px rgba(108, 117, 125, 0.3);
      font-size: 0.9rem;
    }
    
    .btn-dismiss:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(108, 117, 125, 0.4);
    }
    
    .btn-manage {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      border: none;
      padding: 10px 20px;
      border-radius: 12px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      gap: 8px;
      box-shadow: 0 3px 10px rgba(102, 126, 234, 0.3);
      font-size: 0.9rem;
    }
    
    .btn-manage:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
    }
    
    /* Low Stock Badge in Header */
    .low-stock-badge {
      background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%);
      color: white;
      padding: 10px 20px;
      border-radius: 30px;
      font-size: 0.9rem;
      font-weight: 700;
      margin-left: 25px;
      animation: pulse 2.5s infinite;
      display: inline-flex;
      align-items: center;
      gap: 10px;
      box-shadow: 0 5px 20px rgba(255, 107, 107, 0.3);
    }
    
    @keyframes pulse {
      0%, 100% { transform: scale(1); }
      50% { transform: scale(1.05); }
    }
    
    /* Responsive adjustments for low stock modal */
    @media (max-width: 768px) {
      .low-stock-modal {
        padding: 10px;
      }
      
      .low-stock-modal-content {
        max-height: 80vh;
        border-radius: 15px;
        max-width: 95%;
      }
      
      .low-stock-header {
        padding: 15px;
      }
      
      .low-stock-header h3 {
        font-size: 1.4rem;
        flex-direction: column;
        gap: 8px;
      }
      
      .low-stock-body {
        padding: 15px;
      }
      
      .medicine-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
        padding: 10px;
      }
      
      .medicine-item-details {
        flex-direction: column;
        gap: 6px;
        align-items: flex-start;
      }
      
      .low-stock-footer {
        flex-direction: column;
        gap: 12px;
        align-items: stretch;
        padding: 12px;
      }
      
      .alert-actions {
        justify-content: center;
        flex-wrap: wrap;
        gap: 8px;
      }
      
      .low-stock-badge {
        font-size: 0.8rem;
        padding: 8px 16px;
        margin-left: 15px;
      }
    }
  </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed">
  <!-- Site wrapper -->
  <div class="wrapper">
    <!-- Navbar -->
    <?php include './config/header.php';
include './config/sidebar.php';?>  
    
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
      <!-- Content Header (Page header) -->
      

      <!-- Main content -->
      <section class="content">
        <div class="pos-container">
          <div class="container-fluid">
            <div class="pos-card">
              <div class="pos-header">
                <h3><i class="fas fa-cash-register"></i> Point of Sale</h3>
              </div>
              
              <div class="pos-body">
                <div class="row">
                  <!-- Left Column - Medicine Selection -->
                  <div class="col-lg-6">
                    <!-- Patient Selection -->
                    <div class="patient-section">
            <div class="row">
                <div class="col-md-6">
                        <label for="pos_patient" class="form-label">Patient (optional)</label>
                        <select id="pos_patient" name="pos_patient" class="form-control">
                            <option value="">Walk-in / Not Selected</option>
                        </select>
                    </div>
                        <div class="col-md-6">
                        <label for="pos_patient_name" class="form-label">Patient Name (if not listed)</label>
                          <input type="text" id="pos_patient_name" name="pos_patient_name" class="form-control" placeholder="Type patient name for walk-in" />
                        </div>
                      </div>
                    </div>

                    <!-- Latest Prescription -->
                    <div class="prescription-section">
                      <label class="form-label">Latest Prescription</label>
                      <div id="latest_rx" style="max-height: 200px; overflow-y: auto;">
                        <em>Select a patient to load latest prescription.</em>
                      </div>
                    </div>

                    <!-- Medicine Search -->
                    <div class="search-container">
                      
                      <input type="text" id="search_medicine" name="search_medicine" class="form-control search-input" placeholder="Search medicines by name, generic name, or brand...">
                      <button class="clear-search-btn" id="btnClearSearch" type="button" title="Clear search">
                        <i class="fas fa-times"></i>
                        
                      </button>
                    </div>

                    <!-- Medicine List -->
                    <div class="medicine-list" id="medicine_list" style="max-height: 500px; overflow-y: auto; display: grid; grid-template-columns: 1fr 1fr; gap: 15px; align-content: flex-start;">
                        <!-- Medicines will be loaded here via AJAX -->
                    </div>
                </div>

                  <!-- Right Column - Cart -->
                  <div class="col-lg-6">
                    <div class="cart-container">
                      <div class="cart-header">
                        <h4 class="cart-title"></h4>
                        <div>
                          <span class="item-count-badge" id="badgeItemCount">0 items</span>
                          <button class="clear-cart-btn" id="btnClearCart" type="button" title="Clear cart">
                            <i class="fas fa-trash"></i> Clear
                          </button>
                        </div>
                      </div>
                      
                    <div class="table-responsive">
                        <table class="table cart-table" id="selected_items_table">
                        <thead>
                            <tr>
                              <th style="width: 30%;">Medicine</th>
                              <th style="width: 15%;">Packing</th>
                              <th style="width: 15%;">Price</th>
                              <th style="width: 20%;">Quantity</th>
                              <th style="width: 15%;">Subtotal</th>
                              <th style="width: 5%;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Selected items will be appended here -->
                        </tbody>
                        <tfoot>
                            <tr class="total-row">
                              <td colspan="4" class="text-right"><strong>Total:</strong></td>
                              <td id="grand_total"><strong>₱0.00</strong></td>
                              <td></td>
                            </tr>
                        </tfoot>
                    </table>
                    </div>
                      
                    <input type="hidden" id="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>" />
                      <button class="process-sale-btn" id="process_sale" disabled>
                        <i class="fas fa-credit-card"></i> Process Supply
                      </button>
                    </div>
                  </div>
                </div>
                </div>
            </div>
          </div>
        </div>
      </section>
      <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->
  </div>
  <!-- ./wrapper -->

  <?php include './config/site_js_links.php'; ?>
  <?php include './config/data_tables_js.php'; ?>
  <script>
    showMenuSelected("#mnu_pos", "");

    var message = '<?php echo $message;?>';

    if(message !== '') {
      showCustomMessage(message);
    }
    
    // AJAX functions for POS will go here
    $(document).ready(function() {
        let selectedItems = {};
        let medIndex = {};

        function loadMedicines() {
            // Show loading state
            $('#medicine_list').html('<div class="text-center p-4"><div class="spinner"></div><br><small>Loading medicines...</small></div>');
            
            $.ajax({
                url: "ajax/pos_actions.php",
                type: "POST",
                dataType: "json",
                data: { action: "get_medicines_for_pos" },
                success: function(response) {
                    let medicines = response.data || [];
                    let medicineHtml = '';
                    medIndex = {};
                    let lowStockItems = [];
                    
                    medicines.forEach(med => {
                        medIndex[parseInt(med.medicine_detail_id)] = {
                            id: parseInt(med.medicine_detail_id),
                            name: med.medicine_name,
                            packing: med.packing,
                            price: parseFloat(med.price),
                            stock: parseInt(med.quantity_stock)
                        };
                        
                        // Track low stock items
                        if (med.quantity_stock <= 10) {
                            lowStockItems.push({
                                name: med.medicine_name,
                                packing: med.packing,
                                stock: med.quantity_stock
                            });
                        }
                        
                        const stockClass = med.quantity_stock <= 5 ? 'critical' : (med.quantity_stock <= 10 ? 'low' : 'good');
                        const stockText = med.quantity_stock <= 5 ? '⚠ Critical' : (med.quantity_stock <= 10 ? '⚠ Low' : med.quantity_stock);
                        
                        medicineHtml += `
                            <div class="medicine-card" data-id="${med.medicine_detail_id}">
                                <div class="stock-badge ${stockClass}">${stockText}</div>
                                <div class="medicine-name" title="${med.medicine_name}">${med.medicine_name}</div>
                                <div class="medicine-packing">${med.packing}</div>
                                <div class="medicine-price">₱${parseFloat(med.price).toFixed(2)}</div>
                                <button class="add-btn add-to-cart" 
                                        data-id="${med.medicine_detail_id}" 
                                        data-name="${med.medicine_name}"
                                        data-packing="${med.packing}"
                                        data-price="${med.price}"
                                        data-stock="${med.quantity_stock}"
                                        ${med.quantity_stock <= 0 ? 'disabled' : ''}>
                                    ${med.quantity_stock <= 0 ? 'Out of Stock' : 'Add'}
                                </button>
                            </div>
                        `;
                    });
                    $('#medicine_list').html(medicineHtml);
                    
                    // Update low stock badge in header
                    updateLowStockBadge(lowStockItems.length);
                    
                    // Show enhanced low stock alert if any items are low
                    if (lowStockItems.length > 0) {
                        showEnhancedLowStockAlert(lowStockItems);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading medicines:', error);
                    $('#medicine_list').html('<div class="alert alert-danger">Error loading medicines: ' + error + '</div>');
                }
            });
        }

        function loadTodayPatients(){
            $.ajax({
                url: 'ajax/pos_actions.php',
                type: 'POST',
                dataType: 'json',
                data: { action: 'get_today_rx_patients' },
                success: function(res){
                    var $sel = $('#pos_patient');
                    $sel.empty();
                    $sel.append('<option value="">Walk-in / Not Selected</option>');
                    if (res.status === 'success' && res.data && res.data.length) {
                        res.data.forEach(function(r){
                            var name = (r.patient_name||'') + (r.phone_number?(' ('+r.phone_number+')'):'');
                            $sel.append('<option value="'+r.id+'">'+ $('<div>').text(name).html() +'</option>');
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading patients:', error);
                }
            });
        }

        function updateCart() {
            let cartHtml = '';
            let grandTotal = 0;
            let itemCount = 0;
            for (let id in selectedItems) {
                let item = selectedItems[id];
                let subtotal = item.qty * parseFloat(item.price);
                grandTotal += subtotal;
                itemCount += item.qty;
                cartHtml += `
                    <tr data-id="${item.id}">
                        <td><strong>${item.name}</strong></td>
                        <td>${item.packing}</td>
                        <td>₱${parseFloat(item.price).toFixed(2)}</td>
                        <td class="text-center">
                          <div class="quantity-controls">
                            <button class="qty-btn btn-qty-dec" type="button">-</button>
                            <input type="number" id="qty_${item.id}" name="qty_${item.id}" class="qty-input" value="${item.qty}" min="1" max="${item.stock}">
                            <button class="qty-btn btn-qty-inc" type="button">+</button>
                          </div>
                        </td>
                        <td class="subtotal">₱${subtotal.toFixed(2)}</td>
                        <td class="text-center"><button class="remove-btn remove-from-cart">×</button></td>
                    </tr>
                `;
            }
            $('#selected_items_table tbody').html(cartHtml);
            $('#grand_total').text('₱' + grandTotal.toFixed(2));
            $('#badgeItemCount').text(itemCount + ' items');
            $('#process_sale').prop('disabled', grandTotal <= 0);
            
            // Update medicine cards to show added state
            $('.medicine-card').removeClass('added');
            for (let id in selectedItems) {
                $(`.medicine-card[data-id="${id}"]`).addClass('added');
            }
        }

        function loadLatestPrescription() {
            const pid = $('#pos_patient').val();
            if (!pid) {
                $('#latest_rx').html('<em>Select a patient to load latest prescription.</em>');
                return;
            }
            $.ajax({
                url: 'ajax/pos_actions.php',
                type: 'POST',
                dataType: 'json',
                data: { action: 'get_latest_prescription', patient_id: pid },
                success: function(res) {
                    if (res.status === 'success' && res.data && res.data.length) {
                        const rows = res.data.map(x => {
                            // Improved stock checking logic
                            const hasMedicineDetails = !!x.medicine_detail_id;
                            const hasStock = parseInt(x.quantity_stock || 0) > 0;
                            const isStatusInStock = x.status === 'in_stock';
                            
                            // Medicine is available if it has details and stock, OR if it's marked as in_stock
                            const inStock = hasMedicineDetails && hasStock;
                            const isPrescribed = true; // All items in prescription are prescribed
                            
                            // Show different messages based on the situation
                            let statusHtml, btnHtml, titleText;
                            
                            if (inStock) {
                                statusHtml = '<span class="text-success">✅ In Stock</span>';
                                btnHtml = `<button class="add-btn add-rx-item" data-id="${x.medicine_detail_id}" data-name="${x.medicine_name}" data-packing="${x.packing || ''}" data-price="${x.price || 0}" data-stock="${x.quantity_stock || 0}">Add to Cart</button>`;
                                titleText = `Available: ${x.quantity_stock || 0}`;
                            } else if (hasMedicineDetails && !hasStock) {
                                statusHtml = '<span class="text-danger">❌ Out of Stock</span>';
                                btnHtml = `<button class="add-btn" disabled title="No stock available">Out of Stock</button>`;
                                titleText = 'No stock available';
                            } else if (!hasMedicineDetails) {
                                statusHtml = '<span class="text-warning">⚠️ Not in Inventory</span>';
                                btnHtml = `<button class="add-btn" disabled title="Medicine not found in inventory">Not in Inventory</button>`;
                                titleText = 'Medicine not found in inventory';
                            } else {
                                statusHtml = '<span class="text-warning">⚠️ Not Available</span>';
                                btnHtml = `<button class="add-btn" disabled title="Medicine not available">Not Available</button>`;
                                titleText = 'Medicine not available';
                            }
                            return `<div class="prescription-item">
                                <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="font-weight-bold">${x.medicine_name}</div>
                                    <small class="text-muted">${x.packing || ''} | Stock: ${x.quantity_stock || 0} | Price: ₱${(x.price || 0)}</small>
                                </div>
                                <div class="text-right">
                                    <div>${statusHtml}</div>
                                    <div class="text-muted">Prescribed: ${x.quantity}</div>
                                    ${btnHtml}
                                    </div>
                                </div>
                            </div>`;
                        }).join('');
                        $('#latest_rx').html(rows);
                    } else {
                        $('#latest_rx').html('<em>No prescription found for this patient.</em>');
                    }
                },
                error: function() {
                    $('#latest_rx').html('<em>Failed to load prescription.</em>');
                }
            });
        }


        $('#medicine_list').on('click', '.add-to-cart', function() {
            let $btn = $(this);
            let id = $btn.data('id');
            let name = $btn.data('name');
            let packing = $btn.data('packing');
            let price = $btn.data('price');
            let stock = $btn.data('stock');

            // Add visual feedback
            $btn.prop('disabled', true).html('<div class="spinner" style="width: 12px; height: 12px; border-width: 2px;"></div>');
            
            setTimeout(() => {
            if (selectedItems[id]) {
                if (selectedItems[id].qty < stock) {
                    selectedItems[id].qty++;
                } else {
                    showCustomMessage("Cannot add more than available stock!");
                        $btn.prop('disabled', false).text('Add to Cart');
                        return;
                }
            } else {
                selectedItems[id] = { id: id, name: name, packing: packing, price: price, stock: stock, qty: 1 };
            }
            updateCart();
                
                // Reset button with success feedback
                $btn.text('✓ Added').removeClass('add-btn').addClass('btn-success');
                setTimeout(() => {
                    $btn.text('Add to Cart').removeClass('btn-success').addClass('add-btn').prop('disabled', false);
                }, 1000);
            }, 300);
        });

        $('#latest_rx').on('click', '.add-rx-item', function() {
            let id = parseInt($(this).data('id'));
            let name = $(this).data('name');
            let packing = $(this).data('packing');
            let price = parseFloat($(this).data('price'));
            let stock = parseInt($(this).data('stock'));
            
            if (!selectedItems[id]) {
                selectedItems[id] = { id: id, name: name, packing: packing, price: price, stock: stock, qty: 1 };
            } else {
                if (selectedItems[id].qty < stock) {
                    selectedItems[id].qty += 1;
                } else {
                    showCustomMessage('Cannot add more than available stock!');
                }
            }
            updateCart();
        });


        $('#selected_items_table').on('change', '.qty-input', function() {
            let id = $(this).closest('tr').data('id');
            let newQty = parseInt($(this).val());
            if (newQty > selectedItems[id].stock) {
                showCustomMessage("Cannot add more than available stock!");
                $(this).val(selectedItems[id].qty); // Revert to previous quantity
            } else if (newQty < 1) {
                showCustomMessage("Quantity cannot be less than 1!");
                $(this).val(selectedItems[id].qty);
            } else {
                selectedItems[id].qty = newQty;
            }
            updateCart();
        });

        $('#selected_items_table').on('click', '.btn-qty-dec', function(){
            const $tr = $(this).closest('tr');
            const id = $tr.data('id');
            if (selectedItems[id].qty > 1) { selectedItems[id].qty -= 1; updateCart(); }
        });
        $('#selected_items_table').on('click', '.btn-qty-inc', function(){
            const $tr = $(this).closest('tr');
            const id = $tr.data('id');
            if (selectedItems[id].qty < selectedItems[id].stock) { selectedItems[id].qty += 1; updateCart(); } else { showCustomMessage('Cannot add more than available stock!'); }
        });

        $('#selected_items_table').on('click', '.remove-from-cart', function() {
            let id = $(this).closest('tr').data('id');
            delete selectedItems[id];
            updateCart();
        });

        (function(){
            let timer = null;
            function doFilter(){
                let searchTerm = $('#search_medicine').val().toLowerCase();
                let visibleCount = 0;
                $('#medicine_list > div').each(function() {
                    let medicineText = $(this).text().toLowerCase();
                    if (medicineText.includes(searchTerm)) {
                        $(this).show();
                        visibleCount++;
                    } else {
                        $(this).hide();
                    }
                });
                
                // Show no results message if needed
                if (visibleCount === 0 && searchTerm.length > 0) {
                    if ($('#no-results').length === 0) {
                        $('#medicine_list').append('<div id="no-results" class="text-center p-4 text-muted"><i class="fas fa-search"></i><br>No medicines found matching your search.</div>');
                    }
                } else {
                    $('#no-results').remove();
                }
            }
            
            $('#search_medicine').on('keyup', function(){
                clearTimeout(timer);
                timer = setTimeout(doFilter, 150);
            });
            
            $('#btnClearSearch').on('click', function(){
                $('#search_medicine').val('').focus();
                doFilter();
            });
            
            // Keyboard shortcuts
            $(document).on('keydown', function(e) {
                // Ctrl/Cmd + F to focus search
                if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
                    e.preventDefault();
                    $('#search_medicine').focus();
                }
                
                // Escape to clear search
                if (e.key === 'Escape') {
                    $('#search_medicine').val('').blur();
                    doFilter();
                }
            });
        })();

        $('#pos_patient').on('change', function() {
            loadLatestPrescription();
        });

        $('#btnClearCart').on('click', function(){ selectedItems = {}; updateCart(); });

        $('#process_sale').on('click', function() {
            if (Object.keys(selectedItems).length === 0) {
                showCustomMessage("Please add items to the cart before processing sale.");
                return;
            }

            if (confirm("Confirm sale?")) {
                const $btn = $(this);
                const originalText = $btn.html();
                
                // Show loading state
                $btn.prop('disabled', true).html('<div class="spinner"></div> Processing Sale...');
                
                $.ajax({
                    url: "ajax/pos_actions.php",
                    type: "POST",
                    data: { action: "process_sale", items: JSON.stringify(selectedItems), patient_id: $('#pos_patient').val(), patient_name: $('#pos_patient_name').val().trim(), csrf_token: $('#csrf_token').val() },
                    success: function(response) {
                        let res = response;
                        if (typeof response === 'string') {
                            try {
                                res = JSON.parse(response);
                            } catch(e) {
                                console.error('JSON parse error:', e);
                                showCustomMessage("Error processing response: " + e.message);
                                return;
                            }
                        }
                        if (res.status === "success") {
                            showCustomMessage(res.message, 'success');
                            if (res.receipt) {
                                const win = window.open('print_receipt.php' + (res.receipt.sale_id ? ('?sale_id=' + res.receipt.sale_id) : ''), '_blank');
                                if (win) { win.focus(); }
                            }
                            selectedItems = {}; // Clear cart
                            updateCart();
                            loadMedicines(); // Refresh available medicines (stock might change)
                        } else {
                            showCustomMessage("Error processing sale: " + res.message, 'error');
                        }
                        
                        // Reset button
                        $btn.prop('disabled', false).html(originalText);
                    },
                    error: function(jqXhr, textStatus, errorMessage) {
                        showCustomMessage(errorMessage, 'error');
                        // Reset button
                        $btn.prop('disabled', false).html(originalText);
                    }
                });
            }
        });

        // Enhanced Low Stock Alert Modal Function
        function showEnhancedLowStockAlert(lowStockItems) {
            // Categorize items by stock level
            const criticalItems = lowStockItems.filter(item => item.stock <= 5);
            const lowItems = lowStockItems.filter(item => item.stock > 5 && item.stock <= 10);
            const outOfStockItems = lowStockItems.filter(item => item.stock <= 0);
            
            let modalHtml = `
                <div class="low-stock-modal" id="lowStockModal">
                    <div class="low-stock-modal-content">
                        <div class="low-stock-header">
                            <h3>
                                <i class="fas fa-exclamation-triangle alert-icon"></i>
                                Low Stock Alert
                            </h3>
                            <p>${lowStockItems.length} medicine(s) need attention</p>
                        </div>
                        <div class="low-stock-body">
            `;
            
            // Critical Stock Section
            if (criticalItems.length > 0) {
                modalHtml += `
                    <div class="stock-category">
                        <div class="category-header category-critical">
                            <i class="fas fa-exclamation-circle"></i>
                            Critical Stock (${criticalItems.length} items)
                        </div>
                `;
                criticalItems.forEach(item => {
                    modalHtml += `
                        <div class="medicine-item">
                            <div class="medicine-info">
                                <div class="medicine-item-name">${item.name}</div>
                                <div class="medicine-item-details">
                                    <span><i class="fas fa-box"></i> ${item.packing}</span>
                                </div>
                            </div>
                            <div class="stock-indicator stock-critical">
                                <i class="fas fa-exclamation-triangle"></i>
                                ${item.stock} remaining
                            </div>
                        </div>
                    `;
                });
                modalHtml += '</div>';
            }
            
            // Low Stock Section
            if (lowItems.length > 0) {
                modalHtml += `
                    <div class="stock-category">
                        <div class="category-header category-low">
                            <i class="fas fa-exclamation-triangle"></i>
                            Low Stock (${lowItems.length} items)
                        </div>
                `;
                lowItems.forEach(item => {
                    modalHtml += `
                        <div class="medicine-item">
                            <div class="medicine-info">
                                <div class="medicine-item-name">${item.name}</div>
                                <div class="medicine-item-details">
                                    <span><i class="fas fa-box"></i> ${item.packing}</span>
                                </div>
                            </div>
                            <div class="stock-indicator stock-low">
                                <i class="fas fa-exclamation-triangle"></i>
                                ${item.stock} remaining
                            </div>
                        </div>
                    `;
                });
                modalHtml += '</div>';
            }
            
            // Out of Stock Section
            if (outOfStockItems.length > 0) {
                modalHtml += `
                    <div class="stock-category">
                        <div class="category-header category-out">
                            <i class="fas fa-times-circle"></i>
                            Out of Stock (${outOfStockItems.length} items)
                        </div>
                `;
                outOfStockItems.forEach(item => {
                    modalHtml += `
                        <div class="medicine-item">
                            <div class="medicine-info">
                                <div class="medicine-item-name">${item.name}</div>
                                <div class="medicine-item-details">
                                    <span><i class="fas fa-box"></i> ${item.packing}</span>
                                </div>
                            </div>
                            <div class="stock-indicator stock-out">
                                <i class="fas fa-times-circle"></i>
                                Out of Stock
                            </div>
                        </div>
                    `;
                });
                modalHtml += '</div>';
            }
            
            modalHtml += `
                        </div>
                        <div class="low-stock-footer">
                            <div class="alert-actions">
                                <button class="btn-dismiss" id="btnDismissLowStock">
                                    <i class="fas fa-times"></i> Dismiss
                                </button>
                                <button class="btn-manage" id="btnManageInventory">
                                    <i class="fas fa-cog"></i> Manage Inventory
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            // Remove any existing modal
            $('#lowStockModal').remove();
            
            // Add modal to body
            $('body').append(modalHtml);
            
            // Add event handlers for buttons
            $('#btnDismissLowStock').on('click', function() {
                dismissLowStockAlert();
            });
            
            $('#btnManageInventory').on('click', function() {
                goToMedicineManagement();
            });
            
            // Add click outside to close
            $('#lowStockModal').on('click', function(e) {
                if (e.target === this) {
                    dismissLowStockAlert();
                }
            });
            
            // Add escape key to close
            $(document).on('keydown.lowStockModal', function(e) {
                if (e.key === 'Escape') {
                    dismissLowStockAlert();
                }
            });
        }
        
        // Dismiss Low Stock Alert Function
        function dismissLowStockAlert() {
            $('#lowStockModal').fadeOut(300, function() {
                $(this).remove();
            });
            
            // Remove escape key listener
            $(document).off('keydown.lowStockModal');
        }
        
        // Go to Medicine Management Function
        function goToMedicineManagement() {
            dismissLowStockAlert();
            // Navigate to medicines page
            window.location.href = 'medicines.php';
        }

        // Add low stock notification badge to header
        function updateLowStockBadge(lowStockCount) {
            if (lowStockCount > 0) {
                if ($('#lowStockBadge').length === 0) {
                    $('.pos-header h3').after('<span id="lowStockBadge" class="low-stock-badge"><i class="fas fa-exclamation-triangle"></i>' + lowStockCount + ' Low Stock</span>');
                } else {
                    $('#lowStockBadge').html('<i class="fas fa-exclamation-triangle"></i>' + lowStockCount + ' Low Stock');
                }
            } else {
                $('#lowStockBadge').remove();
            }
        }

        // Enhanced custom message function
        function showCustomMessage(message, type = 'info') {
            const alertClass = {
                'success': 'alert-success',
                'error': 'alert-danger',
                'warning': 'alert-warning',
                'info': 'alert-info'
            }[type] || 'alert-info';
            
            const icon = {
                'success': 'fas fa-check-circle',
                'error': 'fas fa-exclamation-circle',
                'warning': 'fas fa-exclamation-triangle',
                'info': 'fas fa-info-circle'
            }[type] || 'fas fa-info-circle';
            
            const alertHtml = `
                <div class="alert ${alertClass} alert-dismissible fade show" role="alert" style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px; max-width: 500px;">
                    <i class="${icon}"></i> ${message}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            `;
            
            $('body').append(alertHtml);
            
            // Auto-dismiss after 5 seconds
            setTimeout(function() {
                $('.alert').fadeOut(500, function() {
                    $(this).remove();
                });
            }, 5000);
        }

        loadMedicines();
        loadTodayPatients();
        loadLatestPrescription();
    });
  </script>
</body>
</html>
