// School Orders JavaScript
document.addEventListener('DOMContentLoaded', function() {
  // Check if user is authenticated
  checkAuth();
  
  // Set up event listeners
  setupEventListeners();
  
  // Initialize filters
  initializeFilters();
  
  // Initialize date pickers if needed
  initializeDatePickers();
  
  // Load initial data
  loadOrdersData();
});

// Check if user is authenticated as school
function checkAuth() {
  fetch('api/check_session.php')
    .then(response => response.json())
    .then(data => {
      if (!data.authenticated || data.user_type !== 'school') {
        window.location.href = 'school_login.php';
      } else {
        // User is authenticated, update UI with school name
        document.getElementById('schoolName').textContent = data.school_name || 'School Dashboard';
        
        // Set school logo if available
        if (data.school_logo) {
          const logoElement = document.getElementById('schoolLogo');
          if (logoElement) {
            logoElement.src = data.school_logo;
            logoElement.alt = data.school_name + ' Logo';
          }
        }
      }
    })
    .catch(error => {
      console.error('Authentication check failed:', error);
      window.location.href = 'school_login.php';
    });
}

// Set up event listeners
function setupEventListeners() {
  // Refresh orders button
  const refreshBtn = document.getElementById('refreshOrdersBtn');
  if (refreshBtn) {
    refreshBtn.addEventListener('click', function() {
      loadOrdersData();
    });
  }
  
  // Export orders button
  const exportBtn = document.getElementById('exportOrdersBtn');
  if (exportBtn) {
    exportBtn.addEventListener('click', function() {
      exportOrders();
    });
  }
  
  // Apply filters button
  const applyFiltersBtn = document.getElementById('applyFiltersBtn');
  if (applyFiltersBtn) {
    applyFiltersBtn.addEventListener('click', function() {
      loadOrdersData(1); // Load first page with filters
    });
  }
  
  // Reset filters button
  const resetFiltersBtn = document.getElementById('resetFiltersBtn');
  if (resetFiltersBtn) {
    resetFiltersBtn.addEventListener('click', function() {
      resetFilters();
    });
  }
  
  // Date range filter change
  const dateRangeFilter = document.getElementById('dateRangeFilter');
  if (dateRangeFilter) {
    dateRangeFilter.addEventListener('change', function() {
      toggleCustomDateInputs();
    });
  }
  
  // Select all orders checkbox
  const selectAllCheckbox = document.getElementById('selectAllOrders');
  if (selectAllCheckbox) {
    selectAllCheckbox.addEventListener('change', function() {
      toggleAllCheckboxes(this.checked);
    });
  }
  
  // Bulk action buttons
  setupBulkActionButtons();
  
  // Logout button
  const logoutBtn = document.getElementById('logoutBtn');
  if (logoutBtn) {
    logoutBtn.addEventListener('click', function(e) {
      e.preventDefault();
      logout();
    });
  }
}

// Initialize filters
function initializeFilters() {
  // Set current date for end date if it exists
  const endDateInput = document.getElementById('endDate');
  if (endDateInput) {
    const today = new Date().toISOString().split('T')[0];
    endDateInput.value = today;
  }
  
  // Set date 30 days ago for start date if it exists
  const startDateInput = document.getElementById('startDate');
  if (startDateInput) {
    const thirtyDaysAgo = new Date();
    thirtyDaysAgo.setDate(thirtyDaysAgo.getDate() - 30);
    startDateInput.value = thirtyDaysAgo.toISOString().split('T')[0];
  }
}

// Initialize date pickers
function initializeDatePickers() {
  // This is just a placeholder in case we need special date picker initialization
  // For now, we're using the native date inputs
}

// Toggle custom date inputs based on date range selection
function toggleCustomDateInputs() {
  const dateRangeFilter = document.getElementById('dateRangeFilter');
  const customDateContainer = document.getElementById('customDateContainer');
  
  if (!dateRangeFilter || !customDateContainer) return;
  
  if (dateRangeFilter.value === 'custom') {
    customDateContainer.classList.remove('d-none');
  } else {
    customDateContainer.classList.add('d-none');
  }
}

// Reset all filters to default values
function resetFilters() {
  document.getElementById('statusFilter').value = 'all';
  document.getElementById('dateRangeFilter').value = 'all';
  document.getElementById('classFilter').value = 'all';
  document.getElementById('searchOrders').value = '';
  
  // Hide custom date inputs
  const customDateContainer = document.getElementById('customDateContainer');
  if (customDateContainer) {
    customDateContainer.classList.add('d-none');
  }
  
  // Reload data with reset filters
  loadOrdersData(1);
}

// Load orders data with current filters
function loadOrdersData(page = 1) {
  // Show loading spinner
  const loadingSpinner = document.getElementById('ordersLoadingSpinner');
  const tableContainer = document.getElementById('ordersTableContainer');
  const noOrdersMessage = document.getElementById('noOrdersMessage');
  const paginationContainer = document.getElementById('ordersPagination');
  
  if (loadingSpinner) loadingSpinner.classList.remove('d-none');
  if (tableContainer) tableContainer.classList.add('d-none');
  if (noOrdersMessage) noOrdersMessage.classList.add('d-none');
  if (paginationContainer) paginationContainer.classList.add('d-none');
  
  // Get filter values
  const filters = getFilters();
  filters.page = page;
  
  // Convert filters to query params
  const queryParams = new URLSearchParams();
  Object.keys(filters).forEach(key => {
    if (filters[key] !== null && filters[key] !== '') {
      queryParams.append(key, filters[key]);
    }
  });
  
  // Fetch orders data
  fetch(`api/school_orders.php?${queryParams.toString()}`)
    .then(response => response.json())
    .then(data => {
      // Hide loading spinner
      if (loadingSpinner) loadingSpinner.classList.add('d-none');
      
      if (data.success) {
        // Update order statistics
        updateOrderStatistics(data.statistics);
        
        if (data.orders && data.orders.length > 0) {
          // Render orders table
          renderOrdersTable(data.orders);
          
          // Show table container
          if (tableContainer) tableContainer.classList.remove('d-none');
          
          // Render pagination
          if (paginationContainer) {
            renderPagination(data.pagination);
            paginationContainer.classList.remove('d-none');
          }
          
          // Enable/disable bulk action buttons based on selection
          updateBulkActionButtons();
        } else {
          // Show no orders message
          if (noOrdersMessage) noOrdersMessage.classList.remove('d-none');
        }
      } else {
        // Show error message
        showErrorToast(data.message || 'Failed to load orders');
        
        // Show no orders message
        if (noOrdersMessage) {
          noOrdersMessage.textContent = 'Error loading orders. Please try again.';
          noOrdersMessage.classList.remove('d-none');
          noOrdersMessage.classList.remove('alert-info');
          noOrdersMessage.classList.add('alert-danger');
        }
      }
    })
    .catch(error => {
      console.error('Failed to load orders:', error);
      
      // Hide loading spinner
      if (loadingSpinner) loadingSpinner.classList.add('d-none');
      
      // Show error message
      showErrorToast('Network error. Please check your connection and try again.');
      
      // Show no orders message with error
      if (noOrdersMessage) {
        noOrdersMessage.textContent = 'Network error. Please check your connection and try again.';
        noOrdersMessage.classList.remove('d-none');
        noOrdersMessage.classList.remove('alert-info');
        noOrdersMessage.classList.add('alert-danger');
      }
    });
}

// Get current filter values
function getFilters() {
  const status = document.getElementById('statusFilter').value;
  const dateRange = document.getElementById('dateRangeFilter').value;
  const studentClass = document.getElementById('classFilter').value;
  const searchQuery = document.getElementById('searchOrders').value.trim();
  
  const filters = {
    status: status !== 'all' ? status : null,
    date_range: dateRange !== 'all' ? dateRange : null,
    class: studentClass !== 'all' ? studentClass : null,
    search: searchQuery !== '' ? searchQuery : null
  };
  
  // Add custom date range if selected
  if (dateRange === 'custom') {
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;
    
    if (startDate) filters.start_date = startDate;
    if (endDate) filters.end_date = endDate;
  }
  
  return filters;
}

// Update order statistics display
function updateOrderStatistics(statistics) {
  if (!statistics) return;
  
  // Update total orders
  const totalOrdersElement = document.getElementById('totalOrdersCount');
  if (totalOrdersElement) {
    totalOrdersElement.textContent = statistics.total || 0;
    totalOrdersElement.classList.remove('d-none');
    const spinner = totalOrdersElement.previousElementSibling;
    if (spinner) spinner.classList.add('d-none');
  }
  
  // Update pending orders
  const pendingOrdersElement = document.getElementById('pendingOrdersCount');
  if (pendingOrdersElement) {
    pendingOrdersElement.textContent = statistics.pending || 0;
    pendingOrdersElement.classList.remove('d-none');
    const spinner = pendingOrdersElement.previousElementSibling;
    if (spinner) spinner.classList.add('d-none');
  }
  
  // Update processing orders
  const processingOrdersElement = document.getElementById('processingOrdersCount');
  if (processingOrdersElement) {
    processingOrdersElement.textContent = statistics.processing || 0;
    processingOrdersElement.classList.remove('d-none');
    const spinner = processingOrdersElement.previousElementSibling;
    if (spinner) spinner.classList.add('d-none');
  }
  
  // Update delivered orders
  const deliveredOrdersElement = document.getElementById('deliveredOrdersCount');
  if (deliveredOrdersElement) {
    deliveredOrdersElement.textContent = statistics.delivered || 0;
    deliveredOrdersElement.classList.remove('d-none');
    const spinner = deliveredOrdersElement.previousElementSibling;
    if (spinner) spinner.classList.add('d-none');
  }
}

// Render orders table
function renderOrdersTable(orders) {
  const tableBody = document.getElementById('ordersTableBody');
  if (!tableBody) return;
  
  // Clear existing rows
  tableBody.innerHTML = '';
  
  // Add order rows
  orders.forEach(order => {
    const row = createOrderRow(order);
    tableBody.appendChild(row);
  });
  
  // Add event listeners to action buttons
  addActionButtonListeners();
  
  // Add event listeners to checkboxes
  addCheckboxListeners();
}

// Create table row for an order
function createOrderRow(order) {
  // Create table row
  const row = document.createElement('tr');
  const order_date = new Date(order.created_at);
  
  // Format the date
  const formattedDate = order_date.toLocaleDateString('en-IN', {
    day: 'numeric',
    month: 'short',
    year: 'numeric'
  });
  
  // Format the time
  const formattedTime = order_date.toLocaleTimeString('en-IN', {
    hour: '2-digit',
    minute: '2-digit'
  });
  
  // Create student name and class displayed
  const studentName = order.student_name || '';
  const studentClass = order.class || '';
  
  // Get status badge color
  const status = order.status;
  let statusBadgeClass = '';
  let statusText = capitalizeFirstLetter(status || '');
  
  if (status === 'pending') statusBadgeClass = 'bg-warning';
  else if (status === 'processing') statusBadgeClass = 'bg-info';
  else if (status === 'ready') statusBadgeClass = 'bg-primary';
  else if (status === 'delivered') statusBadgeClass = 'bg-success';
  else if (status === 'cancelled') statusBadgeClass = 'bg-danger';
  else statusBadgeClass = 'bg-secondary';
  
  // Set row HTML
  row.innerHTML = `
    <td>
      <div class="form-check">
        <input class="form-check-input order-checkbox" type="checkbox" id="order-${order.id}" data-order-id="${order.id}">
      </div>
    </td>
    <td>
      <a href="#" class="text-primary view-order-btn" data-order-id="${order.id}">
        ${order.order_number}
      </a>
    </td>
    <td>
      <div>${formattedDate}</div>
      <small class="text-muted">${formattedTime}</small>
    </td>
    <td>${studentName}</td>
    <td>${studentClass}</td>
    <td class="text-center">${order.item_count}</td>
    <td class="text-end">₹${parseFloat(order.total_amount).toLocaleString('en-IN')}</td>
    <td>
      <span class="badge ${statusBadgeClass}">${statusText}</span>
    </td>
    <td>
      <button type="button" class="btn btn-sm btn-outline-primary view-order-btn" data-order-id="${order.id}">
        <i class="fas fa-eye"></i>
      </button>
    </td>
  `;
  
  return row;
}

// Add event listeners to action buttons
function addActionButtonListeners() {
  // View order buttons
  document.querySelectorAll('.view-order-btn').forEach(button => {
    button.addEventListener('click', function(e) {
      e.preventDefault();
      const orderId = this.getAttribute('data-order-id');
      viewOrderDetails(orderId);
    });
  });
  
  // Process order buttons
  document.querySelectorAll('.process-order-btn').forEach(button => {
    button.addEventListener('click', function() {
      const orderId = this.getAttribute('data-order-id');
      processOrder(orderId);
    });
  });
  
  // Complete order buttons
  document.querySelectorAll('.complete-order-btn').forEach(button => {
    button.addEventListener('click', function() {
      const orderId = this.getAttribute('data-order-id');
      markOrderAsDelivered(orderId);
    });
  });
}

// Add event listeners to checkboxes
function addCheckboxListeners() {
  document.querySelectorAll('.order-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', function() {
      updateBulkActionButtons();
      
      // Update select all checkbox state
      updateSelectAllCheckbox();
    });
  });
}

// Update select all checkbox state
function updateSelectAllCheckbox() {
  const selectAllCheckbox = document.getElementById('selectAllOrders');
  const orderCheckboxes = document.querySelectorAll('.order-checkbox');
  
  if (!selectAllCheckbox || orderCheckboxes.length === 0) return;
  
  const allChecked = Array.from(orderCheckboxes).every(checkbox => checkbox.checked);
  const someChecked = Array.from(orderCheckboxes).some(checkbox => checkbox.checked);
  
  selectAllCheckbox.checked = allChecked;
  selectAllCheckbox.indeterminate = someChecked && !allChecked;
}

// Toggle all checkboxes
function toggleAllCheckboxes(checked) {
  document.querySelectorAll('.order-checkbox').forEach(checkbox => {
    checkbox.checked = checked;
  });
  
  // Update bulk action buttons
  updateBulkActionButtons();
}

// Setup bulk action buttons
function setupBulkActionButtons() {
  // Process selected orders
  const bulkProcessBtn = document.getElementById('bulkProcessBtn');
  if (bulkProcessBtn) {
    bulkProcessBtn.addEventListener('click', function() {
      bulkUpdateOrderStatus('processing');
    });
  }
  
  // Mark selected orders as ready
  const bulkReadyBtn = document.getElementById('bulkReadyBtn');
  if (bulkReadyBtn) {
    bulkReadyBtn.addEventListener('click', function() {
      bulkUpdateOrderStatus('ready');
    });
  }
  
  // Mark selected orders as delivered
  const bulkDeliverBtn = document.getElementById('bulkDeliverBtn');
  if (bulkDeliverBtn) {
    bulkDeliverBtn.addEventListener('click', function() {
      bulkUpdateOrderStatus('delivered');
    });
  }
  
  // Cancel selected orders
  const bulkCancelBtn = document.getElementById('bulkCancelBtn');
  if (bulkCancelBtn) {
    bulkCancelBtn.addEventListener('click', function() {
      bulkUpdateOrderStatus('cancelled');
    });
  }
}

// Update bulk action buttons state
function updateBulkActionButtons() {
  const checkedOrders = document.querySelectorAll('.order-checkbox:checked');
  const hasCheckedOrders = checkedOrders.length > 0;
  
  // Bulk process button (for pending orders)
  const bulkProcessBtn = document.getElementById('bulkProcessBtn');
  const bulkReadyBtn = document.getElementById('bulkReadyBtn');
  const bulkDeliverBtn = document.getElementById('bulkDeliverBtn');
  const bulkCancelBtn = document.getElementById('bulkCancelBtn');
  
  if (bulkProcessBtn) bulkProcessBtn.disabled = !hasCheckedOrders;
  if (bulkReadyBtn) bulkReadyBtn.disabled = !hasCheckedOrders;
  if (bulkDeliverBtn) bulkDeliverBtn.disabled = !hasCheckedOrders;
  if (bulkCancelBtn) bulkCancelBtn.disabled = !hasCheckedOrders;
}

// Bulk update order status
function bulkUpdateOrderStatus(status) {
  const checkedOrderIds = Array.from(document.querySelectorAll('.order-checkbox:checked'))
    .map(checkbox => checkbox.getAttribute('data-order-id'));
  
  if (checkedOrderIds.length === 0) {
    showErrorToast('No orders selected');
    return;
  }
  
  // Show confirmation based on status
  let confirmMessage = 'Are you sure you want to update the status of the selected orders?';
  
  if (status === 'cancelled') {
    confirmMessage = 'Are you sure you want to cancel the selected orders? This action cannot be undone.';
  } else if (status === 'delivered') {
    confirmMessage = 'Are you sure you want to mark the selected orders as delivered?';
  }
  
  if (!confirm(confirmMessage)) {
    return;
  }
  
  // Show loading overlay
  showLoadingOverlay();
  
  // Make API request to update order statuses
  fetch('api/update_order_status.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      order_ids: checkedOrderIds,
      status: status
    })
  })
    .then(response => response.json())
    .then(data => {
      hideLoadingOverlay();
      
      if (data.success) {
        showSuccessToast(`Successfully updated ${data.updated_count} orders`);
        
        // Reload orders data
        loadOrdersData();
      } else {
        showErrorToast(data.message || 'Failed to update orders');
      }
    })
    .catch(error => {
      hideLoadingOverlay();
      console.error('Failed to update orders:', error);
      showErrorToast('Network error. Please try again.');
    });
}

// View order details
function viewOrderDetails(orderId) {
  // Show loading overlay
  showLoadingOverlay();
  
  // Fetch order details
  fetch(`api/order_details.php?id=${orderId}`)
    .then(response => response.json())
    .then(data => {
      hideLoadingOverlay();
      
      if (data.success) {
        displayOrderDetailsModal(data.order);
      } else {
        showErrorToast(data.message || 'Failed to load order details');
      }
    })
    .catch(error => {
      hideLoadingOverlay();
      console.error('Failed to load order details:', error);
      showErrorToast('Network error. Please try again.');
    });
}

// Display order details in modal
function displayOrderDetailsModal(order) {
  // Set order number and status
  document.getElementById('modal-order-number').textContent = order.order_number;
  
  // Set status badge
  const statusBadge = document.getElementById('modal-order-status');
  statusBadge.textContent = capitalizeFirstLetter(order.status);
  
  // Set status badge class
  statusBadge.className = 'badge ms-2';
  if (order.status === 'pending') statusBadge.classList.add('bg-warning');
  else if (order.status === 'processing') statusBadge.classList.add('bg-info');
  else if (order.status === 'ready') statusBadge.classList.add('bg-primary');
  else if (order.status === 'delivered') statusBadge.classList.add('bg-success');
  else if (order.status === 'cancelled') statusBadge.classList.add('bg-danger');
  else statusBadge.classList.add('bg-secondary');
  
  // Format date
  const orderDate = new Date(order.created_at);
  const formattedDate = orderDate.toLocaleDateString('en-IN', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
  
  // Set order information
  document.getElementById('modal-order-date').textContent = formattedDate;
  document.getElementById('modal-order-amount').textContent = `₹${parseFloat(order.total_amount).toLocaleString('en-IN')}`;
  document.getElementById('modal-payment-status').textContent = order.payment_status || 'N/A';
  
  // Set student information with null checks
  document.getElementById('modal-student-name').textContent = order.student_name || '';
  
  // Handle class info with null checks
  let studentClass = '';
  if (order.student_class) {
    studentClass = order.student_class;
    if (order.student_section) {
      studentClass += `-${order.student_section}`;
    }
  }
  document.getElementById('modal-student-class').textContent = studentClass;
  
  document.getElementById('modal-parent-name').textContent = order.parent_name || '';
  
  // Set order items
  const orderItemsContainer = document.getElementById('modal-order-items');
  orderItemsContainer.innerHTML = '';
  
  let totalAmount = 0;
  
  if (order.items && Array.isArray(order.items)) {
    order.items.forEach(item => {
      const row = document.createElement('tr');
      
      const itemTotal = parseFloat(item.unit_price) * parseInt(item.quantity);
      totalAmount += itemTotal;
      
      row.innerHTML = `
        <td>${item.name || ''}</td>
        <td>${capitalizeFirstLetter(item.type || 'N/A')}</td>
        <td class="text-center">${item.quantity}</td>
        <td class="text-end">₹${parseFloat(item.unit_price).toLocaleString('en-IN')}</td>
        <td class="text-end">₹${itemTotal.toLocaleString('en-IN')}</td>
      `;
      
      orderItemsContainer.appendChild(row);
    });
  }
  
  // Set total amount
  document.getElementById('modal-items-total').textContent = `₹${totalAmount.toLocaleString('en-IN')}`;
  
  // Set delivery note if exists
  const deliveryNoteContainer = document.getElementById('delivery-note-container');
  const modalDeliveryNote = document.getElementById('modal-delivery-note');
  
  if (order.delivery_note) {
    deliveryNoteContainer.classList.remove('d-none');
    modalDeliveryNote.textContent = order.delivery_note;
  } else {
    deliveryNoteContainer.classList.add('d-none');
  }
  
  // Set delivery address if exists
  const deliveryAddressContainer = document.getElementById('delivery-address-container');
  const modalDeliveryAddress = document.getElementById('modal-delivery-address');
  
  if (order.delivery_address) {
    deliveryAddressContainer.classList.remove('d-none');
    modalDeliveryAddress.textContent = order.delivery_address;
  } else {
    deliveryAddressContainer.classList.add('d-none');
  }
  
  // Update status buttons active state
  document.querySelectorAll('.update-status-btn').forEach(button => {
    button.classList.remove('active');
    
    if (button.getAttribute('data-status') === order.status) {
      button.classList.add('active');
    }
  });
  
  // Set up status update buttons
  document.querySelectorAll('.update-status-btn').forEach(button => {
    // Remove existing event listeners
    const newButton = button.cloneNode(true);
    button.parentNode.replaceChild(newButton, button);
    
    // Add new event listener
    newButton.addEventListener('click', function() {
      const newStatus = this.getAttribute('data-status');
      updateOrderStatus(order.id, newStatus);
    });
  });
  
  // Set print button link
  const printOrderBtn = document.getElementById('printOrderBtn');
  printOrderBtn.href = `order_print.php?id=${order.id}`;
  
  // Show modal
  const orderDetailsModal = new bootstrap.Modal(document.getElementById('orderDetailsModal'));
  orderDetailsModal.show();
}

// Update order status
function updateOrderStatus(orderId, newStatus) {
  // Show loading overlay
  showLoadingOverlay();
  
  // Make API request to update order status
  fetch('api/update_order_status.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      order_ids: [orderId],
      status: newStatus
    })
  })
    .then(response => response.json())
    .then(data => {
      hideLoadingOverlay();
      
      if (data.success) {
        showSuccessToast(`Order status updated to ${capitalizeFirstLetter(newStatus)}`);
        
        // Close modal
        const orderDetailsModal = bootstrap.Modal.getInstance(document.getElementById('orderDetailsModal'));
        if (orderDetailsModal) {
          orderDetailsModal.hide();
        }
        
        // Reload orders data
        loadOrdersData();
      } else {
        showErrorToast(data.message || 'Failed to update order status');
      }
    })
    .catch(error => {
      hideLoadingOverlay();
      console.error('Failed to update order status:', error);
      showErrorToast('Network error. Please try again.');
    });
}

// Process order (change status to processing)
function processOrder(orderId) {
  if (!confirm('Are you sure you want to process this order?')) {
    return;
  }
  // Show loading overlay
  showLoadingOverlay();
  
  // Make API request to update order status
  fetch('api/update_order_status.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      order_ids: [orderId],
      status: 'processing'
    })
  })
  .then(response => response.json())
  .then(data => {
    hideLoadingOverlay();
    
    if (data.success) {
      showSuccessToast(`Order status updated to Processing`);
      // Reload orders data
      loadOrdersData();
    } else {
      showErrorToast(data.message || 'Failed to update order status');
    }
  })
  .catch(error => {
    hideLoadingOverlay();
    console.error('Failed to update order status:', error);
    showErrorToast('Network error. Please try again.');
  });
}

// Mark order as delivered
function markOrderAsDelivered(orderId) {
  if (!confirm('Are you sure you want to mark this order as delivered?')) {
    return;
  }
  // Show loading overlay
  showLoadingOverlay();
  
  // Make API request to update order status
  fetch('api/update_order_status.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      order_ids: [orderId],
      status: 'delivered'
    })
  })
  .then(response => response.json())
  .then(data => {
    hideLoadingOverlay();
    
    if (data.success) {
      showSuccessToast(`Order status updated to Delivered`);
      // Reload orders data
      loadOrdersData();
    } else {
      showErrorToast(data.message || 'Failed to update order status');
    }
  })
  .catch(error => {
    hideLoadingOverlay();
    console.error('Failed to update order status:', error);
    showErrorToast('Network error. Please try again.');
  });
}

// Render pagination
function renderPagination(pagination) {
  if (!pagination) return;
  
  const paginationContainer = document.getElementById('paginationContainer');
  if (!paginationContainer) return;
  
  // Clear existing pagination
  paginationContainer.innerHTML = '';
  
  // Previous page button
  const prevLi = document.createElement('li');
  prevLi.className = `page-item ${pagination.current_page <= 1 ? 'disabled' : ''}`;
  prevLi.innerHTML = `
    <a class="page-link" href="#" aria-label="Previous" ${pagination.current_page > 1 ? `data-page="${pagination.current_page - 1}"` : ''}>
      <span aria-hidden="true">&laquo;</span>
    </a>
  `;
  paginationContainer.appendChild(prevLi);
  
  // Page numbers
  for (let i = 1; i <= pagination.total_pages; i++) {
    // If too many pages, add ellipsis
    if (pagination.total_pages > 7) {
      if (i !== 1 && i !== pagination.total_pages && (i < pagination.current_page - 1 || i > pagination.current_page + 1)) {
        // Skip pages too far from current page
        if (i === 2 || i === pagination.total_pages - 1) {
          const ellipsisLi = document.createElement('li');
          ellipsisLi.className = 'page-item disabled';
          ellipsisLi.innerHTML = '<span class="page-link">...</span>';
          paginationContainer.appendChild(ellipsisLi);
        }
        continue;
      }
    }
    
    const pageLi = document.createElement('li');
    pageLi.className = `page-item ${i === pagination.current_page ? 'active' : ''}`;
    pageLi.innerHTML = `<a class="page-link" href="#" data-page="${i}">${i}</a>`;
    paginationContainer.appendChild(pageLi);
  }
  
  // Next page button
  const nextLi = document.createElement('li');
  nextLi.className = `page-item ${pagination.current_page >= pagination.total_pages ? 'disabled' : ''}`;
  nextLi.innerHTML = `
    <a class="page-link" href="#" aria-label="Next" ${pagination.current_page < pagination.total_pages ? `data-page="${pagination.current_page + 1}"` : ''}>
      <span aria-hidden="true">&raquo;</span>
    </a>
  `;
  paginationContainer.appendChild(nextLi);
  
  // Add event listeners to pagination links
  document.querySelectorAll('.page-link[data-page]').forEach(link => {
    link.addEventListener('click', function(e) {
      e.preventDefault();
      const page = parseInt(this.getAttribute('data-page'));
      loadOrdersData(page);
      
      // Scroll to top of table
      document.querySelector('.card.shadow').scrollIntoView({ behavior: 'smooth' });
    });
  });
}

// Export orders with current filters
function exportOrders() {
  // Get filter values
  const filters = getFilters();
  
  // Convert filters to query params
  const queryParams = new URLSearchParams();
  Object.keys(filters).forEach(key => {
    if (filters[key] !== null && filters[key] !== '') {
      queryParams.append(key, filters[key]);
    }
  });
  
  // Redirect to export URL
  window.location.href = `export_orders.php?${queryParams.toString()}`;
}

// Handle logout
function logout() {
  fetch('api/logout.php')
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        window.location.href = 'school_login.php';
      } else {
        showErrorToast('Logout failed. Please try again.');
      }
    })
    .catch(error => {
      console.error('Logout error:', error);
      showErrorToast('Logout failed. Please try again.');
    });
}

// Show loading overlay
function showLoadingOverlay() {
  const loadingOverlay = document.getElementById('loading-overlay');
  if (loadingOverlay) {
    loadingOverlay.classList.remove('d-none');
  }
}

// Hide loading overlay
function hideLoadingOverlay() {
  const loadingOverlay = document.getElementById('loading-overlay');
  if (loadingOverlay) {
    loadingOverlay.classList.add('d-none');
  }
}

// Show success toast notification
function showSuccessToast(message) {
  showToast(message, 'success');
}

// Show error toast notification
function showErrorToast(message) {
  showToast(message, 'danger');
}

// Show toast notification
function showToast(message, type = 'info') {
  const toastContainer = document.createElement('div');
  toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
  toastContainer.style.zIndex = '1080';
  
  const icons = {
    success: 'fa-check-circle',
    danger: 'fa-exclamation-circle',
    warning: 'fa-exclamation-triangle',
    info: 'fa-info-circle'
  };
  
  const icon = icons[type] || icons.info;
  
  toastContainer.innerHTML = `
    <div class="toast align-items-center text-white bg-${type} border-0" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="d-flex">
        <div class="toast-body">
          <i class="fas ${icon} me-2"></i> ${message}
        </div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
    </div>
  `;
  
  document.body.appendChild(toastContainer);
  
  const toastElement = toastContainer.querySelector('.toast');
  const toast = new bootstrap.Toast(toastElement, { delay: 5000 });
  toast.show();
  
  toastElement.addEventListener('hidden.bs.toast', function() {
    toastContainer.remove();
  });
}

// Capitalize first letter of a string
function capitalizeFirstLetter(string) {
  if (!string) return '';
  return string.charAt(0).toUpperCase() + string.slice(1);
} 