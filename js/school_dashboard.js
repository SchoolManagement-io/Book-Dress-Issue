// Wait for DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
  // Check if user is authenticated
  checkAuth();
  
  // Load dashboard data
  loadDashboardData();
  
  // Set up event listeners
  setupEventListeners();
  
  // Initialize Bootstrap tooltips
  initTooltips();
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
      showErrorToast('Authentication check failed. Please try refreshing the page.');
    });
}

// Load all dashboard data
function loadDashboardData() {
  showLoadingOverlay();
  
  fetch('api/school_data.php')
    .then(response => response.json())
    .then(data => {
      hideLoadingOverlay();
      
      if (data.success) {
        // Update dashboard with retrieved data
        updateSchoolInfo(data.school);
        updateInventoryStats(data.inventory);
        updateOrdersStats(data.orders);
        updateStudentsCount(data.students_count);
        updateRecentOrders(data.recent_orders);
      } else {
        showErrorToast(data.message || 'Failed to load dashboard data');
        // Show error state for dashboard widgets
        showErrorState();
      }
    })
    .catch(error => {
      hideLoadingOverlay();
      console.error('Dashboard data loading error:', error);
      showErrorToast('Failed to load dashboard data. Please try refreshing the page.');
      showErrorState();
    });
}

// Update school information in the dashboard
function updateSchoolInfo(school) {
  if (!school) return;
  
  // Update school profile info
  document.getElementById('schoolNameHeader').textContent = school.school_name || '';
  document.getElementById('schoolCode').textContent = school.school_code || '';
  document.getElementById('schoolEmail').textContent = school.email || '';
  document.getElementById('schoolPhone').textContent = school.phone || '';
  
  // Update school address if element exists
  const addressElement = document.getElementById('schoolAddress');
  if (addressElement) {
    const fullAddress = [
      school.address,
      school.city,
      school.state,
      school.zipcode
    ].filter(Boolean).join(', ');
    
    addressElement.textContent = fullAddress || 'No address provided';
  }
  
  // Update principal name if available
  const principalElement = document.getElementById('principalName');
  if (principalElement && school.principal_name) {
    principalElement.textContent = school.principal_name;
  }
  
  // Update registration date if available
  if (school.registration_date) {
    const regDate = new Date(school.registration_date);
    const formattedDate = regDate.toLocaleDateString('en-IN', {
      day: 'numeric',
      month: 'short',
      year: 'numeric'
    });
    
    const registrationDateElement = document.getElementById('registrationDate');
    if (registrationDateElement) {
      registrationDateElement.textContent = formattedDate;
    }
  }
  
  // Update last login if available
  if (school.last_login) {
    const lastLogin = new Date(school.last_login);
    const formattedLastLogin = lastLogin.toLocaleDateString('en-IN', {
      day: 'numeric',
      month: 'short',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    });
    
    const lastLoginElement = document.getElementById('lastLogin');
    if (lastLoginElement) {
      lastLoginElement.textContent = formattedLastLogin;
    }
  }
}

// Update inventory statistics
function updateInventoryStats(inventory) {
  if (!inventory) return;
  
  // Remove loading spinners
  const loadingElements = document.querySelectorAll('.inventory-stats .loading-spinner');
  loadingElements.forEach(element => {
    element.classList.add('d-none');
  });
  
  // Update total items
  const totalItemsElement = document.getElementById('totalItems');
  if (totalItemsElement) {
    totalItemsElement.textContent = inventory.total_items.toLocaleString('en-IN');
    totalItemsElement.classList.remove('d-none');
  }
  
  // Update total inventory value
  const totalValueElement = document.getElementById('totalValue');
  if (totalValueElement) {
    totalValueElement.textContent = '₹' + inventory.total_value.toLocaleString('en-IN');
    totalValueElement.classList.remove('d-none');
  }
  
  // Update item type counts
  updateItemTypeCount('booksCount', inventory.books_count);
  updateItemTypeCount('uniformsCount', inventory.uniforms_count);
  updateItemTypeCount('stationaryCount', inventory.stationary_count);
  updateItemTypeCount('otherItemsCount', inventory.other_count);
  
  // Update low stock count
  const lowStockElement = document.getElementById('lowStockCount');
  if (lowStockElement) {
    lowStockElement.textContent = inventory.low_stock_count.toLocaleString('en-IN');
    
    // Highlight if there are items in low stock
    if (inventory.low_stock_count > 0) {
      const lowStockCard = document.getElementById('lowStockCard');
      if (lowStockCard) {
        lowStockCard.classList.add('border-warning');
        lowStockCard.querySelector('.card-title').classList.add('text-warning');
      }
    }
    
    lowStockElement.classList.remove('d-none');
  }
  
  // Show inventory composition chart if exists
  const chartElement = document.getElementById('inventoryChart');
  if (chartElement && typeof Chart !== 'undefined') {
    createInventoryChart(chartElement, inventory);
  }
}

// Update individual item type count
function updateItemTypeCount(elementId, count) {
  const element = document.getElementById(elementId);
  if (element) {
    element.textContent = count.toLocaleString('en-IN');
    element.classList.remove('d-none');
  }
}

// Create inventory chart
function createInventoryChart(canvas, inventory) {
  // Create data for chart
  const data = {
    labels: ['Books', 'Uniforms', 'Stationary', 'Other'],
    datasets: [{
      data: [
        inventory.books_count, 
        inventory.uniforms_count, 
        inventory.stationary_count, 
        inventory.other_count
      ],
      backgroundColor: [
        '#4e73df', // Blue
        '#1cc88a', // Green
        '#f6c23e', // Yellow
        '#e74a3b'  // Red
      ],
      borderWidth: 1
    }]
  };
  
  // Check if chart instance already exists and destroy it
  if (window.inventoryChartInstance) {
    window.inventoryChartInstance.destroy();
  }
  
  // Create new chart
  window.inventoryChartInstance = new Chart(canvas, {
    type: 'doughnut',
    data: data,
    options: {
      responsive: true,
      maintainAspectRatio: false,
      cutout: '70%',
      plugins: {
        legend: {
          position: 'bottom'
        }
      }
    }
  });
}

// Update orders statistics
function updateOrdersStats(orders) {
  if (!orders) return;
  
  // Remove loading spinners
  const loadingElements = document.querySelectorAll('.orders-stats .loading-spinner');
  loadingElements.forEach(element => {
    element.classList.add('d-none');
  });
  
  // Update total orders
  const totalOrdersElement = document.getElementById('totalOrders');
  if (totalOrdersElement) {
    totalOrdersElement.textContent = orders.total_orders.toLocaleString('en-IN');
    totalOrdersElement.classList.remove('d-none');
  }
  
  // Update order status counts
  updateOrderStatusCount('pendingOrders', orders.pending_orders);
  updateOrderStatusCount('processingOrders', orders.processing_orders);
  updateOrderStatusCount('completedOrders', orders.completed_orders);
  
  // Update total sales
  const totalSalesElement = document.getElementById('totalSales');
  if (totalSalesElement) {
    totalSalesElement.textContent = '₹' + orders.total_sales.toLocaleString('en-IN');
    totalSalesElement.classList.remove('d-none');
  }
  
  // Update monthly sales
  const monthlySalesElement = document.getElementById('monthlySales');
  if (monthlySalesElement) {
    monthlySalesElement.textContent = '₹' + orders.monthly_sales.toLocaleString('en-IN');
    monthlySalesElement.classList.remove('d-none');
  }
  
  // Show orders chart if element exists
  const chartElement = document.getElementById('ordersChart');
  if (chartElement && typeof Chart !== 'undefined') {
    createOrdersChart(chartElement, orders);
  }
}

// Update individual order status count
function updateOrderStatusCount(elementId, count) {
  const element = document.getElementById(elementId);
  if (element) {
    element.textContent = count.toLocaleString('en-IN');
    element.classList.remove('d-none');
  }
}

// Create orders status chart
function createOrdersChart(canvas, orders) {
  // Create data for chart
  const data = {
    labels: ['Completed', 'Processing', 'Pending'],
    datasets: [{
      data: [
        orders.completed_orders,
        orders.processing_orders,
        orders.pending_orders
      ],
      backgroundColor: [
        '#1cc88a', // Green
        '#f6c23e', // Yellow
        '#e74a3b'  // Red
      ],
      borderWidth: 1
    }]
  };
  
  // Check if chart instance already exists and destroy it
  if (window.ordersChartInstance) {
    window.ordersChartInstance.destroy();
  }
  
  // Create new chart
  window.ordersChartInstance = new Chart(canvas, {
    type: 'doughnut',
    data: data,
    options: {
      responsive: true,
      maintainAspectRatio: false,
      cutout: '70%',
      plugins: {
        legend: {
          position: 'bottom'
        }
      }
    }
  });
}

// Update students count
function updateStudentsCount(count) {
  const studentsCountElement = document.getElementById('studentsCount');
  if (studentsCountElement) {
    // Remove loading spinner
    const spinner = studentsCountElement.closest('.card').querySelector('.loading-spinner');
    if (spinner) {
      spinner.classList.add('d-none');
    }
    
    // Update count
    studentsCountElement.textContent = count.toLocaleString('en-IN');
    studentsCountElement.classList.remove('d-none');
  }
}

// Update recent orders
function updateRecentOrders(orders) {
  const recentOrdersTableBody = document.getElementById('recentOrdersBody');
  const recentOrdersLoading = document.getElementById('recentOrdersLoading');
  const noOrdersMessage = document.getElementById('noOrdersMessage');
  
  if (recentOrdersLoading) {
    recentOrdersLoading.classList.add('d-none');
  }
  
  if (!recentOrdersTableBody) return;
  
  // Clear existing content
  recentOrdersTableBody.innerHTML = '';
  
  if (!orders || orders.length === 0) {
    if (noOrdersMessage) {
      noOrdersMessage.classList.remove('d-none');
    }
    return;
  }
  
  // Hide no orders message if shown
  if (noOrdersMessage) {
    noOrdersMessage.classList.add('d-none');
  }
  
  // Add orders to table
  orders.forEach(order => {
    const row = document.createElement('tr');
    
    // Format date
    const orderDate = new Date(order.order_date);
    const formattedDate = orderDate.toLocaleDateString('en-IN', { 
      day: 'numeric', 
      month: 'short', 
      year: 'numeric' 
    });
    
    // Create status badge class based on order status
    let statusClass = 'bg-secondary';
    if (order.order_status === 'completed') {
      statusClass = 'bg-success';
    } else if (order.order_status === 'processing') {
      statusClass = 'bg-warning text-dark';
    } else if (order.order_status === 'pending') {
      statusClass = 'bg-danger';
    }
    
    // Build row HTML
    row.innerHTML = `
      <td>${order.order_number}</td>
      <td>${formattedDate}</td>
      <td>${order.student_name}</td>
      <td>${order.student_class}${order.student_section ? '-' + order.student_section : ''}</td>
      <td>${order.parent_name}</td>
      <td>₹${order.total_amount.toLocaleString('en-IN')}</td>
      <td><span class="badge ${statusClass}">${capitalizeFirstLetter(order.order_status)}</span></td>
      <td>
        <a href="school_order_details.php?id=${order.id}" class="btn btn-sm btn-primary">
          <i class="fas fa-eye"></i>
        </a>
      </td>
    `;
    
    recentOrdersTableBody.appendChild(row);
  });
  
  // Show the table
  const recentOrdersTable = document.getElementById('recentOrdersTable');
  if (recentOrdersTable) {
    recentOrdersTable.classList.remove('d-none');
  }
}

// Set up event listeners
function setupEventListeners() {
  // Logout button
  const logoutBtn = document.getElementById('logoutBtn');
  if (logoutBtn) {
    logoutBtn.addEventListener('click', function(e) {
      e.preventDefault();
      logout();
    });
  }
  
  // Add inventory button
  const addInventoryBtn = document.getElementById('addInventoryBtn');
  if (addInventoryBtn) {
    addInventoryBtn.addEventListener('click', function() {
      // Show inventory modal if exists
      const inventoryModal = new bootstrap.Modal(document.getElementById('addInventoryModal'));
      if (inventoryModal) {
        inventoryModal.show();
      }
    });
  }
  
  // Add inventory form submission
  const addInventoryForm = document.getElementById('addInventoryForm');
  if (addInventoryForm) {
    addInventoryForm.addEventListener('submit', function(e) {
      e.preventDefault();
      addInventoryItem();
    });
  }
  
  // Refresh data button
  const refreshBtn = document.getElementById('refreshDataBtn');
  if (refreshBtn) {
    refreshBtn.addEventListener('click', function() {
      loadDashboardData();
    });
  }
}

// Add new inventory item
function addInventoryItem() {
  const form = document.getElementById('addInventoryForm');
  const submitBtn = form.querySelector('button[type="submit"]');
  const formData = new FormData(form);
  
  // Disable submit button and show loading state
  if (submitBtn) {
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Adding...';
  }
  
  fetch('api/add_inventory.php', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(data => {
    // Re-enable submit button
    if (submitBtn) {
      submitBtn.disabled = false;
      submitBtn.innerHTML = 'Add Item';
    }
    
    if (data.success) {
      // Show success message
      showSuccessToast('Item added successfully!');
      
      // Close modal
      const modal = bootstrap.Modal.getInstance(document.getElementById('addInventoryModal'));
      if (modal) {
        modal.hide();
      }
      
      // Reset form
      form.reset();
      
      // Reload dashboard data to reflect changes
      loadDashboardData();
    } else {
      // Show error message
      showErrorToast(data.message || 'Failed to add inventory item');
    }
  })
  .catch(error => {
    console.error('Add inventory error:', error);
    showErrorToast('Failed to add inventory item. Please try again.');
    
    // Re-enable submit button
    if (submitBtn) {
      submitBtn.disabled = false;
      submitBtn.innerHTML = 'Add Item';
    }
  });
}

// Logout the user
function logout() {
  fetch('api/logout.php')
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        // Redirect to login page
        window.location.href = 'school_login.php?success=logout';
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

// Show error state for dashboard widgets
function showErrorState() {
  const errorElements = document.querySelectorAll('.error-message');
  errorElements.forEach(element => {
    element.classList.remove('d-none');
  });
  
  const loadingElements = document.querySelectorAll('.loading-spinner');
  loadingElements.forEach(element => {
    element.classList.add('d-none');
  });
}

// Initialize Bootstrap tooltips
function initTooltips() {
  const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
  tooltipTriggerList.map(function(tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
  });
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

// Helper function to capitalize first letter
function capitalizeFirstLetter(string) {
  if (!string) return '';
  return string.charAt(0).toUpperCase() + string.slice(1);
} 