/**
 * Samridhi Book Dress - Parent Orders JavaScript
 * Handles order data loading and display functionality
 */

document.addEventListener('DOMContentLoaded', function() {
    // Check if user is logged in
    function checkAuth() {
      fetch('api/check_session.php', {
        method: 'GET',
        credentials: 'same-origin'
      })
      .then(response => response.json())
      .then(data => {
        if (!data.authenticated || data.user_type !== 'parent') {
          window.location.href = 'parent_login.php';
        } else {
          // Load user data and order history
          loadUserData(data.parent_id);
          loadOrderHistory();
        }
      })
      .catch(error => {
        console.error('Authentication check failed:', error);
        window.location.href = 'parent_login.php';
      });
    }
  
    // Load parent and student information
    function loadUserData(parentId) {
      fetch(`api/parent_data.php?parent_id=${parentId}`, {
        method: 'GET',
        credentials: 'same-origin'
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          const parentData = data.parent;
          const studentData = data.student;
          const schoolData = data.school;
          
          // Update user name in navbar
          document.getElementById('user-name').textContent = parentData.name;
          
          // Update student information in header
          document.getElementById('student-name').textContent = studentData.name;
          document.getElementById('student-class').textContent = `Class: ${studentData.class}`;
          document.getElementById('student-school').textContent = `School: ${schoolData.name}`;
          document.getElementById('parent-id').textContent = data.parent_id || parentData.id;
        } else {
          console.error('Failed to load user data:', data.message);
        }
      })
      .catch(error => {
        console.error('Failed to load user data:', error);
      });
    }
  
    // Load order history
    function loadOrderHistory() {
      const ordersContainer = document.getElementById('orders-container');
      
      // Show loading state
      ordersContainer.innerHTML = `
        <div class="text-center py-5">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
          <p class="mt-2">Loading orders...</p>
        </div>
      `;
      
      fetch('api/parent_orders.php', {
        method: 'GET',
        credentials: 'same-origin'
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          if (data.orders.length === 0) {
            ordersContainer.innerHTML = `
              <div class="text-center py-5">
                <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                <h5>No Orders Found</h5>
                <p class="text-muted">You haven't placed any orders yet.</p>
                <a href="parent_dashboard.php" class="btn btn-primary mt-3">
                  <i class="fas fa-shopping-basket me-2"></i>Browse Items
                </a>
              </div>
            `;
            return;
          }
          
          // Store orders globally for filtering
          window.ordersList = data.orders;
          
          // Render orders
          renderOrders(data.orders);
          
          // Initialize filters
          initializeFilters();
          
          // Update order count
          document.getElementById('order-count').textContent = data.orders.length;
        } else {
          ordersContainer.innerHTML = `
            <div class="text-center py-5">
              <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
              <h5>Failed to load orders</h5>
              <p class="text-muted">${data.message || 'Could not load your order history'}</p>
            </div>
          `;
        }
      })
      .catch(error => {
        console.error('Failed to load order history:', error);
        ordersContainer.innerHTML = `
          <div class="text-center py-5">
            <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
            <h5>Error</h5>
            <p class="text-muted">Failed to connect to the server. Please try again later.</p>
          </div>
        `;
      });
    }
  
    // Render orders
    function renderOrders(orders) {
      const ordersContainer = document.getElementById('orders-container');
      ordersContainer.innerHTML = '';
      
      orders.forEach(order => {
        // Format date
        const orderDate = new Date(order.created_at);
        const formattedDate = orderDate.toLocaleDateString('en-IN', {
          day: 'numeric',
          month: 'short',
          year: 'numeric'
        });
        
        // Choose status badge color
        let statusClass = 'bg-secondary';
        if (order.status === 'Pending') statusClass = 'bg-warning text-dark';
        if (order.status === 'Processing') statusClass = 'bg-info text-dark';
        if (order.status === 'Ready') statusClass = 'bg-primary';
        if (order.status === 'Delivered') statusClass = 'bg-success';
        if (order.status === 'Cancelled') statusClass = 'bg-danger';
        
        const orderCard = document.createElement('div');
        orderCard.className = 'col-lg-6 mb-4';
        orderCard.innerHTML = `
          <div class="card order-card h-100 shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
              <h5 class="mb-0">
                <i class="fas fa-shopping-bag me-2 text-primary"></i>
                <span class="text-primary">${order.order_id}</span>
              </h5>
              <span class="badge ${statusClass} py-2 px-3">${order.status}</span>
            </div>
            <div class="card-body">
              <div class="mb-3">
                <div class="d-flex justify-content-between mb-2">
                  <span class="text-muted">Order Date:</span>
                  <span>${formattedDate}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                  <span class="text-muted">Items:</span>
                  <span>${order.item_count}</span>
                </div>
                <div class="d-flex justify-content-between">
                  <span class="text-muted">Total Amount:</span>
                  <span class="fw-bold">₹${parseFloat(order.total_amount).toFixed(2)}</span>
                </div>
              </div>
              
              <div class="border-top pt-3">
                <h6 class="mb-3">Order Items</h6>
                <div class="order-items">
                  ${renderOrderItems(order.items)}
                </div>
              </div>
              
              ${order.delivery_note ? `
                <div class="alert alert-info mt-3 mb-0">
                  <small class="fw-medium">Note: ${order.delivery_note}</small>
                </div>
              ` : ''}
            </div>
            <div class="card-footer bg-white text-end py-3">
              <button class="btn btn-sm btn-outline-primary view-details-btn" data-order-id="${order.order_id}">
                <i class="fas fa-eye me-1"></i> View Details
              </button>
            </div>
          </div>
        `;
        
        ordersContainer.appendChild(orderCard);
      });
      
      // Add event listeners for view details buttons
      document.querySelectorAll('.view-details-btn').forEach(button => {
        button.addEventListener('click', function() {
          const orderId = this.dataset.orderId;
          const order = orders.find(o => o.order_id === orderId);
          showOrderDetailsModal(order);
        });
      });
    }
    
    // Render order items in card
    function renderOrderItems(items) {
      if (!items || items.length === 0) {
        return '<p class="text-muted small">No items found</p>';
      }
      
      // Only show first 3 items in card
      const displayItems = items.slice(0, 3);
      const remainingCount = items.length - displayItems.length;
      
      let html = '<ul class="list-unstyled mb-0">';
      
      displayItems.forEach(item => {
        html += `
          <li class="small mb-1">
            ${item.name} <span class="text-muted">(${item.quantity} × ₹${parseFloat(item.unit_price).toFixed(2)})</span>
          </li>
        `;
      });
      
      if (remainingCount > 0) {
        html += `<li class="small text-primary">+ ${remainingCount} more item${remainingCount > 1 ? 's' : ''}</li>`;
      }
      
      html += '</ul>';
      return html;
    }
    
    // Show order details modal
    function showOrderDetailsModal(order) {
      // Format date and time
      const orderDate = new Date(order.created_at);
      const formattedDate = orderDate.toLocaleDateString('en-IN', {
        day: 'numeric',
        month: 'short',
        year: 'numeric'
      });
      const formattedTime = orderDate.toLocaleTimeString('en-IN', {
        hour: '2-digit',
        minute: '2-digit'
      });
      
      // Choose status badge color
      let statusClass = 'bg-secondary';
      if (order.status === 'Pending') statusClass = 'bg-warning text-dark';
      if (order.status === 'Processing') statusClass = 'bg-info text-dark';
      if (order.status === 'Ready') statusClass = 'bg-primary';
      if (order.status === 'Delivered') statusClass = 'bg-success';
      if (order.status === 'Cancelled') statusClass = 'bg-danger';
      
      // Fill modal with order details
      document.getElementById('modal-order-id').textContent = order.order_id;
      document.getElementById('modal-order-status').className = `badge ${statusClass} py-2 px-3`;
      document.getElementById('modal-order-status').textContent = order.status;
      document.getElementById('modal-order-date').textContent = `${formattedDate} at ${formattedTime}`;
      
      // Render all order items in modal
      const itemsContainer = document.getElementById('modal-order-items');
      itemsContainer.innerHTML = '';
      
      order.items.forEach(item => {
        const itemRow = document.createElement('tr');
        itemRow.innerHTML = `
          <td>${item.name}</td>
          <td>${item.category}</td>
          <td class="text-center">${item.quantity}</td>
          <td class="text-end">₹${parseFloat(item.unit_price).toFixed(2)}</td>
          <td class="text-end">₹${parseFloat(item.total_price).toFixed(2)}</td>
        `;
        itemsContainer.appendChild(itemRow);
      });
      
      // Update order summary
      document.getElementById('modal-items-count').textContent = order.item_count;
      document.getElementById('modal-total-amount').textContent = `₹${parseFloat(order.total_amount).toFixed(2)}`;
      
      // Update delivery note if present
      const noteContainer = document.getElementById('modal-delivery-note-container');
      if (order.delivery_note) {
        noteContainer.classList.remove('d-none');
        document.getElementById('modal-delivery-note').textContent = order.delivery_note;
      } else {
        noteContainer.classList.add('d-none');
      }
      
      // Update delivery address if present
      const addressContainer = document.getElementById('modal-delivery-address-container');
      if (order.delivery_address) {
        addressContainer.classList.remove('d-none');
        document.getElementById('modal-delivery-address').textContent = order.delivery_address;
      } else {
        addressContainer.classList.add('d-none');
      }
      
      // Show modal
      const detailsModal = new bootstrap.Modal(document.getElementById('orderDetailsModal'));
      detailsModal.show();
    }
    
    // Initialize filters
    function initializeFilters() {
      // Status filter
      document.querySelectorAll('.status-filter').forEach(button => {
        button.addEventListener('click', function() {
          document.querySelectorAll('.status-filter').forEach(btn => btn.classList.remove('active'));
          this.classList.add('active');
          applyFilters();
        });
      });
      
      // Search input
      const searchInput = document.getElementById('search-input');
      searchInput.addEventListener('input', function() {
        applyFilters();
      });
      
      // Clear search button
      document.getElementById('clear-search').addEventListener('click', function() {
        searchInput.value = '';
        applyFilters();
      });
      
      // Date filter buttons
      document.querySelectorAll('.date-filter').forEach(button => {
        button.addEventListener('click', function() {
          document.querySelectorAll('.date-filter').forEach(btn => btn.classList.remove('active'));
          this.classList.add('active');
          applyFilters();
        });
      });
    }
    
    // Apply all filters
    function applyFilters() {
      if (!window.ordersList) return;
      
      let filteredOrders = [...window.ordersList];
      
      // Status filter
      const activeStatus = document.querySelector('.status-filter.active').dataset.status;
      if (activeStatus !== 'all') {
        filteredOrders = filteredOrders.filter(order => order.status === activeStatus);
      }
      
      // Search filter
      const searchTerm = document.getElementById('search-input').value.toLowerCase().trim();
      if (searchTerm) {
        filteredOrders = filteredOrders.filter(order => 
          order.order_id.toLowerCase().includes(searchTerm) || 
          order.items.some(item => item.name.toLowerCase().includes(searchTerm))
        );
      }
      
      // Date filter
      const dateFilter = document.querySelector('.date-filter.active').dataset.period;
      if (dateFilter !== 'all-time') {
        const now = new Date();
        let startDate = new Date();
        
        switch (dateFilter) {
          case 'last-30-days':
            startDate.setDate(now.getDate() - 30);
            break;
          case 'last-3-months':
            startDate.setMonth(now.getMonth() - 3);
            break;
          case 'last-6-months':
            startDate.setMonth(now.getMonth() - 6);
            break;
        }
        
        filteredOrders = filteredOrders.filter(order => {
          const orderDate = new Date(order.created_at);
          return orderDate >= startDate;
        });
      }
      
      // Sort by date (most recent first)
      filteredOrders.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
      
      // Render filtered orders
      renderOrders(filteredOrders);
      
      // Update order count
      document.getElementById('order-count').textContent = filteredOrders.length;
    }
    
    // Handle logout
    document.getElementById('logout-btn').addEventListener('click', function() {
      fetch('api/logout.php', {
        method: 'POST',
        credentials: 'same-origin'
      })
      .then(() => {
        window.location.href = 'parent_login.php';
      })
      .catch(error => {
        console.error('Logout failed:', error);
        window.location.href = 'parent_login.php';
      });
    });
  
    // Initialize
    checkAuth();
  });