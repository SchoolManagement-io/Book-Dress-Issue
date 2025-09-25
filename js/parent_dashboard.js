/**
 * Samridhi Book Dress - Parent Dashboard JavaScript
 * Handles student info, inventory management, cart functionality and order processing
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
        // Load user data and inventory
        loadUserData(data.parent_id);
        loadInventoryItems();
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
        
        // Update student information
        document.getElementById('student-name').textContent = studentData.name;
        document.getElementById('student-class').textContent = `Class: ${studentData.class}`;
        document.getElementById('student-school').textContent = `School: ${schoolData.name}`;
        document.getElementById('school-id').textContent = schoolData.code;
        document.getElementById('parent-id').textContent = parentData.id;
      } else {
        console.error('Failed to load user data:', data.message);
      }
    })
    .catch(error => {
      console.error('Failed to load user data:', error);
    });
  }

  // Load inventory items
  function loadInventoryItems() {
    const itemsContainer = document.getElementById('items-container');
    
    fetch('api/inventory_items.php', {
      method: 'GET',
      credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        // Clear loading spinner
        itemsContainer.innerHTML = '';
        
        if (data.items.length === 0) {
          itemsContainer.innerHTML = `
            <div class="col-12 text-center py-5">
              <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
              <h5>No items available</h5>
              <p class="text-muted">No inventory items are available for your school or class</p>
            </div>`;
          return;
        }
        
        // Store items globally for filtering
        window.inventoryItems = data.items;
        
        // Render all items initially
        renderInventoryItems(data.items);
        
        // Initialize filters
        initializeFilters();
      } else {
        itemsContainer.innerHTML = `
          <div class="col-12 text-center py-5">
            <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
            <h5>Failed to load items</h5>
            <p class="text-muted">${data.message || 'Could not load inventory items'}</p>
          </div>`;
      }
    })
    .catch(error => {
      console.error('Failed to load inventory items:', error);
      itemsContainer.innerHTML = `
        <div class="col-12 text-center py-5">
          <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
          <h5>Error</h5>
          <p class="text-muted">Failed to connect to the server. Please try again later.</p>
        </div>`;
    });
  }

  // Render inventory items
  function renderInventoryItems(items) {
    const itemsContainer = document.getElementById('items-container');
    itemsContainer.innerHTML = '';
    
    items.forEach(item => {
      const itemCard = document.createElement('div');
      itemCard.className = 'col-md-6 col-lg-4 inventory-item';
      itemCard.dataset.category = item.category;
      
      const stockStatus = item.in_stock ? 
        `<span class="badge bg-success">In Stock</span>` : 
        `<span class="badge bg-danger">Out of Stock</span>`;
      
      const addToCartButton = item.in_stock ?
        `<button class="btn btn-sm btn-primary add-to-cart" data-item-id="${item.id}">
           <i class="fas fa-cart-plus me-1"></i> Add to Cart
         </button>` :
        `<button class="btn btn-sm btn-secondary" disabled>
           <i class="fas fa-ban me-1"></i> Out of Stock
         </button>`;
      
      itemCard.innerHTML = `
        <div class="card h-100">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-start">
              <h5 class="card-title mb-1">${item.name}</h5>
              ${stockStatus}
            </div>
            <p class="card-text text-muted small mb-2">${item.category}</p>
            <p class="card-text">${item.description || 'No description available'}</p>
            <div class="d-flex justify-content-between align-items-center mt-3">
              <span class="fs-5 fw-bold">₹${parseFloat(item.price).toFixed(2)}</span>
              ${addToCartButton}
            </div>
          </div>
        </div>
      `;
      
      itemsContainer.appendChild(itemCard);
    });
    
    // Add event listeners to "Add to Cart" buttons
    document.querySelectorAll('.add-to-cart').forEach(button => {
      button.addEventListener('click', function() {
        const itemId = this.dataset.itemId;
        const item = items.find(i => i.id == itemId);
        addToCart(item);
      });
    });
  }

  // Initialize category filters, search, and sorting
  function initializeFilters() {
    // Category buttons
    document.querySelectorAll('.category-btn').forEach(button => {
      button.addEventListener('click', function() {
        // Remove active class from all buttons
        document.querySelectorAll('.category-btn').forEach(btn => {
          btn.classList.remove('active');
        });
        
        // Add active class to clicked button
        this.classList.add('active');
        
        // Filter items
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
    
    // In-stock filter
    document.getElementById('in-stock-only').addEventListener('change', function() {
      applyFilters();
    });
    
    // Sort options
    document.querySelectorAll('.sort-option').forEach(option => {
      option.addEventListener('click', function() {
        applyFilters();
      });
    });
  }

  // Apply all filters and sorting
  function applyFilters() {
    if (!window.inventoryItems) return;
    
    let filteredItems = [...window.inventoryItems];
    
    // Category filter
    const activeCategory = document.querySelector('.category-btn.active').dataset.category;
    if (activeCategory !== 'all') {
      filteredItems = filteredItems.filter(item => item.category === activeCategory);
    }
    
    // Search filter
    const searchTerm = document.getElementById('search-input').value.toLowerCase().trim();
    if (searchTerm) {
      filteredItems = filteredItems.filter(item => 
        item.name.toLowerCase().includes(searchTerm) || 
        item.description.toLowerCase().includes(searchTerm) ||
        item.category.toLowerCase().includes(searchTerm)
      );
    }
    
    // In-stock filter
    const inStockOnly = document.getElementById('in-stock-only').checked;
    if (inStockOnly) {
      filteredItems = filteredItems.filter(item => item.in_stock);
    }
    
    // Sorting
    const activeSort = document.querySelector('.sort-option:active');
    if (activeSort) {
      const sortOption = activeSort.dataset.sort;
      
      switch (sortOption) {
        case 'name-asc':
          filteredItems.sort((a, b) => a.name.localeCompare(b.name));
          break;
        case 'name-desc':
          filteredItems.sort((a, b) => b.name.localeCompare(a.name));
          break;
        case 'price-asc':
          filteredItems.sort((a, b) => parseFloat(a.price) - parseFloat(b.price));
          break;
        case 'price-desc':
          filteredItems.sort((a, b) => parseFloat(b.price) - parseFloat(a.price));
          break;
      }
    }
    
    renderInventoryItems(filteredItems);
  }

  // Cart functionality
  let cart = [];
  
  // Add item to cart
  function addToCart(item) {
    const existingItem = cart.find(i => i.id === item.id);
    
    if (existingItem) {
      existingItem.quantity += 1;
    } else {
      cart.push({
        id: item.id,
        name: item.name,
        price: parseFloat(item.price),
        quantity: 1,
        category: item.category
      });
    }
    
    // Update cart UI
    updateCartUI();
    
    // Show brief notification
    const toast = new bootstrap.Toast(document.createElement('div'));
    toast._element.classList.add('toast', 'bg-success', 'text-white');
    toast._element.setAttribute('role', 'alert');
    toast._element.setAttribute('aria-live', 'assertive');
    toast._element.setAttribute('aria-atomic', 'true');
    toast._element.innerHTML = `
      <div class="toast-body">
        <i class="fas fa-check-circle me-2"></i> ${item.name} added to cart!
      </div>
    `;
    document.body.appendChild(toast._element);
    toast.show();
    
    setTimeout(() => {
      document.body.removeChild(toast._element);
    }, 2000);
  }
  
  // Update cart UI
  function updateCartUI() {
    const cartCount = document.getElementById('cart-count');
    const cartItems = document.getElementById('cart-items');
    const emptyCart = document.getElementById('empty-cart');
    const cartItemsContainer = document.getElementById('cart-items-container');
    const subtotal = document.getElementById('subtotal');
    
    // Update cart count
    const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
    cartCount.textContent = totalItems;
    
    // Show/hide empty cart message
    if (cart.length === 0) {
      emptyCart.classList.remove('d-none');
      cartItemsContainer.classList.add('d-none');
    } else {
      emptyCart.classList.add('d-none');
      cartItemsContainer.classList.remove('d-none');
      
      // Render cart items
      cartItems.innerHTML = '';
      let cartSubtotal = 0;
      
      cart.forEach(item => {
        const itemTotal = item.price * item.quantity;
        cartSubtotal += itemTotal;
        
        const cartItem = document.createElement('div');
        cartItem.className = 'card mb-2';
        cartItem.innerHTML = `
          <div class="card-body p-3">
            <div class="d-flex justify-content-between align-items-center">
              <h6 class="card-title mb-0">${item.name}</h6>
              <button class="btn btn-sm btn-outline-danger remove-item" data-item-id="${item.id}">
                <i class="fas fa-trash-alt"></i>
              </button>
            </div>
            <p class="card-text text-muted small">${item.category}</p>
            <div class="d-flex justify-content-between align-items-center">
              <div class="input-group input-group-sm" style="width: 100px;">
                <button class="btn btn-outline-secondary decrement-qty" data-item-id="${item.id}">-</button>
                <input type="text" class="form-control text-center" value="${item.quantity}" readonly>
                <button class="btn btn-outline-secondary increment-qty" data-item-id="${item.id}">+</button>
              </div>
              <span>₹${itemTotal.toFixed(2)}</span>
            </div>
          </div>
        `;
        
        cartItems.appendChild(cartItem);
      });
      
      // Update subtotal
      subtotal.textContent = `₹${cartSubtotal.toFixed(2)}`;
      
      // Add event listeners to cart item buttons
      document.querySelectorAll('.remove-item').forEach(button => {
        button.addEventListener('click', function() {
          const itemId = this.dataset.itemId;
          removeFromCart(itemId);
        });
      });
      
      document.querySelectorAll('.decrement-qty').forEach(button => {
        button.addEventListener('click', function() {
          const itemId = this.dataset.itemId;
          decrementCartItem(itemId);
        });
      });
      
      document.querySelectorAll('.increment-qty').forEach(button => {
        button.addEventListener('click', function() {
          const itemId = this.dataset.itemId;
          incrementCartItem(itemId);
        });
      });
    }
  }
  
  // Remove item from cart
  function removeFromCart(itemId) {
    cart = cart.filter(item => item.id != itemId);
    updateCartUI();
  }
  
  // Decrement item quantity
  function decrementCartItem(itemId) {
    const item = cart.find(item => item.id == itemId);
    if (item) {
      item.quantity -= 1;
      if (item.quantity <= 0) {
        removeFromCart(itemId);
      } else {
        updateCartUI();
      }
    }
  }
  
  // Increment item quantity
  function incrementCartItem(itemId) {
    const item = cart.find(item => item.id == itemId);
    if (item) {
      item.quantity += 1;
      updateCartUI();
    }
  }
  
  // Clear cart
  document.getElementById('clear-cart-btn').addEventListener('click', function() {
    cart = [];
    updateCartUI();
  });
  
  // Checkout process
  document.getElementById('checkout-btn').addEventListener('click', function() {
    if (cart.length === 0) return;
    
    // Populate order summary
    const orderItemsSummary = document.getElementById('order-items-summary');
    const totalItems = document.getElementById('total-items');
    const totalAmount = document.getElementById('total-amount');
    
    orderItemsSummary.innerHTML = '';
    let cartTotal = 0;
    let itemCount = 0;
    
    cart.forEach(item => {
      const itemTotal = item.price * item.quantity;
      cartTotal += itemTotal;
      itemCount += item.quantity;
      
      const orderItem = document.createElement('div');
      orderItem.className = 'mb-2 pb-2 border-bottom';
      orderItem.innerHTML = `
        <div class="d-flex justify-content-between">
          <div>
            <span class="fw-medium">${item.name}</span>
            <small class="text-muted d-block">₹${item.price.toFixed(2)} x ${item.quantity}</small>
          </div>
          <span>₹${itemTotal.toFixed(2)}</span>
        </div>
      `;
      
      orderItemsSummary.appendChild(orderItem);
    });
    
    totalItems.textContent = itemCount;
    totalAmount.textContent = `₹${cartTotal.toFixed(2)}`;
    
    // Show confirmation modal
    const orderModal = new bootstrap.Modal(document.getElementById('orderConfirmModal'));
    orderModal.show();
  });
  
  // Place order
  document.getElementById('confirm-order-btn').addEventListener('click', function() {
    // Show loading overlay
    const loadingOverlay = document.getElementById('loading-overlay');
    loadingOverlay.classList.remove('d-none');
    
    // Prepare order data
    const orderData = {
      items: cart,
      note: document.getElementById('delivery-note').value,
      delivery_address: document.getElementById('delivery-address').value
    };
    
    // Submit order
    fetch('api/place_order.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(orderData),
      credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
      loadingOverlay.classList.add('d-none');
      
      if (data.success) {
        // Hide confirmation modal
        bootstrap.Modal.getInstance(document.getElementById('orderConfirmModal')).hide();
        
        // Update order success modal
        document.getElementById('success-order-id').textContent = data.order_id;
        
        // Show success modal
        const successModal = new bootstrap.Modal(document.getElementById('orderSuccessModal'));
        successModal.show();
        
        // Clear cart
        cart = [];
        updateCartUI();
      } else {
        alert('Failed to place order: ' + (data.message || 'Unknown error'));
      }
    })
    .catch(error => {
      console.error('Order placement failed:', error);
      loadingOverlay.classList.add('d-none');
      alert('Failed to connect to server. Please try again later.');
    });
  });
  
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